<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration du Système Mobile Money</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .row { display: flex; gap: 20px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; }
        .card-full { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; }
        h2, h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 9px 15px; background-color: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 5px; }
        button:hover { background-color: #219653; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h1>Panneau de Configuration Mobile Money</h1>
    <!-- lien vers autre_operateur.php -->
    <a>
    </a>
    <!--gain operateur  -->
    <div class="card-full" style="margin-top: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px;">
            📊 Vue d'ensemble : Frais Collectés par Type de Transaction
        </h3>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="background-color: #34495e; color: white;">
                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Type d'Opération</th>
                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Volume (Nombre)</th>
                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Total des Frais Engrangés</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resume_frais)): ?>
                    <?php 
                    $grand_total = 0; 
                    foreach ($resume_frais as $rf): 
                        $grand_total += $rf['total_frais'];
                    ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <strong style="text-transform: uppercase; color: #2c3e50;"><?= esc($rf['operation_nom']) ?></strong>
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <span style="background-color: #7f8c8d; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                    <?= $rf['nombre_transactions'] ?>
                                </span>
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <span style="font-weight: bold; color: #27ae60;">
                                    <?= number_format($rf['total_frais'], 2, ',', ' ') ?> Ar
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Ligne de total général -->
                    <tr style="background-color: #f9f9f9; font-weight: bold; font-size: 16px;">
                        <td colspan="2" style="padding: 12px; text-align: right; border: 1px solid #ddd; color: #2c3e50;">
                            TOTAL GÉNÉRAL :
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; color: #2980b9;">
                            <?= number_format($grand_total, 2, ',', ' ') ?> Ar
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="padding: 15px; text-align: center; color: #7f8c8d; font-style: italic;">
                            Aucune transaction enregistrée, aucun frais collecté pour le moment.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Flashdata Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- FORMULAIRE 1 : PREFIXE -->
        <div class="card">
            <h3>1. Ajouter un Préfixe</h3>
            <!-- CORRECTION URL : /operateur/addPrefixe -->
            <form action="<?= base_url('/operateur/addPrefixe') ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="prefixe_nom">Numéro du Préfixe :</label>
                    <input type="text" id="prefixe_nom" name="nom" placeholder="Ex: 034" maxlength="3" required>
                </div>
                <button type="submit">Enregistrer le préfixe</button>
            </form>

            <h4>Préfixes actuels :</h4>
            <ul>
                <?php foreach($prefixes as $p): ?>
                    <li><strong><?= esc($p['nom']) ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- FORMULAIRE 2 : OPERATION (TYPE TRANSACTION) -->
        <div class="card">
            <h3>2. Ajouter une Opération</h3>
            <!-- CORRECTION URL : /operateur/addOperation -->
            <form action="<?= base_url('/operateur/addOperation') ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="op_nom">Nom de l'opération :</label>
                    <input type="text" id="op_nom" name="nom" placeholder="Ex: Retrait, Envoi, ..." required>
                </div>
                <button type="submit">Enregistrer l'opération</button>
            </form>

            <h4>Opérations actuelles :</h4>
            <ul>
                <?php foreach($operations as $o): ?>
                    <li><strong><?= esc($o['nom']) ?></strong> (ID: <?= $o['id'] ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- FORMULAIRE 3 : BORNES ET FRAIS DE TRANSFERT -->
    <div class="card-full">
        <h3>3. Configurer les Bornes d'Intervalle et Frais</h3>
        <!-- CORRECTION URL : /operateur/addFrai -->
        <form action="<?= base_url('/operateur/addFrai') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row" style="margin-bottom: 0;">
                <div class="form-group" style="flex: 1;">
                    <label for="idtype_transaction">Sélectionner l'opération :</label>
                    <select id="idtype_transaction" name="idtype_transaction" required>
                        <option value="">-- Choisir une opération --</option>
                        <?php foreach($operations as $o): ?>
                            <option value="<?= $o['id'] ?>"><?= esc($o['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label for="Montant1">Montant Minimum (Borne 1) :</label>
                    <input type="number" step="0.01" id="Montant1" name="Montant1" placeholder="Ex: 100" required>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="Montant2">Montant Maximum (Borne 2) :</label>
                    <input type="number" step="0.01" id="Montant2" name="Montant2" placeholder="Ex: 1000" required>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="frai">Frais appliqués (Ariary) :</label>
                    <input type="number" step="0.01" id="frai" name="frai" placeholder="Ex: 50" required>
                </div>
            </div>
            <button type="submit" style="width: auto; padding: 10px 30px; display: block; margin: 10px auto 0;">Enregistrer la grille tarifaire</button>
        </form>

        <h3>Grille des tarifs enregistrée</h3>
        <table>
            <thead>
                <tr>
                    <th>Opération</th>
                    <th>Intervalle de Montant</th>
                    <th>Frais de l'opération</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($frais)): ?>
                    <?php foreach($frais as $f): ?>
                        <tr>
                            <td><span style="text-transform: uppercase; font-weight: bold; color: #2c3e50;"><?= esc($f['operation_nom']) ?></span></td>
                            <td>De <?= number_format($f['Montant1'], 2, ',', ' ') ?> Ar à <?= number_format($f['Montant2'], 2, ',', ' ') ?> Ar</td>
                            <td><span style="color: #c0392b; font-weight: bold;"><?= number_format($f['frai'], 2, ',', ' ') ?> Ar</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Aucun frais configuré pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="card-full" style="margin-top: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px;">
        👥 Liste des Utilisateurs et Soldes Actuels
    </h3>
    
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr style="background-color: #2c3e50; color: white;">
                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">ID</th>
                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Nom de l'utilisateur</th>
                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Numéro de Téléphone</th>
                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Solde Virtuel</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($utilisateurs_soldes)): ?>
                <?php foreach ($utilisateurs_soldes as $user): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 10px; border: 1px solid #ddd; color: #7f8c8d;">
                            #<?= $user['id'] ?>
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">
                            <?= esc($user['nom']) ?>
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd; font-family: monospace;">
                            <?= esc($user['telephone']) ?>
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <!-- Changement de couleur dynamique si le solde est négatif (découvert) ou positif -->
                            <span style="font-weight: bold; color: <?= $user['solde_actuel'] >= 0 ? '#27ae60' : '#c0392b' ?>;">
                                <?= number_format($user['solde_actuel'], 2, ',', ' ') ?> Ar
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="padding: 15px; text-align: center; color: #7f8c8d; font-style: italic;">
                        Aucun utilisateur trouvé dans le système.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>