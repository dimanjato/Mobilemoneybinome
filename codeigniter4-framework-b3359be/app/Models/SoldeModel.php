<?php

namespace App\Models;

/**
 * Class SoldeModel
 * Modèle de données simple.
 */
class SoldeModel
{
    public int $idUser;
    public float $solde;
    public string $date;

    /**
     * Constructeur pour initialiser le modèle avec ses arguments
     */
    public function __construct(int $idUser = 0, float $solde = 0.0, ?string $date = null)
    {
        $this->idUser = $idUser;
        $this->solde = $solde;
        $this->date = $date ?? date('Y-m-d H:i:s');
    }

    /**
     * Convertir l'objet en tableau associatif
     */
    public function toArray(): array
    {
        return [
            'id_user' => $this->idUser,
            'solde'   => $this->solde,
            'date'    => $this->date
        ];
    }

    /**
     * Récupère le solde calculé depuis la vue SQL à une date/heure précise
     * 
     * @param int $idUser ID de l'utilisateur
     * @param string $dateHeureLimite Date limite (Format: Y-m-d H:i:s)
     * @return SoldeModel
     */
    public static function getSoldeAu(int $idUser, string $dateHeureLimite): SoldeModel
    {
        // 1. Connexion manuelle à la base de données
        $db = \Config\Database::connect();

        // 2. Requête SQL basée sur ta vue
        // On somme tous les impacts passés avant la date limite pour avoir le solde final à cet instant
        $sql = "SELECT COALESCE(SUM(impact_solde), 0.0) AS solde_final 
                FROM view_calcul_releve 
                WHERE id_utilisateur = ? AND date < ?";

        $query = $db->query($sql, [$idUser, $dateHeureLimite]);
        $row = $query->getRow();

        // 3. Extraction du montant calculé
        $soldeCalcule = $row ? (float)$row->solde_final : 0.0;

        // 4. On retourne une nouvelle instance de SoldeModel avec le résultat
        return new self($idUser, $soldeCalcule, $dateHeureLimite);
    }
}