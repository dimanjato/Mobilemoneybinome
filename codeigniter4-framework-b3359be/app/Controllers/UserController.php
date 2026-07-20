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

        // 1. EXTRACTION ET VÉRIFICATION DU PRÉFIXE (ex: "034", "032")
        $prefixeSaisi = substr($cleanNumber, 0, 3);

        // On cherche si ce préfixe existe dans la table 'prefixe'
        $prefixeExiste = $prefixeModel->where('nom', $prefixeSaisi)->first();

        if (!$prefixeExiste) {
            $session->setFlashdata('error', "Le préfixe '{$prefixeSaisi}' n'est pas pris en charge par notre réseau Mobile Money.");
            return redirect()->to('/login');
        }

        // 2. RÉCUPÉRATION OU CRÉATION AUTOMATIQUE DU COMPTE
        // Utilisation de $userModel (et non $model) pour corriger l'erreur critique
        $user = $userModel->getOrCreateUserByPhoneNumber($cleanNumber);

        if ($user) {
            // Connexion reussie : on stocke les infos de l'utilisateur dans la session
            $session->set([
                'id_user'    => $user['id_user'],
                'nom'        => $user['nom'] ?? 'Client', // Sécurité au cas où le nom est vide à la création
                'isLoggedIn' => true,
            ]);

            // Redirection vers le tableau de bord
            return redirect()->to('/client/voirsolde');
        }

        // Echec de sécurité
        $session->setFlashdata('error', 'Impossible de charger ou de créer votre compte.');
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
