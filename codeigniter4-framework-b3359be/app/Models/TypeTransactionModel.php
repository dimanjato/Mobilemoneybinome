<?php
namespace App\Models;
use CodeIgniter\Model;

class TypeTransactionModel extends Model {
    protected $table = 'type_transaction';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nom'];
}