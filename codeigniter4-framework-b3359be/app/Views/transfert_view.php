<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Faire un transfert</title>
<style>
.mm-numero-row { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; }
.mm-btn-remove { background: #e74c3c; color: #fff; border: none; border-radius: 4px; padding: 4px 10px; cursor: pointer; }
.mm-btn-add { background: #2ecc71; color: #fff; border: none; border-radius: 4px; padding: 6px 12px; cursor: pointer; margin-top: 4px; }
.mm-montant-row { display: flex; align-items: center; gap: 12px; }
.mm-checkbox-inline { display: flex; align-items: center; gap: 6px; font-size: 0.9em; white-space: nowrap; }
</style>
</head>
<body>
<?= $this->include('partials/nav') ?>
<div class="mm-container">
<h2>Faire un transfert</h2>

<p>Solde actuel : <strong><?= number_format($solde, 2, ',', ' ') ?> Ar</strong></p>

<?php if (session()->getFlashdata('error')): ?>
<div class="mm-flash-error"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= base_url('client/transfert') ?>" method="POST">
<?= csrf_field() ?>

<div class="mm-field" id="numeros-container">
<label>Numero(s) du destinataire</label>
<div class="mm-numero-row">
<input type="text" name="numero[]" placeholder="Ex: 033 12 345 67" required>
</div>
</div>

<button type="button" id="add-numero" class="mm-btn-add">+ Ajouter un destinataire</button>

<div class="mm-field">
<label for="montant">Montant a transferer (Ar) — le meme montant sera envoye a chaque destinataire</label>
<div class="mm-montant-row">
<input type="number" step="0.01" min="1" id="montant" name="montant" required>
<label class="mm-checkbox-inline">
<input type="checkbox" id="inclure_frais" name="inclure_frais" value="1">
Inclure les frais dans le montant
</label>
</div>
</div>

<p><small>Si la case est cochee, les frais sont preleves sur le montant saisi. Sinon, les frais s'ajoutent en plus du montant.</small></p>
<button type="submit" class="mm-btn">Transferer</button>
</form>
</div>

<script>
document.getElementById('add-numero').addEventListener('click', function () {
    const container = document.getElementById('numeros-container');

    const row = document.createElement('div');
    row.className = 'mm-numero-row';

    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'numero[]';
    input.placeholder = 'Ex: 033 12 345 67';
    input.required = true;

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'mm-btn-remove';
    removeBtn.textContent = '-';
    removeBtn.addEventListener('click', function () {
        row.remove();
    });

    row.appendChild(input);
    row.appendChild(removeBtn);
    container.appendChild(row);
});
</script>
</body>
</html>