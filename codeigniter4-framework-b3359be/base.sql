-- =========================================================
-- Mobile Money - Script de creation de la base (SQLite3)
-- =========================================================
-- table operateur
CREATE TABLE IF NOT EXISTS operateur (
    id_operateur INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    prefixe TEXT NOT NULL,
    commition REAL DEFAULT 0.0
);

-- 1. Table : user (comptes clients, creation automatique a la 1ere connexion)
CREATE TABLE IF NOT EXISTS user (
    id_user INTEGER PRIMARY KEY AUTOINCREMENT,
    sufixe TEXT,
    prefixe TEXT,
    nom TEXT NOT NULL
);

-- 2. Table : type_transaction
CREATE TABLE IF NOT EXISTS type_transaction (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL
);

-- 3. Table : Montant_frai (bareme de frais par tranche de montant, par type d'operation)
CREATE TABLE IF NOT EXISTS Montant_frai (
    idMontantFrai INTEGER PRIMARY KEY AUTOINCREMENT,
    Montant1 REAL NOT NULL,
    Montant2 REAL NOT NULL,
    frai REAL NOT NULL,
    idtype_transaction INTEGER NOT NULL,
    FOREIGN KEY (idtype_transaction) REFERENCES type_transaction(id)
);

-- 4. Table : solde_user (historique de solde, non utilisee pour le calcul temps reel,
--    le solde est recalcule depuis les transactions via la vue view_calcul_releve)
CREATE TABLE IF NOT EXISTS solde_user (
    id_user INTEGER,
    solde REAL NOT NULL DEFAULT 0.0,
    date TEXT NOT NULL DEFAULT (DATETIME('now')),
    PRIMARY KEY (id_user, date),
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
);

-- 5. Table : transactions
CREATE TABLE IF NOT EXISTS transactions (
    id_transaction INTEGER PRIMARY KEY AUTOINCREMENT,
    id_type INTEGER,
    montant REAL NOT NULL,
    date TEXT NOT NULL DEFAULT (DATETIME('now')),
    id1 INTEGER, -- utilisateur emetteur / qui effectue l'operation
    id2 INTEGER, -- utilisateur recepteur (transfert uniquement)
    idMontant_frai INTEGER,
    FOREIGN KEY (id_type) REFERENCES type_transaction(id),
    FOREIGN KEY (id1) REFERENCES user(id_user),
    FOREIGN KEY (id2) REFERENCES user(id_user),
    FOREIGN KEY (idMontant_frai) REFERENCES Montant_frai(idMontantFrai)
);

-- 6. Table : prefixe (prefixes valables cote operateur, ex: 033, 037)
CREATE TABLE IF NOT EXISTS prefixe (
    id_prefixe INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    id_operateur INTEGER NOT NULL,
    FOREIGN KEY (id_operateur) REFERENCES operateur(id_operateur)
);




-- =========================================================
-- INSERTIONS DE DONNEES
-- =========================================================

-- 1. Types de transaction : 1=retrait, 2=depot, 3=transfert
INSERT INTO type_transaction (nom) VALUES ('retrait');
INSERT INTO type_transaction (nom) VALUES ('depot');
INSERT INTO type_transaction (nom) VALUES ('transfert');

-- 2. Insertion des opérateurs (Important pour avoir un id_operateur valide)
-- Remarque : j'ai gardé ton orthographe "commition" pour que ça colle à ta table !
INSERT INTO operateur (id_operateur, nom, prefixe, commition) VALUES (1, 'MonOperateur', '033', 0.0);
INSERT INTO operateur (id_operateur, nom, prefixe, commition) VALUES (2, 'AutreOperateur', '032', 2.5);

-- 3. Préfixes valables (En fournissant obligatoirement l'id_operateur)
INSERT INTO prefixe (nom, id_operateur) VALUES ('033', 1); -- Lié à MonOperateur
INSERT INTO prefixe (nom, id_operateur) VALUES ('037', 1); -- Lié à MonOperateur
INSERT INTO prefixe (nom, id_operateur) VALUES ('032', 2); -- Lié à AutreOperateur

-- 4. Bareme de frais pour le RETRAIT (idtype_transaction = 1)
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (100, 1000, 50, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (1001, 5000, 50, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (5001, 10000, 100, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (10001, 25000, 200, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (25001, 50000, 400, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (50001, 100000, 800, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (100001, 250000, 1500, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (250001, 500000, 1500, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (500001, 1000000, 2500, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (1000001, 2000000, 3000, 1);

-- 5. Bareme de frais pour le TRANSFERT (idtype_transaction = 3)
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (100, 1000, 50, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (1001, 5000, 50, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (5001, 10000, 100, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (10001, 25000, 200, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (25001, 50000, 400, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (50001, 100000, 800, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (100001, 250000, 1500, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (250001, 500000, 1500, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (500001, 1000000, 2500, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (1000001, 2000000, 3000, 3);