sqlite3 mobilemoney.db
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
-- (Créée avant 'transaction' car 'transaction' y fait référence)
CREATE TABLE IF NOT EXISTS Montant_frai (
    idMontantFrai INTEGER PRIMARY KEY AUTOINCREMENT,
    Montant1 REAL NOT NULL,
    Montant2 REAL NOT NULL,
    frai REAL NOT NULL,
    idtype_transaction INTEGER NOT NULL
);

-- 5. Table : transaction
CREATE TABLE IF NOT EXISTS transactions (
    id_transaction INTEGER PRIMARY KEY AUTOINCREMENT,
    id_type INTEGER,
    montant REAL NOT NULL,
    date TEXT NOT NULL DEFAULT (DATETIME('now')),
    id1 INTEGER, -- Utilisateur émetteur (ou lié)
    id2 INTEGER, -- Utilisateur récepteur (ou lié)
    idMontant_frai INTEGER,
    FOREIGN KEY (id_type) REFERENCES type_transaction(id),
    FOREIGN KEY (id1) REFERENCES user(id_user),
    FOREIGN KEY (id2) REFERENCES user(id_user),
    FOREIGN KEY (idMontant_frai) REFERENCES Montant_frai(idMontantFrai)
);

CREATE  TABLE IF NOT EXISTS prefixe (
    id_prefixe INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL
);

-- 1. Insertions dans la table type_transaction
INSERT INTO type_transaction (nom) VALUES ('retrait');
INSERT INTO type_transaction (nom) VALUES ('depot');
INSERT INTO type_transaction (nom) VALUES ('transfert');

-- 1. Insertions pour les FRAIS DE RETRAIT (idtype_transaction = 1)
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES 
(100, 1000, 50, 1),
(1001, 5000, 50, 1),
(5001, 10000, 100, 1),
(10001, 25000, 200, 1),
(25001, 50000, 400, 1),
(50001, 100000, 800, 1),
(100001, 250000, 1500, 1),
(250001, 500000, 1500, 1),
(500001, 1000000, 2500, 1),
(1000001, 2000000, 3000, 1);

-- 2. Insertions pour les FRAIS DE TRANSFERT (idtype_transaction = 3)
INSERT INTO Montant_frai (Montant1, Montant2, frai, idtype_transaction) VALUES 
(100, 1000, 50, 3),
(1001, 5000, 50, 3),
(5001, 10000, 100, 3),
(10001, 25000, 200, 3),
(25001, 50000, 400, 3),
(50001, 100000, 800, 3),
(100001, 250000, 1500, 3),
(250001, 500000, 1500, 3),
(500001, 1000000, 2500, 3),
(1000001, 2000000, 3000, 3);

/*view qui va cacluler solde present dans transaction*/
