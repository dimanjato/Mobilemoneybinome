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
     * Traite la tentative de connexion (avec inscription automatique)
     */
    public function login()
    {
        $session      = session();
        $userModel    = new UserModel();
        $prefixeModel = new PrefixeModel();

        // Récupération sécurisée du champ "phone" envoyé en POST
        $numeroSaisi = $this->request->getPost('phone');

        if (empty($numeroSaisi)) {
            $session->setFlashdata('error', 'Veuillez entrer votre numero de telephone.');
            return redirect()->to('/login');
        }

        // Nettoyage complet du numéro (uniquement des chiffres)
        $cleanNumber = preg_replace('/[^0-9]/', '', $numeroSaisi);

        if (strlen($cleanNumber) < 4) {
            $session->setFlashdata('error', 'Numero de telephone invalide.');
            return redirect()->to('/login');
        }

        // 1. EXTRACTION ET VÉRIFICATION DU PRÉFIXE (ex: 034, 032, etc.)
        $prefixeSaisi = substr($cleanNumber, 0, 3);
        $prefixeExiste = $prefixeModel->where('nom', $prefixeSaisi)->first();

        if (!$prefixeExiste) {
            $session->setFlashdata('error', "Le préfixe '{$prefixeSaisi}' n'est pas pris en charge par notre réseau Mobile Money.");
            return redirect()->to('/login'); // Redirection vers la bonne route
        }

        // 2. RÉCUPÉRATION OU CRÉATION DU COMPTE CLIENT
        // Correction ici : on utilise le bon modèle $userModel et la variable nettoyée
        $user = $userModel->getOrCreateUserByPhoneNumber($cleanNumber);

        if ($user) {
            // Connexion réussie : stockage dans la session de l'application
            $session->set([
                'id_user'    => $user['id_user'],
                'nom'        => $user['nom'],
                'isLoggedIn' => true,
            ]);

            // Redirection vers le solde du client
            return redirect()->to('/client/voirsolde');
        }

        // Échec de sécurité générique
        $session->setFlashdata('error', 'Impossible de valider votre numéro de téléphone.');
        return redirect()->to('/login');
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}