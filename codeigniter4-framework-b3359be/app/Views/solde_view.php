<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Solde</title>
</head>
<body>
    <h1>Mon Compte</h1>
    
    <div class="balance-card">
        <h3>Solde Actuel :</h3>
        <!-- Affichage du solde formaté en Ariary (Ar) -->
        <p style="font-size: 24px; font-weight: bold;">
            <?= number_format($soldeData['solde'], 2, ',', ' ') ?> Ar
        </p>
        <small>Dernière mise à jour : <?= $soldeData['date'] ?? 'Aucune transaction' ?></small>
    </div>
</body>
</html>