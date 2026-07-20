<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table            = 'operateur';
    protected $primaryKey       = 'id_operateur';
    protected $allowedFields    = ['nom', 'prefixe', 'commition'];
    protected $returnType       = 'array';

    /**
     * Récupère uniquement les AUTRES opérateurs (exclut l'opérateur principal '033' / '037')
     */
    public function getAutresOperateurs()
    {
        return $this->whereNotIn('prefixe', ['033', '037'])->findAll();
    }

    /**
     * Calcule la somme globale des gains générés via les commissions des autres opérateurs
     */
    public function getSommeGainsAutres()
    {
        // Jointure entre les transactions et les opérateurs tiers basés sur le préfixe
        $builder = $this->db->table('transactions t');
        $builder->select('SUM(t.montant * (o.commition / 100)) as total_gains');
        $builder->join('user u', 't.id2 = u.id_user'); // Utilisateur récepteur
        $builder->join('operateur o', 'u.prefixe = o.prefixe');
        $builder->where('t.id_type', 3); // Uniquement les transferts
        $builder->whereNotIn('o.prefixe', ['033', '037']); // Autres opérateurs uniquement

        $result = $builder->get()->getRowArray();
        return $result['total_gains'] ?? 0.0;
    }

    /**
     * Liste l'historique complet des montants envoyés vers les autres opérateurs avec filtres
     */
    public function getHistoriqueAutres($filtres = [])
    {
        $builder = $this->db->table('transactions t');
        $builder->select('
            (u.prefixe || u.sufixe) as numero_destinataire,
            t.montant as montant_brut,
            o.commition as pourcentage,
            (t.montant * (o.commition / 100)) as commission_gain,
            COALESCE(mf.frai, 0) as frais_transfert,
            t.date as date_transaction,
            o.nom as operateur_nom,
            u.prefixe as prefixe_destinataire
        ');
        $builder->join('user u', 't.id2 = u.id_user');
        $builder->join('operateur o', 'u.prefixe = o.prefixe');
        $builder->leftJoin('Montant_frai mf', 't.idMontant_frai = mf.idMontantFrai');
        $builder->where('t.id_type', 3);
        $builder->whereNotIn('o.prefixe', ['033', '037']);

        // Application des filtres dynamiques (depuis ton formulaire de recherche)
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