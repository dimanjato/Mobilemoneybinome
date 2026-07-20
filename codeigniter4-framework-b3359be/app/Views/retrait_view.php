<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Faire un retrait</title>
</head>
<body>
<?= $this->include('partials/nav') ?>
<div class="mm-container">
    <h2>Faire un retrait</h2>
    <p>Solde actuel : <strong><?= number_format($solde, 2, ',', ' ') ?> Ar</strong></p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mm-flash-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('client/retrait') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="mm-field">
            <label for="montant">Montant a retirer (Ar)</label>
            <input type="number" step="0.01" min="1" id="montant" name="montant" required>
        </div>
        <p><small>Des frais seront appliques selon le bareme en vigueur.</small></p>
        <button type="submit" class="mm-btn">Retirer (automatique)</button>
    </form>
</div>
</body>
</html>
