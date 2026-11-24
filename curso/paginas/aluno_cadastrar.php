<?php
require_once "../includes/somente_professor.php";

$titulo_pagina = "Cadastrar aluno";
$erro    = "";
$sucesso = "";
$nome    = "";
$email   = "";
$senha   = "";

// Só executa quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = "Preencha todos os campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";

    } elseif (strlen($senha) < 6) {
        $erro = "A senha provisória precisa ter pelo menos 6 caracteres.";

    } else {
        // O e-mail é o login, então não pode se repetir
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = "Já existe um usuário com esse e-mail.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, senha, perfil, trocar_senha)
                 VALUES (?, ?, ?, 'aluno', 1)"
            );
            $stmt->execute([$nome, $email, $hash]);

            $sucesso = "Aluno cadastrado! Passe a ele o login <strong>"
                     . htmlspecialchars($email)
                     . "</strong> e a senha provisória <strong>"
                     . htmlspecialchars($senha) . "</strong>.";

            // Limpa o formulário para o próximo cadastro
            $nome = "";
            $email = "";
        }
    }
}

// Sugere uma senha provisória aleatória (o professor pode trocar)
$senha_campo = $erro ? $senha : substr(bin2hex(random_bytes(4)), 0, 8);

// Lista de alunos para conferência

require "../includes/topo.php";
?>

<h1>Cadastrar aluno</h1>
<p><a href="alunos.php">&larr; Ver todos os alunos</a></p>
<?php if ($erro): ?>
  <p class="mensagem mensagem-erro"><?= $erro ?></p>
<?php endif; ?>

<?php if ($sucesso): ?>
  <p class="mensagem mensagem-ok"><?= $sucesso ?></p>
<?php endif; ?>

<form method="post" class="formulario">
  <label for="nome">Nome completo (como sairá no certificado)</label>
  <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>

  <label for="email">E-mail (será o login)</label>
  <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

  <label for="senha">Senha provisória</label>
  <input type="text" id="senha" name="senha" value="<?= htmlspecialchars($senha_campo) ?>" required>

  <button type="submit">Cadastrar aluno</button>
</form>


<?php require "../includes/rodape.php"; ?>