<?php
require_once "../includes/somente_professor.php";

$titulo_pagina = "Alunos";

// Filtro da lista: ativos (padrão), desativados ou todos
$filtro = $_GET['filtro'] ?? 'ativos';
if (!in_array($filtro, ['ativos', 'desativados', 'todos'])) {
    $filtro = 'ativos';
}

// Guarda uma mensagem na sessão e volta para a lista
function avisar($tipo, $texto, $filtro) {
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
    header("Location: alunos.php?filtro=" . $filtro);
    exit;
}

// ---------- Ações (resetar, reativar, excluir) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_ok()) {
        avisar('erro', 'Sessão expirada. Tente de novo.', $filtro);
    }

    $acao = $_POST['acao'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    // Só age sobre alunos: o professor nunca entra nessas ações
    $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE id = ? AND perfil = 'aluno'");
    $stmt->execute([$id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        avisar('erro', 'Aluno não encontrado.', $filtro);
    }

    $nome = htmlspecialchars($aluno['nome']);

    if ($acao === 'resetar') {
        // Gera uma senha provisória nova e obriga a troca no próximo login
        $nova = bin2hex(random_bytes(4));
        $hash = password_hash($nova, PASSWORD_DEFAULT);

        $pdo->prepare("UPDATE usuarios SET senha = ?, trocar_senha = 1 WHERE id = ?")
            ->execute([$hash, $id]);

        avisar('ok', "Senha de <strong>$nome</strong> redefinida. Nova senha provisória: "
            . "<strong>$nova</strong>. Anote agora: ela não será mostrada de novo.", $filtro);

    } elseif ($acao === 'reativar') {
        $pdo->prepare("UPDATE usuarios
                       SET ativo = 1, desativado_em = NULL, motivo_desativacao = NULL
                       WHERE id = ?")->execute([$id]);

        avisar('ok', "<strong>$nome</strong> foi reativado e retoma de onde parou.", $filtro);

    } elseif ($acao === 'excluir') {
        // Só exclui quem não tem nenhum progresso nem tentativa de avaliação
        $stmt = $pdo->prepare(
            "SELECT (SELECT COUNT(*) FROM progresso WHERE usuario_id = ?)
                  + (SELECT COUNT(*) FROM tentativas_avaliacao WHERE usuario_id = ?)"
        );
        $stmt->execute([$id, $id]);

        if ($stmt->fetchColumn() > 0) {
            avisar('erro', "<strong>$nome</strong> já tem histórico no curso. "
                . "Use Desativar para preservar os dados.", $filtro);
        }

        $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND perfil = 'aluno'")
            ->execute([$id]);

        avisar('ok', "<strong>$nome</strong> foi excluído definitivamente.", $filtro);
    }

    avisar('erro', 'Ação desconhecida.', $filtro);
}

// ---------- Mensagem da ação anterior (aparece uma vez só) ----------
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ---------- Lista de alunos ----------
// $where é montado só com textos fixos do próprio código, nunca com dados digitados
$where = "u.perfil = 'aluno'";
if ($filtro === 'ativos')      { $where .= " AND u.ativo = 1"; }
if ($filtro === 'desativados') { $where .= " AND u.ativo = 0"; }

$alunos = $pdo->query(
    "SELECT u.id, u.nome, u.email, u.ativo, u.criado_em,
            u.desativado_em, u.motivo_desativacao,
            (SELECT COUNT(*) FROM progresso p WHERE p.usuario_id = u.id) AS concluidos,
            (SELECT COUNT(*) FROM tentativas_avaliacao t WHERE t.usuario_id = u.id) AS tentativas
     FROM usuarios u
     WHERE $where
     ORDER BY u.nome"
)->fetchAll(PDO::FETCH_ASSOC);

$total_modulos = (int) $pdo->query("SELECT COUNT(*) FROM modulos")->fetchColumn();

require "../includes/topo.php";
?>

<h1>Alunos</h1>

<?php if ($flash): ?>
  <p class="mensagem mensagem-larga <?= $flash['tipo'] === 'ok' ? 'mensagem-ok' : 'mensagem-erro' ?>">
    <?= $flash['texto'] ?>
  </p>
<?php endif; ?>

<p><a class="btn-pequeno" href="aluno_cadastrar.php">+ Cadastrar novo aluno</a></p>

<div class="filtros">
  <a href="?filtro=ativos"      class="<?= $filtro === 'ativos' ? 'ativo' : '' ?>">Ativos</a>
  <a href="?filtro=desativados" class="<?= $filtro === 'desativados' ? 'ativo' : '' ?>">Desativados</a>
  <a href="?filtro=todos"       class="<?= $filtro === 'todos' ? 'ativo' : '' ?>">Todos</a>
  <span>(<?= count($alunos) ?> na lista)</span>
</div>

<?php if (count($alunos) === 0): ?>
  <p>Nenhum aluno nesta lista.</p>
<?php else: ?>
  <div class="tabela-rolagem">
  <table class="tabela">
    <thead>
      <tr>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Progresso</th>
        <th>Situação</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($alunos as $a): ?>
        <tr class="<?= $a['ativo'] ? '' : 'linha-inativa' ?>">
          <td><?= htmlspecialchars($a['nome']) ?></td>
          <td><?= htmlspecialchars($a['email']) ?></td>
          <td><?= $a['concluidos'] ?> de <?= $total_modulos ?></td>
          <td>
            <?php if ($a['ativo']): ?>
              <span class="etiqueta etiqueta-ativo">Ativo</span>
            <?php else: ?>
              <span class="etiqueta etiqueta-inativo">Desativado</span>
              <?php if ($a['desativado_em']): ?>
                <br><small>em <?= date('d/m/Y', strtotime($a['desativado_em'])) ?></small>
              <?php endif; ?>
              <?php if ($a['motivo_desativacao']): ?>
                <br><small><?= htmlspecialchars($a['motivo_desativacao']) ?></small>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td class="acoes">

            <a class="btn-pequeno btn-neutro" href="aluno_editar.php?id=<?= $a['id'] ?>">Editar</a>

            <form method="post" action="alunos.php?filtro=<?= $filtro ?>" class="form-inline"
                  data-confirma="<?= htmlspecialchars('Gerar uma nova senha provisória para ' . $a['nome'] . '?') ?>">
              <?= csrf_campo() ?>
              <input type="hidden" name="acao" value="resetar">
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <button type="submit" class="btn-pequeno btn-aviso">Resetar senha</button>
            </form>

            <?php if ($a['ativo']): ?>
              <a class="btn-pequeno btn-aviso" href="aluno_desativar.php?id=<?= $a['id'] ?>">Desativar</a>
            <?php else: ?>
              <form method="post" action="alunos.php?filtro=<?= $filtro ?>" class="form-inline"
                    data-confirma="<?= htmlspecialchars('Reativar ' . $a['nome'] . '?') ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="reativar">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" class="btn-pequeno">Reativar</button>
              </form>
            <?php endif; ?>

            <?php if ($a['concluidos'] == 0 && $a['tentativas'] == 0): ?>
              <form method="post" action="alunos.php?filtro=<?= $filtro ?>" class="form-inline"
                    data-confirma="<?= htmlspecialchars('EXCLUIR ' . $a['nome'] . ' definitivamente? Isso não pode ser desfeito.') ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" class="btn-pequeno btn-perigo">Excluir</button>
              </form>
            <?php endif; ?>

          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
<?php endif; ?>

<script>
// Pede confirmação antes de enviar qualquer formulário que tenha data-confirma
document.querySelectorAll('form[data-confirma]').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!confirm(form.dataset.confirma)) {
      e.preventDefault();
    }
  });
});
</script>

<?php require "../includes/rodape.php"; ?>