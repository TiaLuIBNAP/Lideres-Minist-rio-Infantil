<?php
$pagina_troca_senha = true;   // avisa o auth.php que esta página é permitida
require_once "../includes/auth.php";

$titulo_pagina = "Trocar senha";
$obrigatoria   = !empty($_SESSION['trocar_senha']);
$erro          = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atual    = $_POST['senha_atual'] ?? '';
    $nova     = $_POST['nova_senha'] ?? '';
    $confirma = $_POST['confirma_senha'] ?? '';

    // Busca a senha atual (criptografada) deste usuário
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $hash_atual = $stmt->fetchColumn();

    if (!$hash_atual || !password_verify($atual, $hash_atual)) {
        $erro = "A senha atual está incorreta.";

    } elseif (strlen($nova) < 6) {
        $erro = "A nova senha precisa ter pelo menos 6 caracteres.";

    } elseif ($nova !== $confirma) {
        $erro = "A confirmação não é igual à nova senha.";

    } elseif ($nova === $atual) {
        $erro = "A nova senha precisa ser diferente da atual.";

    } else {
        // Grava a nova senha e marca que a troca foi feita
        $novo_hash = password_hash($nova, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "UPDATE usuarios SET senha = ?, trocar_senha = 0 WHERE id = ?"
        );
        $stmt->execute([$novo_hash, $_SESSION['usuario_id']]);

        $_SESSION['trocar_senha'] = 0;   // libera o acesso ao curso

        header("Location: painel.php?senha=ok");
        exit;
    }
}

require "../includes/topo.php";
?>

<h1>Trocar senha</h1>

<?php if ($obrigatoria): ?>
  <p class="mensagem mensagem-aviso">
    Por segurança, troque a senha provisória antes de continuar.
  </p>
<?php endif; ?>

<?php if ($erro): ?>
  <p class="mensagem mensagem-erro"><?= $erro ?></p>
<?php endif; ?>

<form method="post" class="formulario">
  <label for="senha_atual">Senha atual<?= $obrigatoria ? ' (a provisória)' : '' ?></label>
  <input type="password" id="senha_atual" name="senha_atual" required>

  <label for="nova_senha">Nova senha (mínimo 6 caracteres)</label>
  <input type="password" id="nova_senha" name="nova_senha" required>

  <label for="confirma_senha">Repita a nova senha</label>
  <input type="password" id="confirma_senha" name="confirma_senha" required>

  <button type="submit">Salvar nova senha</button>
</form>

<?php require "../includes/rodape.php"; ?>