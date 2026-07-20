<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeModel extends Model
{
    protected $table            = 'prefixe';
    protected $primaryKey       = 'id_prefixe';
    protected $allowedFields    = ['nom']; // Seul le champ 'nom' est modifiable
    protected $returnType       = 'array';
}