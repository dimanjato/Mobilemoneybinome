<?php

namespace App\Controllers;

<<<<<<< HEAD
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
=======
use App\Controllers\BaseController;
use App\Models\SoldeUserModel;
use CodeIgniter\HTTP\ResponseInterface;

class SoldeController extends BaseController
{
    protected $soldeModel;
    protected $session;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Initialisation du modèle pour l'avoir à disposition dans toutes les méthodes
        $this->soldeModel = new SoldeUserModel();
        $this->session = \Config\Services::session();
    }

    /**
     * API : Récupère le solde actuel d'un utilisateur
     * Exemple URL : /solde/actuel/1
     */
    public function actuel()
    {
        // Récupération de l'id_user stocké dans la session
        $id_user = $this->session->get('id_user');
        
        $soldeActuel = $this->soldeModel->getSoldeActuel($id_user) ?? [
        'id_user' => $id_user,
        'solde'   => 0.0
        ];

        // On passe les données à un tableau
        $data['soldeData'] = $soldeActuel;

        // On charge la vue en lui passant le tableau $data
        return view('solde_view', $data);
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
>>>>>>> origin/dev2
    }
}