<?php

namespace App\Controllers;

use App\Models\MontantModel;
use App\Models\SoldeModel;
use App\Models\TransactionModel;
use App\Models\UserModel;

class TransactionController extends BaseController
{
    protected $session;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();
    }

    private function soldeActuel(int $idUser): float
    {
        $solde = SoldeModel::getSoldeAu($idUser, date('Y-m-d H:i:s'));
        return $solde->solde;
    }

    /**
     * Faire un depot (suppose automatique) : +montant, sans frais.
     */
    public function depot()
    {
        $idUser = $this->session->get('id_user');

        if ($this->request->getMethod() === 'POST') {
            $montant = (float) $this->request->getPost('montant');

            if ($montant <= 0) {
                $this->session->setFlashdata('error', 'Veuillez saisir un montant valide.');
                return redirect()->to('/client/depot');
            }

            $model = new TransactionModel();
            $model->enregistrerDepot($idUser, $montant);

            $this->session->setFlashdata('success', 'Depot de ' . number_format($montant, 0, ',', ' ') . ' Ar effectue avec succes.');
            return redirect()->to('/client/voirsolde');
        }

        return view('depot_view', ['solde' => $this->soldeActuel($idUser)]);
    }

    /**
     * Faire un retrait (suppose automatique) : -(montant + frais).
     */
    public function retrait()
    {
        $idUser = $this->session->get('id_user');

        if ($this->request->getMethod() === 'POST') {
            $montant     = (float) $this->request->getPost('montant');
            $montantModel = new MontantModel();

            if ($montant <= 0) {
                $this->session->setFlashdata('error', 'Veuillez saisir un montant valide.');
                return redirect()->to('/client/retrait');
            }

            $tranche = $montantModel->getTranche($montant, TransactionModel::TYPE_RETRAIT);
            if (!$tranche) {
                $this->session->setFlashdata('error', 'Aucun bareme de frais ne correspond a ce montant.');
                return redirect()->to('/client/retrait');
            }

            $frais       = (float) $tranche['frai'];
            $soldeActuel = $this->soldeActuel($idUser);

            if ($soldeActuel < ($montant + $frais)) {
                $this->session->setFlashdata('error', 'Solde insuffisant pour ce retrait (montant + frais de ' . number_format($frais, 0, ',', ' ') . ' Ar).');
                return redirect()->to('/client/retrait');
            }

            $model = new TransactionModel();
            $model->enregistrerRetrait($idUser, $montant, $tranche['idMontantFrai']);

            $this->session->setFlashdata('success', 'Retrait de ' . number_format($montant, 0, ',', ' ') . ' Ar effectue (frais : ' . number_format($frais, 0, ',', ' ') . ' Ar).');
            return redirect()->to('/client/voirsolde');
        }

        return view('retrait_view', ['solde' => $this->soldeActuel($idUser)]);
    }

    /**
     * Faire un transfert vers un autre numero.
     * Si le destinataire n'a pas encore de compte, il est cree automatiquement
     * (meme logique que la connexion : pas d'inscription prealable).
     */
    public function transfert()
    {
        $idUser = $this->session->get('id_user');

        if ($this->request->getMethod() === 'POST') {
            $montant       = (float) $this->request->getPost('montant');
            $numeroDest    = (string) $this->request->getPost('numero');
            $userModel     = new UserModel();
            $montantModel  = new MontantModel();

            if ($montant <= 0 || empty($numeroDest)) {
                $this->session->setFlashdata('error', 'Veuillez saisir un montant et un numero valides.');
                return redirect()->to('/client/transfert');
            }

            $destinataire = $userModel->getOrCreateUserByPhoneNumber($numeroDest);

            if (!$destinataire) {
                $this->session->setFlashdata('error', 'Numero de destinataire invalide.');
                return redirect()->to('/client/transfert');
            }

            if ((int) $destinataire['id_user'] === (int) $idUser) {
                $this->session->setFlashdata('error', 'Vous ne pouvez pas vous transferer de l\'argent a vous-meme.');
                return redirect()->to('/client/transfert');
            }

            $tranche = $montantModel->getTranche($montant, TransactionModel::TYPE_TRANSFERT);
            if (!$tranche) {
                $this->session->setFlashdata('error', 'Aucun bareme de frais ne correspond a ce montant.');
                return redirect()->to('/client/transfert');
            }

            $frais       = (float) $tranche['frai'];
            $soldeActuel = $this->soldeActuel($idUser);

            if ($soldeActuel < ($montant + $frais)) {
                $this->session->setFlashdata('error', 'Solde insuffisant pour ce transfert (montant + frais de ' . number_format($frais, 0, ',', ' ') . ' Ar).');
                return redirect()->to('/client/transfert');
            }

            $model = new TransactionModel();
            $model->enregistrerTransfert($idUser, $destinataire['id_user'], $montant, $tranche['idMontantFrai']);

            $this->session->setFlashdata('success', 'Transfert de ' . number_format($montant, 0, ',', ' ') . ' Ar effectue vers ' . $destinataire['prefixe'] . $destinataire['sufixe'] . ' (frais : ' . number_format($frais, 0, ',', ' ') . ' Ar).');
            return redirect()->to('/client/voirsolde');
        }

        return view('transfert_view', ['solde' => $this->soldeActuel($idUser)]);
    }

    /**
     * Historique des operations de l'utilisateur connecte.
     */
    public function historique()
    {
        $idUser = $this->session->get('id_user');
        $model  = new TransactionModel();

        return view('historique_view', [
            'operations' => $model->historique($idUser),
            'idUser'     => $idUser,
        ]);
    }
}
