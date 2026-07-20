<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PrefixeModel;

class UserController extends BaseController
{
    /**
     * Affiche la vue de connexion
     */
    public function index()
    {
        return view('Connexion');
    }

    /**
     * Traite la tentative de connexion.
     * Conformement au sujet : "Login automatique avec le numero de telephone,
     * pas d'inscription au prealable" -> si le numero n'existe pas encore,
     * un compte client est cree automatiquement.
     */
    public function login()
    {
        $session      = session();
        $userModel    = new UserModel();
        $prefixeModel = new PrefixeModel();

        // Recuperation securisee du champ "phone" envoye en POST
        $numeroSaisi = $this->request->getPost('phone');

        if (empty($numeroSaisi)) {
            $session->setFlashdata('error', 'Veuillez entrer votre numero de telephone.');
            return redirect()->to('/login');
        }

        $cleanNumber = preg_replace('/[^0-9]/', '', $numeroSaisi);

        if (strlen($cleanNumber) < 4) {
            $session->setFlashdata('error', 'Numero de telephone invalide.');
            return redirect()->to('/login');
        }

        // Recupere le compte existant, ou le cree automatiquement s'il n'existe pas
        $user = $model->getOrCreateUserByPhoneNumber($cleanNumber);
        // 1. EXTRACTION ET VÉRIFICATION DU PRÉFIXE
        // On prend les 3 premiers caractères du numéro (ex: "034" depuis "0341234567")
        $prefixeSaisi = substr($numeroSaisi, 0, 3);

        // On cherche si ce préfixe existe dans la table 'prefixe'
        $prefixeExiste = $prefixeModel->where('nom', $prefixeSaisi)->first();

        if (!$prefixeExiste) {
            // Si le préfixe n'est pas trouvé dans la base
            $session->setFlashdata('error', "Le préfixe '{$prefixeSaisi}' n'est pas pris en charge par notre réseau Mobile Money.");
            return redirect()->to('/Connexion');
        }

        // 2. VÉRIFICATION DE L'UTILISATEUR
        // Si le préfixe est valide, on cherche l'utilisateur complet
        $user = $userModel->getUserByPhoneNumber($numeroSaisi);

        if ($user) {
            // Connexion reussie : on stocke les infos de l'utilisateur dans la session
            $session->set([
                'id_user'    => $user['id_user'],
                'nom'        => $user['nom'],
                'isLoggedIn' => true,
            ]);

            // Redirection vers le tableau de bord de l'application Mobile Money
            return redirect()->to('/client/voirsolde');
        }

        // Echec (numero invalide malgre tout)
        $session->setFlashdata('error', 'Numero de telephone invalide.');
        return redirect()->to('/login');
    }

    /**
     * Deconnexion
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
