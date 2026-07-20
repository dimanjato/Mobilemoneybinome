<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SoldeUserModel;
use CodeIgniter\HTTP\ResponseInterface;

class SoldeController extends BaseController
{
    protected $soldeModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Initialisation du modèle pour l'avoir à disposition dans toutes les méthodes
        $this->soldeModel = new SoldeUserModel();
    }

    /**
     * API : Récupère le solde actuel d'un utilisateur
     * Exemple URL : /solde/actuel/1
     */
    public function actuel(int $id_user): ResponseInterface
    {
        $soldeActuel = $this->soldeModel->getSoldeActuel($id_user);

        if (!$soldeActuel) {
            return $this->response->setJSON([
                'id_user' => $id_user,
                'solde'   => 0.0,
                'message' => 'Aucun solde enregistré pour cet utilisateur.'
            ])->setStatusCode(200);
        }

        return $this->response->setJSON($soldeActuel)->setStatusCode(200);
    }

    /**
     * API : Récupère l'historique des soldes d'un utilisateur
     * Exemple URL : /solde/historique/1
     */
    public function historique(int $id_user): ResponseInterface
    {
        $historique = $this->soldeModel->getHistorique($id_user);

        if (empty($historique)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Aucun historique trouvé pour cet utilisateur.'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON($historique)->setStatusCode(200);
    }

    /**
     * API : Enregistrer une évolution de solde (via requête POST)
     * Exemple URL : /solde/ajouter
     */
    public function ajouter(): ResponseInterface
    {
        // Récupération des données JSON envoyées dans la requête POST
        $json = $this->request->getJSON();

        if (!$json || !isset($json->id_user) || !isset($json->solde)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Les champs id_user et solde sont obligatoires.'
            ])->setStatusCode(400);
        }

        try {
            $this->soldeModel->ajouterSolde((int)$json->id_user, (float)$json->solde);
            
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Nouveau solde enregistré avec succès.'
            ])->setStatusCode(201);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Impossible d\'enregistrer le solde.',
                'details' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}