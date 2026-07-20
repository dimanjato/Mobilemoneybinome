<?php

namespace App\Models;

use CodeIgniter\Model;

class MontantModel extends Model
{
    protected $table      = 'Montant_frai';
    protected $primaryKey = 'idMontantFrai';
    protected $allowedFields = ['Montant1', 'Montant2', 'frai', 'idtype_transaction'];

    /**
     * Retourne la ligne de bareme (tranche) correspondant a un montant
     * et un type de transaction donnes (1=retrait, 2=depot, 3=transfert).
     */
    public function getTranche(float $montant, int $idTypeTransaction)
    {
        return $this->where('idtype_transaction', $idTypeTransaction)
                    ->where('Montant1 <=', $montant)
                    ->where('Montant2 >=', $montant)
                    ->first();
    }

    /**
     * Retourne directement le montant du frais applicable.
     * Renvoie null si aucune tranche ne correspond (montant hors bareme).
     */
    public function getFrais(float $montant, int $idTypeTransaction): ?float
    {
        $tranche = $this->getTranche($montant, $idTypeTransaction);
        return $tranche ? (float) $tranche['frai'] : null;
    }
}
