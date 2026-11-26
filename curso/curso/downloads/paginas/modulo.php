<?php
// PÁGINA DE CONTEÚDO do módulo: vídeo + texto + avaliação + botão das atividades
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

$usuario_id = (int)$_SESSION['usuario_id'];
$modulo = buscar_modulo_do_aluno($pdo, (int)($_GET['id'] ?? 0), $eh_professor, $usuario_id);

// Módulo que não existe -> painel
if (!$modulo) { header('Location: painel.php'); exit; }
// TRAVA: módulo bloqueado não abre, mesmo digitando o endereço
if (!$modulo['liberado']) { header('Location: painel.php?erro=bloqueado'); exit; }

// Nota que o aluno já deu (0 = ainda não avaliou)
$st = $pdo->prepare("SELECT nota FROM avaliacoes_modulo WHERE usuario_id = ? AND modulo_id = ?");
$st->execute([$usuario_id, $modulo['id']]);
$nota_atual = (int)$st->fetchColumn();

$embed = youtube_embed($modulo['video_url']);

// Dados para o topo
$titulo_pagina = $modulo['titulo'];
$imagem_fundo  = $modulo['imagem'] ?: 'introducao.jpg';
$capa_etiqueta = rotulo_modulo($modulo['ordem']);
$capa_titulo   = $modulo['titulo'];

require __DIR__ . '/../includes/topo_modulo.php';
?>

<a class="link-voltar" href="painel.php">← Todos os módulos</a>

<!-- VÍDEO -->
<div class="video-caixa">
    <?php if ($embed): ?>
        <iframe src="<?= htmlspecialchars($embed) ?>" title="Vídeo do módulo"
                allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen></iframe>
    <?php else: ?>
        <div class="video-vazio">
            <span class="video-play">▶</span>
            <p>O vídeo deste módulo será carregado aqui</p>
        </div>
    <?php endif; ?>
</div>

<!-- TEXTO DO MÓDULO (HTML guardado na coluna "conteudo") -->
<div class="texto-modulo">
    <?= $modulo['conteudo'] ?: '<p>Conteúdo em preparação.</p>' ?>
</div>

<!-- FIM DA FOLHA: avaliação + botão -->
<div class="folha-fim">
    <form class="form-avaliacao" action="avaliar.php" method="post">
        <?= csrf_campo() ?>
        <input type="hidden" name="modulo_id" value="<?= $modulo['id'] ?>">
        <span class="avaliacao-titulo">Avalie este módulo</span>
        <div class="estrelas">
            <?php for ($n = 5; $n >= 1; $n--): ?>
                <input type="radio" name="nota" id="nota<?= $n ?>" value="<?= $n ?>" <?= $nota_atual === $n ? 'checked' : '' ?>>
                <label for="nota<?= $n ?>" title="<?= $n ?> estrela(s)">★</label>
            <?php endfor; ?>
        </div>
        <span class="avaliacao-msg"><?= $nota_atual ? 'Sua avaliação foi salva.' : '' ?></span>
    </form>

    <a class="btn-principal" href="atividades.php?id=<?= $modulo['id'] ?>">Ir para as atividades →</a>
</div>

<?php require __DIR__ . '/../includes/rodape_modulo.php'; ?>
