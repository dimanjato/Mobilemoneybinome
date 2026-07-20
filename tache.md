# creation de base :
## creation de table :
    - user (id_user, sufixe, prefixe, nom)
    - solde_user (id_user, solde, date)
    type_trasaction(id,nom)
    - transaction (id_transaction, id_type, montant, date, id1,id2, idMontant_frai)
    - Montant_frai (idMontantFrai, Montant1, Montant2, frai)
## Connexion base :
## mise en place de donnee test :
    
## Connexion base :
## mise en place de donnee test :
## Connexion de clients :
    - validation :
    - recherche de compte si existe :
    - mampiditra session client :
## voir le solde : 
    - calul de solde dans table : transactions, et solde_user
    - 
## 
    -

## BASE URL : 

## ETU004067 :
### Voir solde : 
    - creer AuthFIlters : ok
    - creer controller SoldController : ok
    - creer le view qui va calculer le soldes actuelle : ok

### Correction bug connexion (numero ne se connectait pas) :
    - ajout de la vue SQL view_calcul_releve dans base.sql (manquante, utilisee par SoldeModel) : ok
    - correction du typo de tranche de frais 25001->250000 en 250001->500000 dans base.sql : ok
    - UserModel::getOrCreateUserByPhoneNumber() : creation automatique du compte si le numero n'existe pas (conforme au sujet : pas d'inscription au prealable) : ok
    - correction UserController : redirection vers /login (au lieu de /Connexion qui n'existait pas) : ok
    - correction SoldController/solde_view : SoldeModel->toArray() (objet utilise comme tableau) : ok

### Faire un depot :
    - creer TransactionModel::enregistrerDepot() : ok
    - creer TransactionController::depot() (formulaire + traitement) : ok
    - creer la vue depot_view.php : ok

### Faire un retrait :
    - creer MontantModel::getTranche()/getFrais() (bareme de frais par tranche) : ok
    - creer TransactionModel::enregistrerRetrait() : ok
    - creer TransactionController::retrait() avec verification du solde suffisant : ok
    - creer la vue retrait_view.php : ok

### Faire un transfert :
    - creer TransactionModel::enregistrerTransfert() : ok
    - creer TransactionController::transfert() (creation auto du destinataire si besoin, verification solde) : ok
    - creer la vue transfert_view.php : ok

### Voir les historiques :
    - creer TransactionModel::historique() : ok
    - creer TransactionController::historique() : ok
    - creer la vue historique_view.php : ok

### Navigation / UI :
    - creer partials/nav.php (menu commun Solde/Depot/Retrait/Transfert/Historique/Deconnexion) : ok
    - ajout routes /client/depot, /client/retrait, /client/transfert, /client/historique (protegees par le filtre auth) : ok

