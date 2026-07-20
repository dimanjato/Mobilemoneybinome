<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Mobile Money</title>
</head>
<body>

    <form action="<?= base_url('login') ?>" method="POST">
        <?= csrf_field() ?> <!-- Protection contre les failles CSRF native dans CI4 -->
        
        <h2>Connexion Mobile Money</h2>

        <!-- Affichage des messages d'erreur de session (Flashdata) -->
        <?php if (session()->getFlashdata('error')): ?>
            <p style="color: red;"><?= session()->getFlashdata('error') ?></p>
        <?php   endif; ?>

        <label for="phone">Numéro de téléphone :</label>
        <input type="text" id="phone" name="phone" placeholder="Ex: 034 12 345 67" required>

        <button type="submit">Se connecter</button>
    </form>

</body>
</html>