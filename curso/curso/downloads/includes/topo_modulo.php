<?php
// Cabeçalho das páginas de CONTEÚDO e ATIVIDADES.
// Antes de incluir, a página define:
//   $titulo_pagina   -> texto da aba do navegador
//   $imagem_fundo    -> nome do arquivo em assets/img/modulos/hero/
//   $capa_etiqueta   -> texto pequeno acima do título (ex.: "Módulo 1")
//   $capa_titulo     -> título grande sobre a imagem

$st = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$st->execute([$_SESSION['usuario_id']]);
$nome_usuario = (string)$st->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo_pagina) ?> | <?= NOME_CURSO ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modulo.css">
</head>
<body class="pagina-modulo"
      style="--imagem: url('<?= BASE_URL ?>/assets/img/modulos/hero/<?= htmlspecialchars($imagem_fundo) ?>')">

<!-- BARRA SUPERIOR: logo | título do curso | usuário e Sair -->
<header class="barra-topo">
    <a class="barra-logo" href="painel.php" title="Voltar aos módulos">
        <img src="<?= BASE_URL ?>/assets/img/logoCamisaSalina.png" alt="Logo do curso">
    </a>
    <div class="barra-titulo"><?= NOME_CURSO ?></div>
    <div class="barra-usuario">
        <span class="barra-nome"><?= htmlspecialchars($nome_usuario) ?></span>
        <a class="btn-sair" href="sair.php">Sair</a>
    </div>
</header>

<!-- CAPA: título escrito sobre a imagem do módulo -->
<section class="capa">
    <p class="capa-etiqueta"><?= htmlspecialchars($capa_etiqueta) ?></p>
    <h1 class="capa-titulo"><?= htmlspecialchars($capa_titulo) ?></h1>
</section>

<!-- FOLHA: a "página" que sobe por cima da imagem -->
<main class="folha">
