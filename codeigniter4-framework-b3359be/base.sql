-- =========================================================
-- Mobile Money - Script de creation de la base (SQLite3)
-- =========================================================

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
    nom TEXT NOT NULL
);

-- =========================================================
-- VUE : view_calcul_releve
-- Calcule l'impact de chaque transaction sur le solde de chaque
-- utilisateur concerne (utilisee par SoldeModel::getSoldeAu)
--   - depot   (id_type=2) : +montant                 pour id1
--   - retrait (id_type=1) : -(montant + frai)         pour id1
--   - transfert (id_type=3) : -(montant + frai) pour id1, +montant pour id2
-- =========================================================
DROP VIEW IF EXISTS view_calcul_releve;
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

-- =========================================================
-- INSERTIONS DE DONNEES
-- =========================================================

-- Types de transaction : 1=retrait, 2=depot, 3=transfert
INSERT INTO type_transaction (nom) VALUES ('retrait');
INSERT INTO type_transaction (nom) VALUES ('depot');
INSERT INTO type_transaction (nom) VALUES ('transfert');

-- Prefixes valables de l'operateur
INSERT INTO prefixe (nom) VALUES ('033');
INSERT INTO prefixe (nom) VALUES ('037');

-- Bareme de frais pour le RETRAIT (idtype_transaction = 1)
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

-- Bareme de frais pour le TRANSFERT (idtype_transaction = 3)
-- (meme bareme que le retrait, a ajuster cote operateur si besoin)
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
