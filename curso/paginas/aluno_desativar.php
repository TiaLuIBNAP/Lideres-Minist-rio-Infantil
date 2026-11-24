<?php
require_once "../includes/somente_professor.php";

$titulo_pagina = "Desativar aluno";
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE id = ? AND perfil = 'aluno'");
$stmt->execute([$id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'texto' => 'Aluno não encontrado.'];
    header("Location: alunos.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $erro = "Sessão expirada. Tente de novo.";
    } else {
        $motivo = mb_substr(trim($_POST['motivo'] ?? ''), 0, 255);

        $pdo->prepare("UPDATE usuarios
                       SET ativo = 0, desativado_em = NOW(), motivo_desativacao = ?
                       WHERE id = ? AND perfil = 'aluno'")
            ->execute([$motivo === '' ? null : $motivo, $id]);

        $_SESSION['flash'] = [
            'tipo'  => 'ok',
            'texto' => '<strong>' . htmlspecialchars($aluno['nome']) . '</strong> foi desativado. '
                     . 'O histórico dele foi preservado e você pode reativá-lo quando quiser.'
        ];
        header("Location: alunos.php");
        exit;
    }
}

require "../includes/topo.php";
?>

<h1>Desativar aluno</h1>

<?php if ($erro): ?>
  <p class="mensagem mensagem-erro"><?= $erro ?></p>
<?php endif; ?>

<form method="post" class="formulario">
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= $aluno['id'] ?>">

  <p>
    Você vai desativar <strong><?= htmlspecialchars($aluno['nome']) ?></strong>
    (<?= htmlspecialchars($aluno['email']) ?>).
    Ele não conseguirá entrar no curso, mas o progresso e as notas ficam guardados.
  </p>

  <label for="motivo">Motivo (opcional)</label>
  <input type="text" id="motivo" name="motivo" maxlength="255"
         placeholder="Ex.: desistiu do curso">

  <button type="submit">Confirmar desativação</button>
  <a href="alunos.php" class="link-cancelar">Cancelar</a>
</form>

<?php require "../includes/rodape.php"; ?>