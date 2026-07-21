<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Mobile Money</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <script src="<?= base_url('assets/js/theme.js') ?>"></script>
    <style>
        body { display:flex; align-items:center; justify-content:center; height:100vh; }
    </style>
</head>
<body>
<div class="mm-container">
    <h2>Connexion Mobile Money</h2>
    <p class="mm-hint">Entrez votre numero : si vous n'avez pas encore de compte, il sera cree automatiquement.</p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mm-flash-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('login') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="mm-field">
            <label for="phone">Numero de telephone</label>
            <input type="text" id="phone" name="phone" placeholder="Ex: 033 12 345 67" required>
        </div>
        <button type="submit" class="mm-btn" style="width:100%;">Se connecter</button>
    </form>

    <p style="text-align:center; margin-top:18px;">
        <a href="<?= base_url('operateur/config') ?>" class="mm-btn-outline">Ouvrir le Panneau Operateur</a>
    </p>
</div>
</body>
</html>
