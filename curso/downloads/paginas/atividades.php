<?php
// PÁGINA DE ATIVIDADES: as 5 questões, "Conferir resposta" em cada uma e "Concluir módulo"
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

$usuario_id = (int)$_SESSION['usuario_id'];
$modulo = buscar_modulo_do_aluno($pdo, (int)($_GET['id'] ?? 0), $eh_professor, $usuario_id);

if (!$modulo) { header('Location: painel.php'); exit; }
if (!$modulo['liberado']) { header('Location: painel.php?erro=bloqueado'); exit; }

// 1) As questões. Repare: NÃO buscamos a coluna "correta" aqui.
//    A resposta certa só sai do servidor depois que o aluno confere.
$st = $pdo->prepare("SELECT id, enunciado, alt_a, alt_b, alt_c, alt_d
                       FROM questoes
                      WHERE modulo_id = ? AND tipo = 'exercicio'
                      ORDER BY id");
$st->execute([$modulo['id']]);
$questoes = $st->fetchAll(PDO::FETCH_ASSOC);

// 2) O que o aluno já respondeu (para reabrir a página já corrigida)
$st = $pdo->prepare("SELECT q.id, r.escolhida, r.acertou, q.correta, q.explicacao
                       FROM respostas r
                       JOIN questoes q ON q.id = r.questao_id
                      WHERE r.usuario_id = ? AND q.modulo_id = ? AND q.tipo = 'exercicio'");
$st->execute([$usuario_id, $modulo['id']]);
$respondidas = [];
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { $respondidas[(int)$r['id']] = $r; }

$total   = count($questoes);
$feitas  = count($respondidas);
$tudo_ok = ($total > 0 && $feitas >= $total);

$titulo_pagina = 'Atividades · ' . $modulo['titulo'];
$imagem_fundo  = $modulo['imagem'] ?: 'introducao.jpg';
$capa_etiqueta = 'Atividades de fixação';
$capa_titulo   = $modulo['titulo'];

require __DIR__ . '/../includes/topo_modulo.php';
?>

<a class="link-voltar" href="modulo.php?id=<?= $modulo['id'] ?>">← Voltar ao conteúdo</a>

<p class="atividades-intro">Assinale <strong>uma</strong> alternativa em cada questão e clique em
   <strong>Conferir resposta</strong>. Não há nota mínima: o objetivo é aprender.</p>

<?php if (($_GET['erro'] ?? '') === 'faltam'): ?>
    <p class="aviso-atividades">Confira todas as questões antes de concluir o módulo.</p>
<?php endif; ?>

<?php if (!$questoes): ?>
    <p>As atividades deste módulo ainda não foram cadastradas.</p>
<?php endif; ?>

<?php foreach ($questoes as $i => $q):
    $resp = $respondidas[(int)$q['id']] ?? null;   // null = ainda não respondeu
?>
    <form class="questao <?= $resp ? 'respondida' : '' ?>" action="conferir.php" method="post">
        <?= csrf_campo() ?>
        <input type="hidden" name="questao_id" value="<?= (int)$q['id'] ?>">

        <p class="enunciado"><span class="numero"><?= $i + 1 ?></span> <?= htmlspecialchars($q['enunciado']) ?></p>

        <?php foreach (['A' => 'alt_a', 'B' => 'alt_b', 'C' => 'alt_c', 'D' => 'alt_d'] as $letra => $coluna):
            $classe = '';
            if ($resp) {
                if ($letra === strtoupper(trim($resp['correta']))) $classe = 'alt-certa';
                elseif ($letra === $resp['escolhida'])             $classe = 'alt-errada';
            }
        ?>
            <label class="alt <?= $classe ?>">
                <input type="radio" name="resposta" value="<?= $letra ?>"
                       <?= $resp && $resp['escolhida'] === $letra ? 'checked' : '' ?>
                       <?= $resp ? 'disabled' : '' ?>>
                <span class="letra"><?= $letra ?></span>
                <span><?= htmlspecialchars($q[$coluna]) ?></span>
            </label>
        <?php endforeach; ?>

        <button type="submit" class="btn-conferir" <?= $resp ? 'hidden' : '' ?>>Conferir resposta</button>

        <div class="feedback <?= $resp ? ($resp['acertou'] ? 'certo' : 'errado') : '' ?>" <?= $resp ? '' : 'hidden' ?>>
            <?php if ($resp): ?>
                <strong><?= $resp['acertou'] ? '✓ Resposta correta!' : '✗ Não foi dessa vez. A resposta certa é a letra ' . htmlspecialchars(strtoupper(trim($resp['correta']))) . '.' ?></strong>
                <p><?= htmlspecialchars($resp['explicacao'] ?? '') ?></p>
            <?php endif; ?>
        </div>
    </form>
<?php endforeach; ?>

<!-- FIM DA FOLHA: voltar e concluir -->
<div class="folha-fim">
    <a class="btn-secundario" href="modulo.php?id=<?= $modulo['id'] ?>">← Voltar ao conteúdo</a>

    <div class="concluir-area">
        <span class="contador">Questões conferidas: <strong id="contador"><?= $feitas ?> de <?= $total ?></strong></span>
        <form action="concluir.php" method="post">
            <?= csrf_campo() ?>
            <input type="hidden" name="modulo_id" value="<?= $modulo['id'] ?>">
            <button type="submit" id="btn-concluir" class="btn-principal" <?= $tudo_ok || $total === 0 ? '' : 'disabled' ?>>
                Concluir módulo ✓
            </button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/rodape_modulo.php'; ?>
