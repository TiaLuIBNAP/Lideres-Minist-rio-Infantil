<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

// 1) Busca os módulos e calcula quem está liberado
$modulos = modulos_do_aluno($pdo, (int)$_SESSION['usuario_id'], $eh_professor);

// 2) Números da barra de progresso (aparece no rodapé)
$total      = count($modulos);
$feitos     = count(array_filter($modulos, fn($m) => $m['concluido']));
$percentual = $total ? (int)round($feitos * 100 / $total) : 0;
$mostrar_progresso = true;

// Mantenha estas duas linhas iguais às do seu painel atual (topo e rodapé)
require __DIR__ . '/../includes/topo.php';
?>

<h1 class="painel-titulo">Módulos do curso</h1>

<?php if (($_GET['erro'] ?? '') === 'bloqueado'): ?>
    <p class="painel-aviso">Esse módulo ainda está bloqueado. Conclua o módulo anterior para liberá-lo.</p>
<?php endif; ?>

<div class="grade-cards">
<?php foreach ($modulos as $m):

    // Define o rótulo e a cor do card
    if ($m['concluido']) {
        $classe = 'concluido';  $rotulo = '✓ Concluído';
    } elseif ($m['liberado']) {
        $classe = 'andamento';  $rotulo = $eh_professor ? 'Liberado' : 'Em andamento';
    } else {
        $classe = 'bloqueado';  $rotulo = '🔒 Bloqueado';
    }
?>
    <article class="card-modulo <?= $classe ?>">
        <div class="card-imagem">
            <img src="<?= BASE_URL ?>/assets/img/modulos/<?= htmlspecialchars($m['imagem'] ?? 'introducao.jpg') ?>"
                 alt="" loading="lazy">
            <span class="card-rotulo"><?= $rotulo ?></span>
        </div>

        <div class="card-corpo">
            <p class="card-etiqueta"><?= rotulo_modulo($m['ordem']) ?></p>
            <h2 class="card-titulo"><?= htmlspecialchars($m['titulo']) ?></h2>
            <p class="card-descricao"><?= htmlspecialchars($m['descricao'] ?? '') ?></p>

            <?php if ($m['liberado']): ?>
                <a class="card-botao" href="modulo.php?id=<?= $m['id'] ?>">
                    <?= $m['concluido'] ? 'Rever módulo' : 'Iniciar módulo' ?>
                </a>
            <?php else: ?>
                <span class="card-botao desativado">Conclua o módulo anterior</span>
            <?php endif; ?>
        </div>
    </article>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
