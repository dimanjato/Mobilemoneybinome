<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Solde</title>
</head>
<body>
<?= $this->include('partials/nav') ?>
<div class="mm-container">
    <h1>Mon Compte</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mm-flash-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mm-flash-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="balance-card">
        <h3>Solde Actuel :</h3>
        <p class="mm-balance">
            <?= number_format($soldeData['solde'], 2, ',', ' ') ?> Ar
        </p>
        <small>Derniere mise a jour : <?= $soldeData['date'] ?? 'Aucune transaction' ?></small>
    </div>
</div>
</body>
</html>
