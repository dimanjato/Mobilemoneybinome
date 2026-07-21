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
     * Faire un transfert vers un ou plusieurs numeros.
     * Meme montant envoye a chaque destinataire, meme date/heure.
     *
     * @param bool|null $inclureFrais Si true : les frais sont preleves sur le montant saisi
     *                                (le montant total debite = montant saisi).
     *                                Si false : les frais s'ajoutent au montant saisi
     *                                (le montant total debite = montant + frais).
     *                                Si null, la valeur est lue depuis le POST (checkbox).
     */
    public function transfert(?bool $inclureFrais = null)
    {
        $idUser = $this->session->get('id_user');

        if ($this->request->getMethod() === 'POST') {
            $montant      = (float) $this->request->getPost('montant');
            $numeros      = (array) $this->request->getPost('numero');
            $numeros      = array_values(array_filter(array_map('trim', $numeros), fn ($n) => $n !== ''));
            $userModel    = new UserModel();
            $montantModel = new MontantModel();

            // Si non fourni en argument, on lit la case a cocher du formulaire
            if ($inclureFrais === null) {
                $inclureFrais = (bool) $this->request->getPost('inclure_frais');
            }

            if ($montant <= 0 || empty($numeros)) {
                $this->session->setFlashdata('error', 'Veuillez saisir un montant et au moins un numero valides.');
                return redirect()->to('/client/transfert');
            }

            $tranche = $montantModel->getTranche($montant, 1);
            if (!$tranche) {
                $this->session->setFlashdata('error', 'Aucun bareme de frais ne correspond a ce montant.');
                return redirect()->to('/client/transfert');
            }

            $frais = (float) $tranche['frai'];

            // Coeur de la logique demandee :
            // - inclureFrais actif  -> le montant saisi couvre deja les frais (montant inchange)
            // - inclureFrais inactif -> les frais s'ajoutent par-dessus le montant saisi
            if ($inclureFrais) {
                $montant = $montant + $frais;
            } else {
                $montant = $montant;
            }

            $coutParTransfert = $montant;
            $nombreTransferts = count($numeros);
            $coutTotal        = $coutParTransfert * $nombreTransferts;

            $soldeActuel = $this->soldeActuel($idUser);
            if ($soldeActuel < $coutTotal) {
                $this->session->setFlashdata('error', 'Solde insuffisant pour effectuer ' . $nombreTransferts . ' transfert(s) (total requis : ' . number_format($coutTotal, 0, ',', ' ') . ' Ar).');
                return redirect()->to('/client/transfert');
            }

            // On resout et valide TOUS les destinataires avant d'executer quoi que ce soit,
            // pour eviter d'enregistrer une partie des transferts si un numero est invalide.
            $destinataires = [];
            foreach ($numeros as $numeroDest) {
                $destinataire = $userModel->getOrCreateUserByPhoneNumber($numeroDest);

                if (!$destinataire) {
                    $this->session->setFlashdata('error', 'Numero de destinataire invalide : ' . $numeroDest);
                    return redirect()->to('/client/transfert');
                }

                if ((int) $destinataire['id_user'] === (int) $idUser) {
                    $this->session->setFlashdata('error', 'Vous ne pouvez pas vous transferer de l\'argent a vous-meme (' . $numeroDest . ').');
                    return redirect()->to('/client/transfert');
                }

                $destinataires[] = $destinataire;
            }

            $model = new TransactionModel();
            $noms  = [];

            foreach ($destinataires as $destinataire) {
                $model->enregistrerTransfert($idUser, $destinataire['id_user'], $montant, $tranche['idMontantFrai']);
                $noms[] = $destinataire['prefixe'] . $destinataire['sufixe'];
            }

            $this->session->setFlashdata(
                'success',
                $nombreTransferts . ' transfert(s) de ' . number_format($montant, 0, ',', ' ') . ' Ar effectue(s) vers : '
                . implode(', ', $noms) . ' (frais unitaire : ' . number_format($frais, 0, ',', ' ') . ' Ar' . ($inclureFrais ? ', inclus dans le montant' : ', ajoutes au montant') . ').'
            );
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
