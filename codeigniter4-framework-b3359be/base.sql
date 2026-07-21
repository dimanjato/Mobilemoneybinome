sqlite3 mobilemoney.db

-- =========================================================
-- Mobile Money - Script de creation de la base (SQLite3)
-- =========================================================

-- 1. Table : user (comptes clients, creation automatique a la 1ere connexion)
CREATE TABLE IF NOT EXISTS operateur (
    id_operateur INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    prefixe TEXT NOT NULL,
    commition REAL DEFAULT 0.0
);

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
-- DONNÉES DE TEST : Mobile Money (SQLite3)
-- =========================================================

-- Désactiver temporairement les clés étrangères pour éviter les conflits d'insertion si nécessaire
PRAGMA foreign_keys = OFF;

-- Nettoyage préalable des tables (Tronquer/Vider sans supprimer les tables)
DELETE FROM transactions;
DELETE FROM solde_user;
DELETE FROM Montant_frai;
DELETE FROM prefixe;
DELETE FROM operateur;
DELETE FROM type_transaction;
DELETE FROM user;

-- Réinitialisation des compteurs d'auto-incrément
DELETE FROM sqlite_sequence WHERE name IN ('user', 'type_transaction', 'Montant_frai', 'transactions', 'prefixe', 'operateur');

PRAGMA foreign_keys = ON;

-- ---------------------------------------------------------
-- 1. Insertion des Types de Transaction
-- ---------------------------------------------------------
-- (Important : id=1 pour Retrait, id=2 pour Dépôt, id=3 pour Transfert selon ta vue)
INSERT INTO type_transaction (id, nom) VALUES (1, 'Retrait');
INSERT INTO type_transaction (id, nom) VALUES (2, 'Depot');
INSERT INTO type_transaction (id, nom) VALUES (3, 'Transfert');

-- ---------------------------------------------------------
-- 2. Insertion des Opérateurs
-- ---------------------------------------------------------
INSERT INTO operateur (id_operateur, nom, prefixe) VALUES (1, 'Telma', '034');
INSERT INTO operateur (id_operateur, nom, prefixe) VALUES (2, 'Orange', '032');
INSERT INTO operateur (id_operateur, nom, prefixe) VALUES (3, 'Airtel', '033');

-- ---------------------------------------------------------
-- 3. Insertion des Préfixes de validation
-- ---------------------------------------------------------
INSERT INTO prefixe (id_prefixe, nom, id_operateur) VALUES (1, '034', 1);
INSERT INTO prefixe (id_prefixe, nom, id_operateur) VALUES (2, '032', 2);
INSERT INTO prefixe (id_prefixe, nom, id_operateur) VALUES (3, '033', 3);
INSERT INTO prefixe (id_prefixe, nom, id_operateur) VALUES (4, '038', 1); -- Préfixe d'un sous-réseau ou test

-- ---------------------------------------------------------
-- 4. Insertion du Barème des Frais (Montant_frai)
-- ---------------------------------------------------------
-- Pour les Retraits (idtype_transaction = 1)
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (0.0, 5000.0, 200.0, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (5001.0, 20000.0, 500.0, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (20001.0, 100000.0, 1500.0, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (100001.0, 500000.0, 3000.0, 1);

-- Pour les Transferts (idtype_transaction = 3)
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (0.0, 10000.0, 100.0, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (10001.0, 50000.0, 300.0, 3);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (50001.0, 500000.0, 1000.0, 3);

-- ---------------------------------------------------------
-- 5. Insertion des Utilisateurs (Comptes de test)
-- ---------------------------------------------------------
-- Note : les numéros complets sont formés par ton code via préfixe + suffixe
INSERT INTO user (id_user, prefixe, sufixe, nom) VALUES (1, '034', '1234567', 'Finaritra');
INSERT INTO user (id_user, prefixe, sufixe, nom) VALUES (2, '032', '7654321', 'Rindra');
INSERT INTO user (id_user, prefixe, sufixe, nom) VALUES (3, '033', '4567890', 'Rakoto');

-- ---------------------------------------------------------
-- 6. Insertion des Transactions (Historique de test)
-- ---------------------------------------------------------

-- Transaction A : Finaritra (id=1) fait un DÉPÔT de 50 000 Ariary (Pas de frais sur les dépôts)
INSERT INTO transactions (id_transaction, id_type, montant, date, id1, id2, idMontant_frai) 
VALUES (1, 2, 50000.0, '2026-07-20 09:00:00', 1, NULL, NULL);

-- Transaction B : Rindra (id=2) fait un DÉPÔT de 100 000 Ariary
INSERT INTO transactions (id_transaction, id_type, montant, date, id1, id2, idMontant_frai) 
VALUES (2, 2, 100000.0, '2026-07-20 09:15:00', 2, NULL, NULL);

-- Transaction C : Finaritra (id=1) TRANSFERT 20 000 Ariary à Rindra (id=2)
-- Frais associés : tranche 10001 à 50000 pour transfert -> idMontant_frai = 6 (300 Ar de frais)
INSERT INTO transactions (id_transaction, id_type, montant, date, id1, id2, idMontant_frai) 
VALUES (3, 3, 20000.0, '2026-07-20 10:30:00', 1, 2, 6);

-- Transaction D : Rindra (id=2) fait un RETRAIT de 15 000 Ariary en agence
-- Frais associés : tranche 5001 à 20000 pour retrait -> idMontant_frai = 2 (500 Ar de frais)
INSERT INTO transactions (id_transaction, id_type, montant, date, id1, id2, idMontant_frai) 
VALUES (4, 1, 15000.0, '2026-07-20 11:00:00', 2, NULL, 2);

-- ---------------------------------------------------------
-- 7. Table Solde Historique (Optionnel, au cas où ton modèle l'exige)
-- ---------------------------------------------------------
INSERT INTO solde_user (id_user, solde, date) VALUES (1, 0.0, '2026-07-20 08:00:00');
INSERT INTO solde_user (id_user, solde, date) VALUES (2, 0.0, '2026-07-20 08:00:00');

INSERT INTO user (prefixe, sufixe, nom) VALUES ('033', '12345678', 'Rakoto Jean');



