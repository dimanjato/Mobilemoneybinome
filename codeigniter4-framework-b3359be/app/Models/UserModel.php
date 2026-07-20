<?php

namespace App\Models;

use CodeIgniter\Model;

class SoldeUserModel extends Model
{
    protected $table            = 'solde_user';
    // On indique une fausse clé ou pas de clé unique stricte pour contourner la limitation CI4
    protected $primaryKey       = 'id_user'; 
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id_user', 'solde', 'date'];

    /**
     * Récupère le solde actuel (le plus récent) d'un utilisateur
     */
    public function getSoldeActuel(int $id_user)
    {
        return $this->where('id_user', $id_user)
                    ->orderBy('date', 'DESC')
                    ->first();
    }

    /**
     * Récupère l'historique complet des soldes d'un utilisateur
     */
    public function getHistorique(int $id_user)
    {
        return $this->where('id_user', $id_user)
                    ->orderBy('date', 'DESC')
                    ->findAll();
    }

    /**
     * Enregistre un nouveau solde (la date sera générée par SQLite ou passée manuellement)
     */
    public function ajouterSolde(int $id_user, float $solde)
    {
        $data = [
            'id_user' => $id_user,
            'solde'   => $solde,
            // Optionnel : on laisse SQLite mettre le DATETIME('now') par défaut, 
            // ou on le gère ici en PHP :
            'date'    => date('Y-m-d H:i:s') 
        ];

        return $this->insert($data);
    }
}