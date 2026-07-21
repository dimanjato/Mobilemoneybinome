<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Liste generale des prefixes valides (ex: "033", "037"), independante de
 * leur rattachement eventuel a un operateur (voir PrefixeOperateurModel).
 */
class PrefixeModel extends Model
{
    protected $table            = 'prefixe';
    protected $primaryKey       = 'id_prefixe';
    protected $allowedFields    = ['nom'];
    protected $returnType       = 'array';

    /**
     * Ajoute le prefixe s'il n'existe pas deja (evite les doublons).
     */
    public function getOrCreateByNom(string $nom): array
    {
        $existant = $this->where('nom', $nom)->first();
        if ($existant) {
            return $existant;
        }

        $id = $this->insert(['nom' => $nom], true);
        return $this->find($id);
    }
}
