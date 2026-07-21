<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration du Système Mobile Money</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <script src="<?= base_url('assets/js/theme.js') ?>"></script>
</head>
<body>

<div class="mm-container mm-wide">
    <h1>Panneau de Configuration Mobile Money</h1>
    <a href="<?= base_url('operateur/autre') ?>" class="mm-btn-outline">Voir les autres opérateurs</a>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mm-flash-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mm-flash-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Vue d'ensemble : frais collectés -->
    <div class="mm-card" style="margin-top:24px;">
        <h3>📊 Vue d'ensemble : Frais Collectés par Type de Transaction</h3>
        <table class="mm-table">
            <thead>
                <tr>
                    <th>Type d'Opération</th>
                    <th>Volume (Nombre)</th>
                    <th>Total des Frais Engrangés</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resume_frais)): ?>
                    <?php $grand_total = 0; foreach ($resume_frais as $rf): $grand_total += $rf['total_frais']; ?>
                        <tr>
                            <td><strong style="text-transform:uppercase;"><?= esc($rf['operation_nom']) ?></strong></td>
                            <td><span class="mm-pill"><?= $rf['nombre_transactions'] ?></span></td>
                            <td><span class="mm-positive"><?= number_format($rf['total_frais'], 2, ',', ' ') ?> Ar</span></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight:bold; font-size:16px;">
                        <td colspan="2" style="text-align:right;">TOTAL GÉNÉRAL :</td>
                        <td><?= number_format($grand_total, 2, ',', ' ') ?> Ar</td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center; color:var(--mm-muted); font-style:italic;">Aucune transaction enregistrée, aucun frais collecté pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mm-row">
        <!-- FORMULAIRE 1 : PREFIXE -->
        <div class="mm-card">
            <h3>1. Ajouter un Préfixe</h3>
            <form action="<?= base_url('/operateur/addPrefixe') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mm-field">
                    <label for="prefixe_nom">Numéro du Préfixe :</label>
                    <input type="text" id="prefixe_nom" name="nom" placeholder="Ex: 034" maxlength="3" required>
                </div>
                <button type="submit" class="mm-btn" style="width:100%;">Enregistrer le préfixe</button>
            </form>
            <h4>Préfixes actuels :</h4>
            <ul>
                <?php foreach ($prefixes as $p): ?>
                    <li><strong><?= esc($p['nom']) ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- FORMULAIRE 2 : OPERATION -->
        <div class="mm-card">
            <h3>2. Ajouter une Opération</h3>
            <form action="<?= base_url('/operateur/addOperation') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mm-field">
                    <label for="op_nom">Nom de l'opération :</label>
                    <input type="text" id="op_nom" name="nom" placeholder="Ex: Retrait, Envoi, ..." required>
                </div>
                <button type="submit" class="mm-btn" style="width:100%;">Enregistrer l'opération</button>
            </form>
            <h4>Opérations actuelles :</h4>
            <ul>
                <?php foreach ($operations as $o): ?>
                    <li><strong><?= esc($o['nom']) ?></strong> (ID: <?= $o['id'] ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- FORMULAIRE 3 : BORNES ET FRAIS -->
    <div class="mm-card">
        <h3>3. Configurer les Bornes d'Intervalle et Frais</h3>
        <form action="<?= base_url('/operateur/addFrai') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mm-row" style="margin-bottom:0;">
                <div class="mm-field" style="flex:1;">
                    <label for="idtype_transaction">Sélectionner l'opération :</label>
                    <select id="idtype_transaction" name="idtype_transaction" required>
                        <option value="">-- Choisir une opération --</option>
                        <?php foreach ($operations as $o): ?>
                            <option value="<?= $o['id'] ?>"><?= esc($o['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mm-field" style="flex:1;">
                    <label for="Montant1">Montant Minimum (Borne 1) :</label>
                    <input type="number" step="0.01" id="Montant1" name="Montant1" placeholder="Ex: 100" required>
                </div>
                <div class="mm-field" style="flex:1;">
                    <label for="Montant2">Montant Maximum (Borne 2) :</label>
                    <input type="number" step="0.01" id="Montant2" name="Montant2" placeholder="Ex: 1000" required>
                </div>
                <div class="mm-field" style="flex:1;">
                    <label for="frai">Frais appliqués (Ariary) :</label>
                    <input type="number" step="0.01" id="frai" name="frai" placeholder="Ex: 50" required>
                </div>
            </div>
            <button type="submit" class="mm-btn" style="width:auto; padding:10px 30px; display:block; margin:10px auto 0;">Enregistrer la grille tarifaire</button>
        </form>

        <h3>Grille des tarifs enregistrée</h3>
        <table class="mm-table">
            <thead>
                <tr><th>Opération</th><th>Intervalle de Montant</th><th>Frais de l'opération</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($frais)): ?>
                    <?php foreach ($frais as $f): ?>
                        <tr>
                            <td><span style="text-transform:uppercase; font-weight:bold;"><?= esc($f['operation_nom']) ?></span></td>
                            <td>De <?= number_format($f['Montant1'], 2, ',', ' ') ?> Ar à <?= number_format($f['Montant2'], 2, ',', ' ') ?> Ar</td>
                            <td><span class="mm-negative"><?= number_format($f['frai'], 2, ',', ' ') ?> Ar</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">Aucun frais configuré pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- LISTE DES UTILISATEURS -->
    <div class="mm-card">
        <h3>👥 Liste des Utilisateurs et Soldes Actuels</h3>
        <table class="mm-table">
            <thead>
                <tr><th>ID</th><th>Nom de l'utilisateur</th><th>Numéro de Téléphone</th><th>Solde Virtuel</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($utilisateurs_soldes)): ?>
                    <?php foreach ($utilisateurs_soldes as $user): ?>
                        <tr>
                            <td style="color:var(--mm-muted);">#<?= $user['id'] ?></td>
                            <td style="font-weight:bold;"><?= esc($user['nom']) ?></td>
                            <td style="font-family:monospace;"><?= esc($user['telephone']) ?></td>
                            <td>
                                <!-- La couleur (vert/rouge) est decidee par theme.js a partir de data-balance -->
                                <span data-balance="<?= (float) $user['solde_actuel'] ?>" style="font-weight:bold;">
                                    <?= number_format($user['solde_actuel'], 2, ',', ' ') ?> Ar
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; color:var(--mm-muted); font-style:italic;">Aucun utilisateur trouvé dans le système.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
