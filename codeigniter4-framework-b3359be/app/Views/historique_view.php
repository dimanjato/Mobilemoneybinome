<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des operations</title>
</head>
<body>
<?= $this->include('partials/nav') ?>
<div class="mm-container" style="max-width:720px;">
    <h2>Historique des operations</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mm-flash-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (empty($operations)): ?>
        <p>Aucune operation pour le moment.</p>
    <?php else: ?>
    <table class="mm-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Frais</th>
                <th>Sens</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($operations as $op): ?>
            <?php
                $estEmetteur = ((int) $op['id1'] === (int) $idUser);
                $sens = 'Entrant';
                $classe = 'mm-badge-in';
                if ($op['type_nom'] === 'retrait' || ($op['type_nom'] === 'transfert' && $estEmetteur)) {
                    $sens = 'Sortant';
                    $classe = 'mm-badge-out';
                } elseif ($op['type_nom'] === 'depot') {
                    $sens = 'Entrant';
                }
            ?>
            <tr>
                <td><?= esc($op['date']) ?></td>
                <td><?= esc(ucfirst($op['type_nom'])) ?></td>
                <td><?= number_format($op['montant'], 2, ',', ' ') ?> Ar</td>
                <td><?= number_format($op['frai'], 2, ',', ' ') ?> Ar</td>
                <td class="<?= $classe ?>"><?= $sens ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
</body>
</html>
