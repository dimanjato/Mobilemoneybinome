<?php

namespace App\Controllers;

use App\Models\PrefixeModel;
use App\Models\TypeTransactionModel;
use App\Models\MontantFraiModel;
use App\Models\OperateurModel;

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
    /**
     * 5. Affichage de la page des autres opérateurs (Gains et historique filtré)
     */
    public function autreOperateurIndex()
    {
        $db = \Config\Database::connect();

        // Récupération des filtres depuis l'URL (GET)
        $filtreOperateur = $this->request->getGet('operateur');
        $filtrePrefixe   = $this->request->getGet('prefixe');
        $filtreDate      = $this->request->getGet('date');

        // A. Liste des autres opérateurs (exclut le tien, par exemple 033 et 037)
        $data['autresOperateurs'] = $db->table('operateur')
                                       ->whereNotIn('prefixe', ['033', '037'])
                                       ->get()
                                       ->getResultArray();

        // B. Somme des gains via les commissions des autres opérateurs
        // Formule : montant_transaction * (commition / 100)
        $gainQuery = $db->table('transactions t')
                        ->select('SUM(t.montant * (o.commition / 100)) as total_gains')
                        ->join('user u', 't.id2 = u.id_user') // Le récepteur du transfert
                        ->join('operateur o', 'u.prefixe = o.prefixe')
                        ->where('t.id_type', 3) // Uniquement les transferts
                        ->whereNotIn('o.prefixe', ['033', '037'])
                        ->get()
                        ->getRowArray();
        
        $data['totalGains'] = $gainQuery['total_gains'] ?? 0.0;

        // C. Construction de l'historique des montants envoyés aux autres opérateurs avec filtres
        $histBuilder = $db->table('transactions t')
                          ->select('
                              (u.prefixe || u.sufixe) as numero_destinataire,
                              t.montant as montant,
                              o.commition as pourcentage,
                              (t.montant * (o.commition / 100)) as gain_commission,
                              COALESCE(mf.frai, 0) as frais,
                              t.date as date_transaction,
                              o.nom as operateur_nom
                          ')
                          ->join('user u', 't.id2 = u.id_user')
                          ->join('operateur o', 'u.prefixe = o.prefixe')
                          ->leftJoin('Montant_frai mf', 't.idMontant_frai = mf.idMontantFrai')
                          ->where('t.id_type', 3)
                          ->whereNotIn('o.prefixe', ['033', '037']);

        // Application dynamique des filtres de recherche
        if (!empty($filtreOperateur)) {
            $histBuilder->where('o.id_operateur', $filtreOperateur);
        }
        if (!empty($filtrePrefixe)) {
            $histBuilder->where('u.prefixe', $filtrePrefixe);
        }
        if (!empty($filtreDate)) {
            $histBuilder->where('DATE(t.date)', $filtreDate);
        }

        $data['historique'] = $histBuilder->orderBy('t.date', 'DESC')->get()->getResultArray();
        
        // Liste complète pour remplir les listes déroulantes (Select) des filtres
        $data['listeOperateursFiltrable'] = $db->table('operateur')->get()->getResultArray();
        $data['listePrefixesFiltrable']   = $db->table('prefixe')->select('nom')->distinct()->get()->getResultArray();
        
        // Mémorisation des filtres pour les réafficher dans les champs
        $data['filtres'] = [
            'operateur' => $filtreOperateur,
            'prefixe'   => $filtrePrefixe,
            'date'      => $filtreDate
        ];

        return view('operateur/autre_operateur_view', $data);
    }

    /**
     * 6. Traitement du formulaire d'ajout d'un nouvel opérateur (avec plusieurs préfixes possibles)
     */
    public function addOperateurTiers()
    {
        $db           = \Config\Database::connect();
        $prefixeModel = new PrefixeModel();

        $nom      = $this->request->getPost('nom');
        $prct     = $this->request->getPost('prct');
        $prefixes = $this->request->getPost('prefixe'); // Reçoit une chaîne ex: "032,034"

        if (empty($nom) || empty($prefixes)) {
            return redirect()->back()->with('error', 'Le nom et au moins un préfixe sont obligatoires.');
        }

        // Découpage des préfixes saisis par virgule
        $tabPrefixes = explode(',', $prefixes);
        $prefixePrincipal = trim($tabPrefixes[0]);

        // A. Insertion dans la table operateur (On utilise 'commition' pour correspondre à ton schéma)
        $dataOperateur = [
            'nom'       => $nom,
            'prefixe'   => $prefixePrincipal,
            'commition' => floatval($prct ?? 0.0)
        ];
        
        $db->table('operateur')->insert($dataOperateur);
        $idOperateur = $db->insertID();

        // B. Insertion de chaque préfixe dans la table prefixe associée
        if ($idOperateur) {
            foreach ($tabPrefixes as $pref) {
                $cleanPref = trim($pref);
                if (!empty($cleanPref)) {
                    $prefixeModel->save([
                        'nom'          => $cleanPref,
                        'id_operateur' => $idOperateur
                    ]);
                }
            }
            return redirect()->to('/operateur/autre')->with('success', 'Nouvel opérateur et ses préfixes configurés !');
        }

        return redirect()->back()->with('error', 'Une erreur est survenue.');
    }

    /**
     * 7. Page "Situation des montants globaux à envoyer à chaque opérateur"
     */
    public function situationMontantsAEnvoyer()
    {
        $db = \Config\Database::connect();

        // Somme brute des transferts groupée par opérateur récepteur
        $query = $db->query("
            SELECT 
                o.nom as operateur_nom,
                o.prefixe as operateur_prefixe,
                COUNT(t.id_transaction) as total_transferts,
                COALESCE(SUM(t.montant), 0) as montant_global_brut
            FROM transactions t
            JOIN user u ON t.id2 = u.id_user
            JOIN operateur o ON u.prefixe = o.prefixe
            WHERE t.id_type = 3 -- Uniquement transferts
            AND o.prefixe NOT IN ('033', '037') -- Vers les autres opérateurs uniquement
            GROUP BY o.id_operateur
        ");

        $data['situation_flux'] = $query->getResultArray();

        return view('operateur/situation_flux_view', $data);
    }
}