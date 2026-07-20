<?php
namespace App\Models;
use CodeIgniter\Model;

class MontantFraiModel extends Model {
    protected $table = 'Montant_frai';
    protected $primaryKey = 'idMontantFrai';
    protected $allowedFields = ['Montant1', 'Montant2', 'frai', 'idtype_transaction'];

    // Une fonction pratique pour récupérer les frais avec le nom de l'opération associée
    public function getFraisAvecOperation() {
        return $this->select('Montant_frai.*, type_transaction.nom as operation_nom')
                    ->join('type_transaction', 'type_transaction.id = Montant_frai.idtype_transaction')
                    ->findAll();
    }
}