<?php

namespace App\Models;

/**
 * Class SoldeModel
 * Modèle de données simple non lié à la base de données.
 */
class SoldeModel
{
    public int $idUser;
    public float $solde;
    public string $date;

    /**
     * Constructeur pour initialiser le modèle avec ses arguments
     * 
     * @param int $idUser
     * @param float $solde
     * @param string|null $date Si null, prendra la date et l'heure actuelles
     */
    public function __construct(int $idUser = 0, float $solde = 0.0, ?string $date = null)
    {
        $this->idUser = $idUser;
        $this->solde = $solde;
        // Si aucune date n'est fournie, on prend l'instant présent au format Y-m-d H:i:s
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
}