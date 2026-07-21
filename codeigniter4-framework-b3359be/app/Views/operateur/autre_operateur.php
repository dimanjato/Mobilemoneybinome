<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Autres Opérateurs</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <script src="<?= base_url('assets/js/theme.js') ?>"></script>
</head>
<body>

<div class="mm-container mm-wide">

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="mm-flash-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="mm-flash-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <h1>Gestion des Opérateurs Tiers</h1>
        <div class="mm-card" style="background:var(--mm-success); color:#fff; padding:14px 20px; margin-bottom:0;">
            <span style="display:block; text-transform:uppercase; font-size:12px; font-weight:700;">Somme globale des gains</span>
            <strong style="font-size:22px;"><?= number_format($totalGains, 2, ',', ' ') ?> Ar</strong>
        </div>
    </div>

    <div class="mm-row" style="margin-top:20px;">
        <!-- 1. LISTE DES AUTRES OPERATEURS AVEC LEUR COMMISSION -->
        <div class="mm-card">
            <h3>Liste des autres opérateurs & commissions</h3>
            <table class="mm-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Préfixe(s)</th>
                        <th>Commission</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($autresOperateurs)): ?>
                        <?php foreach ($autresOperateurs as $op): ?>
                            <tr>
                                <td style="font-weight:bold;"><?= esc($op['nom']) ?></td>
                                <td>
                                    <?php foreach (explode(',', (string) $op['prefixes']) as $pref): ?>
                                        <span class="mm-pill"><?= esc(trim($pref)) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="mm-positive"><?= esc($op['commission']) ?> %</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center; color:var(--mm-muted); font-style:italic;">Aucun autre opérateur enregistré.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 2. FORMULAIRE NOUVEL OPERATEUR -->
        <div class="mm-card">
            <h3>Ajouter un nouvel opérateur</h3>
            <form action="<?= base_url('operateur/add-tiers') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mm-field">
                    <label for="nom">Nom de l'opérateur</label>
                    <input type="text" id="nom" name="nom" placeholder="Ex: Airtel, Orange" required>
                </div>
                <div class="mm-field">
                    <label for="prefixe">Préfixe(s)</label>
                    <input type="text" id="prefixe" name="prefixe" placeholder="Ex: 033,037 (séparés par une virgule)" required>
                    <p class="mm-hint">S'il y en a plusieurs, sépare-les par des virgules sans espaces.</p>
                </div>
                <div class="mm-field">
                    <label for="prct">Pourcentage commission (%)</label>
                    <input type="number" step="0.01" id="prct" name="prct" placeholder="Ex: 2.5" required>
                </div>
                <button type="submit" class="mm-btn" style="width:100%;">Enregistrer l'opérateur</button>
            </form>
        </div>
    </div>

    <!-- 3. HISTORIQUE DES ENVOIS AVEC FILTRES -->
    <div class="mm-card">
        <h3>Historique des envois vers les opérateurs tiers</h3>

        <form action="<?= base_url('operateur/autre') ?>" method="GET" class="mm-row" style="align-items:flex-end;">
            <div class="mm-field" style="flex:1; min-width:160px;">
                <label for="filter_op">Filtrer par opérateur</label>
                <select name="operateur" id="filter_op">
                    <option value="">Tous les opérateurs</option>
                    <?php foreach ($listeOperateursFiltrable as $lop): ?>
                        <option value="<?= $lop['id_operateur'] ?>" <?= ($filtres['operateur'] == $lop['id_operateur']) ? 'selected' : '' ?>>
                            <?= esc($lop['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mm-field" style="flex:1; min-width:160px;">
                <label for="filter_pref">Filtrer par préfixe</label>
                <select name="prefixe" id="filter_pref">
                    <option value="">Tous les préfixes</option>
                    <?php foreach ($listePrefixesFiltrable as $lpref): ?>
                        <option value="<?= $lpref['nom'] ?>" <?= ($filtres['prefixe'] == $lpref['nom']) ? 'selected' : '' ?>>
                            <?= esc($lpref['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mm-field" style="flex:1; min-width:160px;">
                <label for="filter_date">Filtrer par date</label>
                <input type="date" name="date" id="filter_date" value="<?= esc($filtres['date'] ?? '') ?>">
            </div>
            <div class="mm-field" style="display:flex; gap:10px;">
                <button type="submit" class="mm-btn">Filtrer</button>
                <a href="<?= base_url('operateur/autre') ?>" class="mm-btn-outline">Réinitialiser</a>
            </div>
        </form>

        <table class="mm-table">
            <thead>
                <tr>
                    <th>Opérateur</th>
                    <th>Numéro destinataire</th>
                    <th>Montant envoyé</th>
                    <th>Commission</th>
                    <th>Gain généré</th>
                    <th>Frais de transfert</th>
                    <th>Date & heure</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($historique)): ?>
                    <?php foreach ($historique as $hist): ?>
                        <tr>
                            <td style="font-weight:bold;"><?= esc($hist['operateur_nom']) ?></td>
                            <td style="font-family:monospace;"><?= esc($hist['numero_destinataire']) ?></td>
                            <td style="font-weight:bold;"><?= number_format($hist['montant_brut'], 2, ',', ' ') ?> Ar</td>
                            <td style="color:var(--mm-muted);"><?= esc($hist['pourcentage']) ?> %</td>
                            <td class="mm-positive">+ <?= number_format($hist['commission_gain'], 2, ',', ' ') ?> Ar</td>
                            <td class="mm-negative"><?= number_format($hist['frais_transfert'], 2, ',', ' ') ?> Ar</td>
                            <td style="color:var(--mm-muted); font-size:13px;"><?= date('d/m/Y H:i', strtotime($hist['date_transaction'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; color:var(--mm-muted); font-style:italic;">Aucun historique de transfert ne correspond à ces critères.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
