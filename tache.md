# creation de base :
## creation de table :
    - user (id_user, sufixe, prefixe, nom)
    - solde_user (id_user, solde, date)
    type_trasaction(id,nom)
    - transaction (id_transaction, id_type, montant, date, id1,id2, idMontant_frai)
    - Montant_frai (idMontantFrai, Montant1, Montant2, frai)
    
## Connexion base ETU003896:
## mise en place de donnee test :
## Connexion de clients ETU003896:
    - validation : ok
    - recherche de compte si existe :ok
    - mampiditra session client :ok

### Voir solde ETU004067 : 
    - creer AuthFIlters : ok
    - creer controller SoldController : ok
    - creer le view qui va calculer le soldes actuelle : ok

### Correction bug connexion (numero ne se connectait pas) ETU004067 :
    - ajout de la vue SQL view_calcul_releve dans base.sql (manquante, utilisee par SoldeModel) : ok
    - correction du typo de tranche de frais 25001->250000 en 250001->500000 dans base.sql : ok
    - UserModel::getOrCreateUserByPhoneNumber() : creation automatique du compte si le numero n'existe pas (conforme au sujet : pas d'inscription au prealable) : ok
    - correction UserController : redirection vers /login (au lieu de /Connexion qui n'existait pas) : ok
    - correction SoldController/solde_view : SoldeModel->toArray() (objet utilise comme tableau) : ok

### Faire un depot ETU004067 :
    - creer TransactionModel::enregistrerDepot() : ok
    - creer TransactionController::depot() (formulaire + traitement) : ok
    - creer la vue depot_view.php : ok

### Faire un retrait ETU004067 :
    - creer MontantModel::getTranche()/getFrais() (bareme de frais par tranche) : ok
    - creer TransactionModel::enregistrerRetrait() : ok
    - creer TransactionController::retrait() avec verification du solde suffisant : ok
    - creer la vue retrait_view.php : ok

### Faire un transfert ETU004067 :
    - creer TransactionModel::enregistrerTransfert() : ok
    - creer TransactionController::transfert() (creation auto du destinataire si besoin, verification solde) : ok
    - creer la vue transfert_view.php : ok

### Voir les historiques ETU004067 :
    - creer TransactionModel::historique() : ok
    - creer TransactionController::historique() : ok
    - creer la vue historique_view.php : ok

### Navigation / UI ETU004067 :
    - creer partials/nav.php (menu commun Solde/Depot/Retrait/Transfert/Historique/Deconnexion) : ok
    - ajout routes /client/depot, /client/retrait, /client/transfert, /client/historique (protegees par le filtre auth) : ok


## opperateur ETU003896 :
    -voir la list des user avec sold
    -formualire
        -prefixe
        -operation
        -borne
        - gain de l'operateur

## OPtion inclure fraie de retrait ETU004067:
    view : 
        - ajout de checkbox et le js correspondant : ok
    Controller :
        - modification du fonction transfert : ok
    
## envoie multiple ETU004067:
    view :
        - ajout de bouton d ajout de champ avec js => envoie donnee tableau : ok 
    controller : 
        - rendre en boucle dans fonction transfert : ok
    
