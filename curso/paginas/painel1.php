<?php
require_once "../includes/auth.php";

$titulo_pagina = "Início";
require "../includes/topo.php";
?>
<?php if (($_GET['senha'] ?? '') === 'ok'): ?>
  <p class="mensagem mensagem-ok">Senha alterada com sucesso!</p>
<?php endif; ?>

<h1>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>
<p>Aqui entrarão os cards dos módulos.</p>

<?php require "../includes/rodape.php"; ?>