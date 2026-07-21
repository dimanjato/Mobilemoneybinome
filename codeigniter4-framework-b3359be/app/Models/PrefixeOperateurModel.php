<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Table de liaison entre un prefixe (ex: "032") et l'operateur auquel il
 * appartient. Un meme operateur peut posseder plusieurs prefixes : chaque
 * ligne de cette table = un couple (prefixe, operateur).
 */
class PrefixeOperateurModel extends Model
{
    protected $table         = 'prefixe_opateurateur';
    protected $primaryKey    = 'id_prefixe';
    protected $allowedFields = ['nom', 'id_operateur'];
    protected $returnType    = 'array';

    /**
     * Retrouve l'operateur (avec sa commission) rattache a un prefixe donne.
     * Utile pour savoir "a qui appartient ce numero" avant de calculer une commission.
     */
    public function getOperateurByPrefixe(string $prefixe): ?array
    {
        return $this->db->table('prefixe_opateurateur po')
            ->select('o.id_operateur, o.nom, o.commission')
            ->join('operateur o', 'o.id_operateur = po.id_operateur')
            ->where('po.nom', $prefixe)
            ->get()
            ->getRowArray();
    }
}
