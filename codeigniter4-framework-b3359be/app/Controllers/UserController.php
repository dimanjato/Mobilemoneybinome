<?php

namespace App\Controllers;

use App\Models\UserModel;

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
     * Traite la tentative de connexion
     */
    public function login()
    {
        $session = session();
        $model   = new UserModel();

        // Récupération sécurisée du champ "phone" envoyé en POST
        $numeroSaisi = $this->request->getPost('phone');

        if (empty($numeroSaisi)) {
            $session->setFlashdata('error', 'Veuillez entrer votre numéro de téléphone.');
            return redirect()->to('/Connexion');
        }

        // Appel de la méthode de notre modèle
        $user = $model->getUserByPhoneNumber($numeroSaisi);

        if ($user) {
            // Connexion réussie : On stocke les infos de l'utilisateur dans la session
            $session->set([
                'id_user'    => $user['id_user'],
                'nom'        => $user['nom'],
                'isLoggedIn' => true
            ]);

            // Redirection vers le tableau de bord de votre application Mobile Money
            return redirect()->to('/dashboard');
        } else {
            // Échec : Aucun utilisateur trouvé
            $session->setFlashdata('error', 'Numéro de téléphone introuvable.');
            return redirect()->to('/Connexion');
        }
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/Connexion');
    }
}