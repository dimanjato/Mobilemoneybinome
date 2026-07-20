<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SoldeModel;

class SoldController extends BaseController
{
    protected $session;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Initialisation de la session
        $this->session = \Config\Services::session();
    }

    /**
     * Affiche le solde actuel de l'utilisateur connecté basé sur la vue SQL
     */
    public function actuel()
    {
        // 1. Récupération de l'id_user depuis la session
        $id_user = $this->session->get('id_user');

        // Sécurité : Si l'utilisateur n'est pas connecté, on le redirige ou on l'arrête
        if (!$id_user) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter pour voir votre solde.');
        }

        // 2. Prendre la date et l'heure exacte d'aujourd'hui (instant présent)
        $dateAujourdhui = date('Y-m-d H:i:s');

        // 3. Appel de la fonction présente dans SoldeModel
        $soldeObjet = SoldeModel::getSoldeAu((int)$id_user, $dateAujourdhui);

        // 4. Préparation des données pour la vue
        $data['soldeData'] = $soldeObjet;

        // 5. Retour de la vue avec les données du solde
        return view('solde_view', $data);
    }
}