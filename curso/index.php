<?php
session_start();                       // liga o uso de sessão (memória do login)
require "config/conexao.php";

// Se já está logado, vai direto para o painel
if (isset($_SESSION['usuario_id'])) {
    header("Location: paginas/painel.php");
    exit;
}
    $erro = "";
if (($_GET['erro'] ?? '') === 'desativado') {
    $erro = "Seu acesso foi desativado. Fale com o professor.";
}

// Só executa quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Procura o usuário pelo e-mail
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Confere a senha digitada com a senha criptografada
    if ($usuario && password_verify($senha, $usuario['senha'])) {
    session_regenerate_id(true);

    $_SESSION['usuario_id']   = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['perfil']       = $usuario['perfil'];
    $_SESSION['trocar_senha'] = (int) $usuario['trocar_senha'];

    if ($usuario['trocar_senha']) {
        header("Location: paginas/trocar_senha.php");
    } else {
        header("Location: paginas/painel.php");
    }
    exit;
} else {
    $erro = "E-mail ou senha incorretos.";
}
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Curso</title>
  <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="pagina-login">
  <main class="caixa-login">
    <h1>Acesse o curso</h1>

    <?php if ($erro): ?>
      <p class="erro"><?= $erro ?></p>
    <?php endif; ?>

    <form method="post">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" required>

      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" required>

      <button type="submit">Entrar</button>
    </form>
  </main>
</body>
</html>