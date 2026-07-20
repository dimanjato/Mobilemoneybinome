<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table      = 'transactions';
    protected $primaryKey = 'id_transaction';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_type', 'montant', 'date', 'id1', 'id2', 'idMontant_frai',
    ];

    // Constantes correspondant a la table type_transaction (cf. base.sql)
    const TYPE_RETRAIT   = 1;
    const TYPE_DEPOT     = 2;
    const TYPE_TRANSFERT = 3;

    /**
     * Enregistre un depot : +montant pour id_user, sans frais.
     */
    public function enregistrerDepot(int $idUser, float $montant): int
    {
        return (int) $this->insert([
            'id_type'        => self::TYPE_DEPOT,
            'montant'        => $montant,
            'date'           => date('Y-m-d H:i:s'),
            'id1'            => $idUser,
            'id2'            => null,
            'idMontant_frai' => null,
        ]);
    }

    /**
     * Enregistre un retrait : -(montant + frais) pour id_user.
     */
    public function enregistrerRetrait(int $idUser, float $montant, ?int $idMontantFrai): int
    {
        return (int) $this->insert([
            'id_type'        => self::TYPE_RETRAIT,
            'montant'        => $montant,
            'date'           => date('Y-m-d H:i:s'),
            'id1'            => $idUser,
            'id2'            => null,
            'idMontant_frai' => $idMontantFrai,
        ]);
    }

    /**
     * Enregistre un transfert : -(montant + frais) pour l'emetteur,
     * +montant pour le destinataire.
     */
    public function enregistrerTransfert(int $idEmetteur, int $idDestinataire, float $montant, ?int $idMontantFrai): int
    {
        return (int) $this->insert([
            'id_type'        => self::TYPE_TRANSFERT,
            'montant'        => $montant,
            'date'           => date('Y-m-d H:i:s'),
            'id1'            => $idEmetteur,
            'id2'            => $idDestinataire,
            'idMontant_frai' => $idMontantFrai,
        ]);
    }

    /**
     * Historique des operations d'un utilisateur (en tant qu'emetteur OU destinataire),
     * avec le libelle du type de transaction et le montant du frais applique.
     */
    public function historique(int $idUser): array
    {
        $db = \Config\Database::connect();

        $sql = "SELECT
                    t.id_transaction,
                    t.date,
                    t.montant,
                    tt.nom AS type_nom,
                    t.id1,
                    t.id2,
                    COALESCE(mf.frai, 0) AS frai
                FROM transactions t
                LEFT JOIN type_transaction tt ON tt.id = t.id_type
                LEFT JOIN Montant_frai mf ON mf.idMontantFrai = t.idMontant_frai
                WHERE t.id1 = ? OR t.id2 = ?
                ORDER BY t.date DESC, t.id_transaction DESC";

        return $db->query($sql, [$idUser, $idUser])->getResultArray();
    }
}
