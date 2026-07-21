<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table            = 'operateur';
    protected $primaryKey       = 'id_operateur';
    protected $allowedFields    = ['nom', 'commission'];
    protected $returnType       = 'array';

    /**
     * id_operateur de notre propre operateur (celui dont on ne veut pas
     * afficher les gains/commissions dans les ecrans "autres operateurs").
     */
    public const ID_OPERATEUR_PRINCIPAL = 1;

    /**
     * Recupere uniquement les AUTRES operateurs (exclut le notre), avec la
     * liste de leurs prefixes rattaches (un operateur peut en avoir plusieurs).
     */
    public function getAutresOperateurs()
    {
        $builder = $this->db->table('operateur o')
            ->select('o.id_operateur, o.nom, o.commission, GROUP_CONCAT(po.nom) as prefixes')
            ->join('prefixe_opateurateur po', 'po.id_operateur = o.id_operateur', 'left')
            ->where('o.id_operateur !=', self::ID_OPERATEUR_PRINCIPAL)
            ->groupBy('o.id_operateur');

        return $builder->get()->getResultArray();
    }

    /**
     * Calcule la somme globale des gains generes via les commissions des autres operateurs
     */
    public function getSommeGainsAutres()
    {
        $builder = $this->db->table('transactions t');
        $builder->select('SUM(t.montant * (o.commission / 100)) as total_gains');
        $builder->join('user u', 't.id2 = u.id_user'); // Utilisateur recepteur
        $builder->join('prefixe_opateurateur po', 'u.prefixe = po.nom');
        $builder->join('operateur o', 'po.id_operateur = o.id_operateur');
        $builder->where('t.id_type', 3); // Uniquement les transferts
        $builder->where('o.id_operateur !=', self::ID_OPERATEUR_PRINCIPAL);

        $result = $builder->get()->getRowArray();
        return $result['total_gains'] ?? 0.0;
    }

    /**
     * Liste l'historique complet des montants envoyes vers les autres operateurs avec filtres
     */
    public function getHistoriqueAutres($filtres = [])
    {
        $builder = $this->db->table('transactions t');
        $builder->select('
            (u.prefixe || u.sufixe) as numero_destinataire,
            t.montant as montant_brut,
            o.commission as pourcentage,
            (t.montant * (o.commission / 100)) as commission_gain,
            COALESCE(mf.frai, 0) as frais_transfert,
            t.date as date_transaction,
            o.nom as operateur_nom,
            u.prefixe as prefixe_destinataire
        ');
        $builder->join('user u', 't.id2 = u.id_user');
        $builder->join('prefixe_opateurateur po', 'u.prefixe = po.nom');
        $builder->join('operateur o', 'po.id_operateur = o.id_operateur');
        $builder->leftJoin('Montant_frai mf', 't.idMontant_frai = mf.idMontantFrai');
        $builder->where('t.id_type', 3);
        $builder->where('o.id_operateur !=', self::ID_OPERATEUR_PRINCIPAL);

        // Application des filtres dynamiques (depuis le formulaire de recherche)
        if (!empty($filtres['operateur'])) {
            $builder->where('o.id_operateur', $filtres['operateur']);
        }
        if (!empty($filtres['prefixe'])) {
            $builder->where('u.prefixe', $filtres['prefixe']);
        }
        if (!empty($filtres['date'])) {
            $builder->where('DATE(t.date)', $filtres['date']);
        }

        $builder->orderBy('t.date', 'DESC');
        return $builder->get()->getResultArray();
    }
}
