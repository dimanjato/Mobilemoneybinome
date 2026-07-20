-- --sqlite3 mobilemoney.db
-- -- 1. Table : user
-- CREATE TABLE IF NOT EXISTS user (
--     id_user INTEGER PRIMARY KEY AUTOINCREMENT,
--     sufixe TEXT,
--     prefixe TEXT,
--     nom TEXT NOT NULL
-- );

-- -- 2. Table : type_transaction
-- CREATE TABLE IF NO
-- T EXISTS type_transaction (
--     id INTEGER PRIMARY KEY AUTOINCREMENT,
--     nom TEXT NOT NULL
-- );

-- -- 3. Table : Montant_frai
-- -- (Créée avant 'transaction' car 'transaction' y fait référence)
-- CREATE TABLE IF NOT EXISTS Montant_frai (
--     idMontantFrai INTEGER PRIMARY KEY AUTOINCREMENT,
--     Montant1 REAL NOT NULL,
--     Montant2 REAL NOT NULL,
--     frai REAL NOT NULL,
--     idtype_transaction INTEGER not null,
--     foreign key (idtype_transaction) references type_transaction(id)
-- );

-- -- 4. Table : solde_user
-- CREATE TABLE IF NOT EXISTS solde_user (
--     id_user INTEGER,
--     solde REAL NOT NULL DEFAULT 0.0,
--     date TEXT NOT NULL DEFAULT (DATETIME('now')),
--     PRIMARY KEY (id_user, date), -- Clé primaire composite pour suivre l'évolution du solde dans le temps
--     FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
-- );

-- -- 5. Table : transaction
-- CREATE TABLE IF NOT EXISTS transactions (
--     id_transaction INTEGER PRIMARY KEY AUTOINCREMENT,
--     id_type INTEGER,
--     montant REAL NOT NULL,
--     date TEXT NOT NULL DEFAULT (DATETIME('now')),
--     id1 INTEGER, -- Utilisateur émetteur (ou lié)
--     id2 INTEGER, -- Utilisateur récepteur (ou lié)
--     idMontant_frai INTEGER,
--     FOREIGN KEY (id_type) REFERENCES type_transaction(id),
--     FOREIGN KEY (id1) REFERENCES user(id_user),
--     FOREIGN KEY (id2) REFERENCES user(id_user),
--     FOREIGN KEY (idMontant_frai) REFERENCES Montant_frai(idMontantFrai)
-- );

-- CREATE  TABLE IF NOT EXISTS prefixe (
--     id_prefixe INTEGER PRIMARY KEY AUTOINCREMENT,
--     nom TEXT NOT NULL
-- )

-- -- 1. Insertions dans la table type_transaction
-- INSERT INTO type_transaction (nom) VALUES ('retrait');
-- INSERT INTO type_transaction (nom) VALUES ('depot');
-- INSERT INTO type_transaction (nom) VALUES ('transfert');

-- -- 2. Insertions dans la table Montant_frai (basées sur l'image fournie)
-- -- Colonnes : Montant1 (Borne minimale), Montant2 (Borne maximale), frai (Montant du frais)
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (100, 1000, 50);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (1001, 5000, 50);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (5001, 10000, 100);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (10001, 25000, 200);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (25001, 50000, 400);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (50001, 100000, 800);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (100001, 250000, 1500);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (25001, 500000, 1500);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (500001, 1000000, 2500);
-- INSERT INTO Montant_frai (Montant1, Montant2, frai) VALUES (1000001, 2000000, 3000);
-- 1. Table : user
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

-- 3. Table : Montant_frai
CREATE TABLE IF NOT EXISTS Montant_frai (
    idMontantFrai INTEGER PRIMARY KEY AUTOINCREMENT,
    Montant1 REAL NOT NULL,
    Montant2 REAL NOT NULL,
    frai REAL NOT NULL,
    idtype_transaction INTEGER NOT NULL,
    FOREIGN KEY (idtype_transaction) REFERENCES type_transaction(id)
);

-- 4. Table : solde_user
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
    id1 INTEGER,
    id2 INTEGER,
    idMontant_frai INTEGER,
    FOREIGN KEY (id_type) REFERENCES type_transaction(id),
    FOREIGN KEY (id1) REFERENCES user(id_user),
    FOREIGN KEY (id2) REFERENCES user(id_user),
    FOREIGN KEY (idMontant_frai) REFERENCES Montant_frai(idMontantFrai)
);

-- 6. Table : prefixe
CREATE TABLE IF NOT EXISTS prefixe (
    id_prefixe INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL
);

-- =========================================================
-- INSERTIONS DE DONNÉES
-- =========================================================

-- 1. Types de transaction (Génère id: 1 pour retrait, 2 pour depot, 3 pour transfert)
INSERT INTO type_transaction (nom) VALUES ('retrait');
INSERT INTO type_transaction (nom) VALUES ('depot');
INSERT INTO type_transaction (nom) VALUES ('transfert');

-- 2. Tarifs de frais (Associés par défaut au retrait / idtype_transaction = 1)
-- Modifie le dernier paramètre '1' si ces frais correspondent aux transferts (3) ou dépôts (2)
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (100, 1000, 50, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (1001, 5000, 50, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (5001, 10000, 100, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (10001, 25000, 200, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (25001, 50000, 400, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (50001, 100000, 800, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (100001, 250000, 1500, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (25001, 500000, 1500, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (500001, 1000000, 2500, 1);
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES (1000001, 2000000, 3000, 1);