<?php

namespace App\Controllers;

use App\Models\PrefixeModel;
use App\Models\TypeTransactionModel;
use App\Models\MontantFraiModel;

class OperateurController extends BaseController
{
    // 1. Affichage de la page de configuration globale
    public function index()
    {
        $prefixeModel = new PrefixeModel();
        $typeModel    = new TypeTransactionModel();
        $fraiModel    = new MontantFraiModel();

        $data['prefixes']   = $prefixeModel->findAll();
        $data['operations'] = $typeModel->findAll();
        $data['frais']      = $fraiModel->getFraisAvecOperation();

        // On va chercher le fichier dans app/Views/operateur/operateur_view.php.php
        return view('operateur/operateur_view.php', $data);
    }

    // 2. Traitement du formulaire d'ajout de préfixe
    public function addPrefixe()
    {
        $prefixeModel = new PrefixeModel();
        $nom          = $this->request->getPost('nom');

        if (!empty($nom)) {
            $prefixeModel->save(['nom' => $nom]);
            return redirect()->to('/operateur/config')->with('success', 'Préfixe ajouté avec succès !');
        }
        return redirect()->back()->with('error', 'Le champ préfixe est vide.');
    }

    // 3. Traitement du formulaire d'ajout d'opération
    public function addOperation()
    {
        $typeModel = new TypeTransactionModel();
        $nom       = $this->request->getPost('nom');

        if (!empty($nom)) {
            $typeModel->save(['nom' => $nom]);
            return redirect()->to('/operateur/config')->with('success', 'Type d\'opération ajouté !');
        }
        return redirect()->back()->with('error', 'Le champ opération est vide.');
    }

    // 4. Traitement du formulaire de la grille des frais
    public function addFrai()
    {
        $fraiModel = new MontantFraiModel();

        $idType   = $this->request->getPost('idtype_transaction');
        $montant1 = $this->request->getPost('Montant1');
        $montant2 = $this->request->getPost('Montant2');
        $frai     = $this->request->getPost('frai');

        if (!empty($idType) && isset($montant1) && isset($montant2) && isset($frai)) {
            $fraiModel->save([
                'Montant1'           => $montant1,
                'Montant2'           => $montant2,
                'frai'               => $frai,
                'idtype_transaction' => $idType
            ]);
            return redirect()->to('/operateur/config')->with('success', 'Borne de frais enregistrée avec succès !');
        }
        return redirect()->back()->with('error', 'Veuillez remplir tous les champs de l\'intervalle.');
    }

    public function listeSoldes()
{
    $db = \Config\Database::connect();

    // 1. On s'assure que la vue est créée/mise à jour dans la base de données
    $db->query("DROP VIEW IF EXISTS view_calcul_releve;");
    
    $db->query("
        CREATE VIEW view_calcul_releve AS
        SELECT
            t.id1 AS id_utilisateur,
            t.date AS date,
            CASE
                WHEN t.id_type = 2 THEN t.montant
                WHEN t.id_type = 1 THEN -(t.montant + COALESCE(mf.frai, 0))
                WHEN t.id_type = 3 THEN -(t.montant + COALESCE(mf.frai, 0))
                ELSE 0
            END AS impact_solde
        FROM transactions t
        LEFT JOIN Montant_frai mf ON t.idMontant_frai = mf.idMontantFrai
        WHERE t.id1 IS NOT NULL
        UNION ALL
        SELECT
            t.id2 AS id_utilisateur,
            t.date AS date,
            t.montant AS impact_solde
        FROM transactions t
        WHERE t.id_type = 3 AND t.id2 IS NOT NULL;
    ");

    // 2. On calcule le solde total par utilisateur en joignant sa table de profil
    // Ajuste 'utilisateurs' et le champ 'nom' ou 'telephone' selon ta structure
    $query = $db->query("
        SELECT 
            u.id, 
            u.nom, 
            u.telephone, 
            COALESCE(SUM(v.impact_solde), 0) AS solde_actuel
        FROM utilisateurs u
        LEFT JOIN view_calcul_releve v ON u.id = v.id_utilisateur
        GROUP BY u.id
    ");

    $data['utilisateurs_soldes'] = $query->getResultArray();

    // 3. Retourne le résultat à ta vue globale ou une nouvelle vue
    return view('operateur/admin_config_view', $data); 
}
}