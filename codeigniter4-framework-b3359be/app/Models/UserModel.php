<?php
namespace App\Models;

use CodeIgniter\Model;
class UserModel extends Model{
    protected $table = 'user';
    protected $primaryKey = 'id_user';
    protected $allowedFields = ['sufixe', 'prefixe', 'nom'];
    public function getUserByPhoneNumber($phoneNumber)
    {
        [$prefixeRecherche, $sufixeRecherche] = self::splitPhoneNumber($phoneNumber);

        return $this->where('prefixe', $prefixeRecherche)
                    ->where('sufixe', $sufixeRecherche)
                    ->first();
    }

    /**
     * Decoupe un numero de telephone brut en [prefixe (3 chiffres), sufixe (reste)]
     */
    public static function splitPhoneNumber(string $phoneNumber): array
    {
        $cleanNumber      = preg_replace('/[^0-9]/', '', $phoneNumber);
        $prefixeRecherche = substr($cleanNumber, 0, 3);
        $sufixeRecherche  = substr($cleanNumber, 3);

        return [$prefixeRecherche, $sufixeRecherche];
    }

    /**
     * Recupere le compte client associe au numero, ou le cree automatiquement
     * s'il n'existe pas encore (pas d'inscription prealable, cf. sujet).
     */
    public function getOrCreateUserByPhoneNumber(string $phoneNumber)
    {
        $existing = $this->getUserByPhoneNumber($phoneNumber);
        if ($existing) {
            return $existing;
        }

        [$prefixe, $sufixe] = self::splitPhoneNumber($phoneNumber);

        if ($prefixe === '' || $sufixe === '') {
            return null;
        }

        $id = $this->insert([
            'prefixe' => $prefixe,
            'sufixe'  => $sufixe,
            'nom'     => $prefixe . $sufixe,
        ]);

        return $this->find($id);
    }
}
?>