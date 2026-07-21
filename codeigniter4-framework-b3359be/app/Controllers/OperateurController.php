<?php

namespace App\Controllers;

use App\Models\PrefixeModel;
use App\Models\PrefixeOperateurModel;
use App\Models\TypeTransactionModel;
use App\Models\MontantFraiModel;
use App\Models\OperateurModel;
use App\Models\UserModel;
use App\Models\SoldeModel;

class OperateurController extends BaseController
{
    // 1. Affichage de la page de configuration globale
    public function index()
    {
        $prefixeModel = new PrefixeModel();
        $typeModel    = new TypeTransactionModel();
        $fraiModel    = new MontantFraiModel();
        $userModel    = new UserModel();

        $data['prefixes']   = $prefixeModel->findAll();
        $data['operations'] = $typeModel->findAll();
        $data['frais']      = $fraiModel->getFraisAvecOperation();

        $db = \Config\Database::connect();

        // Resume des frais reellement collectes, groupes par type d'operation
        // (cette variable n'etait auparavant jamais transmise a la vue)
        $data['resume_frais'] = $db->query("
            SELECT
                tt.nom AS operation_nom,
                COUNT(t.id_transaction) AS nombre_transactions,
                COALESCE(SUM(mf.frai), 0) AS total_frais
            FROM transactions t
            JOIN type_transaction tt ON tt.id = t.id_type
            LEFT JOIN Montant_frai mf ON mf.idMontantFrai = t.idMontant_frai
            GROUP BY t.id_type
        ")->getResultArray();

        // Liste des utilisateurs avec leur solde actuel (idem, jamais transmise avant)
        $utilisateurs = $userModel->findAll();
        $data['utilisateurs_soldes'] = array_map(function ($u) {
            $solde = SoldeModel::getSoldeAu((int) $u['id_user'], date('Y-m-d H:i:s'));
            return [
                'id'           => $u['id_user'],
                'nom'          => $u['nom'],
                'telephone'    => $u['prefixe'] . $u['sufixe'],
                'solde_actuel' => $solde->solde,
            ];
        }, $utilisateurs);

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
    /**
     * 5. Affichage de la page des autres opérateurs (Gains et historique filtré)
     */
    public function autreOperateurIndex()
    {
        $db             = \Config\Database::connect();
        $operateurModel = new OperateurModel();

        // Récupération des filtres depuis l'URL (GET)
        $filtres = [
            'operateur' => $this->request->getGet('operateur'),
            'prefixe'   => $this->request->getGet('prefixe'),
            'date'      => $this->request->getGet('date'),
        ];

        // On reutilise les methodes du modele (au lieu de dupliquer le SQL ici)
        $data['autresOperateurs'] = $operateurModel->getAutresOperateurs();
        $data['totalGains']       = $operateurModel->getSommeGainsAutres();
        $data['historique']       = $operateurModel->getHistoriqueAutres($filtres);

        // Liste complète pour remplir les listes déroulantes (Select) des filtres
        $data['listeOperateursFiltrable'] = $operateurModel->findAll();
        $data['listePrefixesFiltrable']   = $db->table('prefixe')->select('nom')->distinct()->get()->getResultArray();

        // Mémorisation des filtres pour les réafficher dans les champs
        $data['filtres'] = $filtres;

        return view('operateur/autre_operateur', $data);
    }

    /**
     * 6. Traitement du formulaire d'ajout d'un nouvel opérateur (avec plusieurs préfixes possibles)
     */
    public function addOperateurTiers()
    {
        $operateurModel        = new OperateurModel();
        $prefixeModel          = new PrefixeModel();
        $prefixeOperateurModel = new PrefixeOperateurModel();

        $nom      = $this->request->getPost('nom');
        $prct     = $this->request->getPost('prct');
        $prefixes = $this->request->getPost('prefixe'); // Reçoit une chaîne ex: "032,034"

        if (empty($nom) || empty($prefixes)) {
            return redirect()->back()->with('error', 'Le nom et au moins un préfixe sont obligatoires.');
        }

        // Découpage des préfixes saisis par virgule
        $tabPrefixes = array_filter(array_map('trim', explode(',', $prefixes)));

        // A. Insertion dans la table operateur
        $idOperateur = $operateurModel->insert([
            'nom'        => $nom,
            'commission' => floatval($prct ?? 0.0),
        ], true);

        if (!$idOperateur) {
            return redirect()->back()->with('error', 'Une erreur est survenue.');
        }

        // B. Rattache chaque préfixe à ce nouvel opérateur, et s'assure qu'il
        //    figure aussi dans la liste générale des préfixes valides.
        foreach ($tabPrefixes as $cleanPref) {
            $prefixeModel->getOrCreateByNom($cleanPref);
            $prefixeOperateurModel->save([
                'nom'          => $cleanPref,
                'id_operateur' => $idOperateur,
            ]);
        }

        return redirect()->to('/operateur/autre')->with('success', 'Nouvel opérateur et ses préfixes configurés !');
    }

    /**
     * 7. Page "Situation des montants globaux à envoyer à chaque opérateur"
     */
    public function situationMontantsAEnvoyer()
    {
        $db = \Config\Database::connect();

        // Somme brute des transferts groupée par opérateur récepteur
        // (un operateur pouvant avoir plusieurs prefixes, on les regroupe avec GROUP_CONCAT)
        $query = $db->query("
            SELECT
                o.nom as operateur_nom,
                GROUP_CONCAT(DISTINCT po.nom) as operateur_prefixes,
                COUNT(t.id_transaction) as total_transferts,
                COALESCE(SUM(t.montant), 0) as montant_global_brut
            FROM transactions t
            JOIN user u ON t.id2 = u.id_user
            JOIN prefixe_opateurateur po ON u.prefixe = po.nom
            JOIN operateur o ON po.id_operateur = o.id_operateur
            WHERE t.id_type = 3 -- Uniquement transferts
            AND o.id_operateur != " . OperateurModel::ID_OPERATEUR_PRINCIPAL . " -- Vers les autres operateurs uniquement
            GROUP BY o.id_operateur
        ");

        $data['situation_flux'] = $query->getResultArray();

        return view('operateur/situation_flux_view', $data);
    }
}