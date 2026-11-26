<?php
require_once "../includes/somente_professor.php";

$titulo_pagina = "Editar aluno";
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE id = ? AND perfil = 'aluno'");
$stmt->execute([$id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'texto' => 'Aluno não encontrado.'];
    header("Location: alunos.php");
    exit;
}

$erro  = "";
$nome  = $aluno['nome'];
$email = $aluno['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = mb_strtolower(trim($_POST['email'] ?? ''));

    if (!csrf_ok()) {
        $erro = "Sessão expirada. Tente de novo.";

    } elseif ($nome === '' || $email === '') {
        $erro = "Preencha todos os campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";

    } else {
        // O e-mail não pode ser de OUTRO usuário (o do próprio aluno pode repetir)
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ?");
        $stmt->execute([$email, $id]);

        if ($stmt->fetch()) {
            $erro = "Já existe outro usuário com esse e-mail.";
        } else {
            $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ? AND perfil = 'aluno'")
                ->execute([$nome, $email, $id]);

            $_SESSION['flash'] = [
                'tipo'  => 'ok',
                'texto' => 'Dados de <strong>' . htmlspecialchars($nome) . '</strong> atualizados.'
            ];
            header("Location: alunos.php");
            exit;
        }
    }
}

require "../includes/topo.php";
?>

<h1>Editar aluno</h1>

<?php if ($erro): ?>
  <p class="mensagem mensagem-erro"><?= $erro ?></p>
<?php endif; ?>

<form method="post" class="formulario">
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= $aluno['id'] ?>">

  <label for="nome">Nome completo (como sairá no certificado)</label>
  <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>

  <label for="email">E-mail (é o login)</label>
  <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

  <button type="submit">Salvar alterações</button>
  <a href="alunos.php" class="link-cancelar">Cancelar</a>
</form>

<?php require "../includes/rodape.php"; ?>