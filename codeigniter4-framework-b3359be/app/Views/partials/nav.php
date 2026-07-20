<style>
    body { font-family: Arial, sans-serif; background:#f2f4f7; margin:0; }
    nav.mm-nav { background:#0b5ed7; padding:12px 20px; display:flex; gap:18px; flex-wrap:wrap; align-items:center; }
    nav.mm-nav a { color:#fff; text-decoration:none; font-size:14px; }
    nav.mm-nav a:hover { text-decoration:underline; }
    nav.mm-nav .brand { font-weight:bold; margin-right:auto; }
    .mm-container { max-width:520px; margin:30px auto; background:#fff; border-radius:10px; padding:24px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
    .mm-flash-error { background:#fdecea; color:#b3261e; padding:10px 14px; border-radius:6px; margin-bottom:16px; }
    .mm-flash-success { background:#e6f4ea; color:#1e7e34; padding:10px 14px; border-radius:6px; margin-bottom:16px; }
    .mm-field { margin-bottom:16px; }
    .mm-field label { display:block; margin-bottom:6px; font-size:14px; color:#333; }
    .mm-field input { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; box-sizing:border-box; font-size:15px; }
    .mm-btn { background:#0b5ed7; color:#fff; border:none; padding:10px 18px; border-radius:6px; font-size:15px; cursor:pointer; }
    .mm-btn:hover { background:#094aad; }
    table.mm-table { width:100%; border-collapse:collapse; font-size:14px; }
    table.mm-table th, table.mm-table td { padding:8px 6px; border-bottom:1px solid #eee; text-align:left; }
    .mm-badge-in { color:#1e7e34; font-weight:bold; }
    .mm-badge-out { color:#b3261e; font-weight:bold; }
</style>
<nav class="mm-nav">
    <span class="brand">Mobile Money</span>
    <a href="<?= base_url('client/voirsolde') ?>">Solde</a>
    <a href="<?= base_url('client/depot') ?>">Depot</a>
    <a href="<?= base_url('client/retrait') ?>">Retrait</a>
    <a href="<?= base_url('client/transfert') ?>">Transfert</a>
    <a href="<?= base_url('client/historique') ?>">Historique</a>
    <a href="<?= base_url('logout') ?>">Deconnexion</a>
</nav>
