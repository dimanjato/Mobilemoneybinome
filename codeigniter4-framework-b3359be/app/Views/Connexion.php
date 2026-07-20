<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Mobile Money</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f2f4f7; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .mm-container { max-width:400px; width:100%; background:#fff; border-radius:10px; padding:28px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .mm-flash-error { background:#fdecea; color:#b3261e; padding:10px 14px; border-radius:6px; margin-bottom:16px; }
        .mm-field { margin-bottom:18px; }
        .mm-field label { display:block; margin-bottom:6px; font-size:14px; color:#333; }
        .mm-field input { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; box-sizing:border-box; font-size:15px; }
        .mm-btn { background:#0b5ed7; color:#fff; border:none; padding:10px 18px; border-radius:6px; font-size:15px; cursor:pointer; width:100%; }
        .mm-btn:hover { background:#094aad; }
        p.hint { font-size:12px; color:#777; }
    </style>
</head>
<body>
<div class="mm-container">
    <h2>Connexion Mobile Money</h2>
    <p class="hint">Entrez votre numero : si vous n'avez pas encore de compte, il sera cree automatiquement.</p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mm-flash-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('login') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="mm-field">
            <label for="phone">Numero de telephone</label>
            <input type="text" id="phone" name="phone" placeholder="Ex: 033 12 345 67" required>
        </div>
        <button type="submit" class="mm-btn">Se connecter</button>
    </form>
</div>
</body>
</html>
