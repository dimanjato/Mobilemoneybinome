<?php
namespace App\Models;

use CodeIgniter\Model;
class UserModel extends Model{
    protected $table = 'user';
    protected $primaryKey = 'id_user';
    protected $allowedFields = ['sufixe', 'prefixe', 'nom'];
    public function getUserByPhoneNumber($phoneNumber)
    {
        $cleanNumber      = preg_replace('/[^0-9]/', '', $phoneNumber);
        $prefixeRecherche = substr($cleanNumber, 0, 3);
        $sufixeRecherche  = substr($cleanNumber, 3);

        return $this->where('prefixe', $prefixeRecherche)
                    ->where('sufixe', $sufixeRecherche)
                    ->first();
    }
}
?>