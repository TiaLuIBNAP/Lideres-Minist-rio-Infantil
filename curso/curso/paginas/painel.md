<?php
session_start();

// Barreira: quem não está logado volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel</title>
  <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
  <h1>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>
  <p>Perfil: <?= $_SESSION['perfil'] ?></p>
  <a href="sair.php">Sair</a>
</body>
</html>