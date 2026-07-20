<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Autres Opérateurs</title>
    <!-- Utilisation de Bootstrap 5 pour un rendu propre et rapide pour les examens -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

    <div class="container bg-white p-4 rounded shadow-sm">
        
        <!-- Section Messages Flash (Succès / Erreurs) -->
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <h1 class="h2 text-primary">Gestion des Opérateurs Tiers</h1>
            <!-- Indicateur de la Somme des Gains Globaux -->
            <div class="bg-success text-white p-3 rounded text-end">
                <span class="d-block text-uppercase small font-weight-bold">Somme globale des gains</span>
                <strong class="fs-4"><?= number_format($totalGains, 2, ',', ' ') ?> Ar</strong>
            </div>
        </div>

        <div class="row g-4">
            <!-- 1. LISTE DES AUTRES OPERATEURS AVEC LEUR POURCENTAGE -->
            <div class="col-md-7">
                <div class="card h-100">
                    <div class="card-header bg-dark text-white font-weight-bold">
                        Liste des autres opérateurs & commissions
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Préfixe Principal</th>
                                        <th class="text-center">Commission (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($autresOperateurs)): ?>
                                        <?php foreach ($autresOperateurs as $op): ?>
                                            <tr>
                                                <td class="fw-bold"><?= esc($op['nom']) ?></td>
                                                <td><span class="badge bg-secondary fs-6"><?= esc($op['prefixe']) ?></span></td>
                                                <td class="text-center fw-bold text-success"><?= esc($op['commition']) ?> %</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted p-3">Aucun autre opérateur enregistré.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. FORMULAIRE NOUVEAU OPERATEUR -->
            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white font-weight-bold">
                        Ajouter un nouvel opérateur
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('operateur/add-tiers') ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="nom" class="form-label font-weight-bold">Nom de l'opérateur</label>
                                <input type="text" class="form-control" id="nom" name="nom" placeholder="Ex: Airtel, Orange" required>
                            </div>

                            <div class="mb-3">
                                <label for="prefixe" class="form-label font-weight-bold">Préfixe(s)</label>
                                <input type="text" class="form-control" id="prefixe" name="prefixe" placeholder="Ex: 033, 037 (séparés par une virgule)" required>
                                <div class="form-text text-muted">S'il y en a plusieurs, sépare-les par des virgules sans espaces.</div>
                            </div>

                            <div class="mb-3">
                                <label for="prct" class="form-label font-weight-bold">Pourcentage commission (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="prct" name="prct" placeholder="Ex: 2.5" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-2">Enregistrer l'opérateur</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. LISTE DES HISTORIQUES ENVOYÉS À CHAQUE OPÉRATEUR AVEC FILTRES -->
        <div class="card mt-5 shadow-sm">
            <div class="card-header bg-secondary text-white font-weight-bold">
                Historique des envois vers les opérateurs tiers
            </div>
            <div class="card-body">
                
                <!-- Zone des formulaires de filtres (Filtre opérateur, préfixe et date) -->
                <form action="<?= base_url('operateur/autre') ?>" method="GET" class="row g-3 mb-4 align-items-end">
                    <div class="col-md-3">
                        <label for="filter_op" class="form-label small font-weight-bold text-muted">Filtrer par Opérateur</label>
                        <select name="operateur" id="filter_op" class="form-select">
                            <option value="">Tous les opérateurs</option>
                            <?php foreach ($listeOperateursFiltrable as $lop): ?>
                                <option value="<?= $lop['id_operateur'] ?>" <?= ($filtres['operateur'] == $lop['id_operateur']) ? 'selected' : '' ?>>
                                    <?= esc($lop['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_pref" class="form-label small font-weight-bold text-muted">Filtrer par Préfixe</label>
                        <select name="prefixe" id="filter_pref" class="form-select">
                            <option value="">Tous les préfixes</option>
                            <?php foreach ($listePrefixesFiltrable as $lpref): ?>
                                <option value="<?= $lpref['nom'] ?>" <?= ($filtres['prefixe'] == $lpref['nom']) ? 'selected' : '' ?>>
                                    <?= esc($lpref['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_date" class="form-label small font-weight-bold text-muted">Filtrer par Date</label>
                        <input type="date" name="date" id="filter_date" class="form-control" value="<?= esc($filtres['date'] ?? '') ?>">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-dark flex-grow-1">Filtrer</button>
                        <a href="<?= base_url('operateur/autre') ?>" class="btn btn-outline-secondary">Réinitialiser</a>
                    </div>
                </form>

                <!-- Tableau historique de transactions -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Opérateur</th>
                                <th>Numéro Destinataire</th>
                                <th class="text-end">Montant Envoyé</th>
                                <th class="text-center">Pourcentage commission</th>
                                <th class="text-end">Gain généré</th>
                                <th class="text-end">Frais de transfert</th>
                                <th class="text-center">Date & Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($historique)): ?>
                                <?php foreach ($historique as $hist): ?>
                                    <tr>
                                        <td><strong class="text-primary"><?= esc($hist['operateur_nom']) ?></strong></td>
                                        <td><code class="fs-6"><?= esc($hist['numero_destinataire']) ?></code></td>
                                        <td class="text-end fw-bold"><?= number_format($hist['montant'], 2, ',', ' ') ?> Ar</td>
                                        <td class="text-center text-muted"><?= esc($hist['pourcentage']) ?> %</td>
                                        <td class="text-end text-success fw-bold">+ <?= number_format($hist['gain_commission'], 2, ',', ' ') ?> Ar</td>
                                        <td class="text-end text-danger"><?= number_format($hist['frais'], 2, ',', ' ') ?> Ar</td>
                                        <td class="text-center text-secondary small"><?= date('d/m/Y H:i', strtotime($hist['date_transaction'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted p-4">Aucun historique de transfert ne correspond à ces critères.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    <!-- Bootstrap JS Bundle pour la gestion d'éventuels composants interactifs -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>