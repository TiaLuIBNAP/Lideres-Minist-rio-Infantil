<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo_pagina ?? 'Curso') ?> - <?= NOME_CURSO ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/estilo.css">
</head>
<body>

<header class="topo">
  <div class="topo-conteudo">

    <a class="topo-logo" href="<?= BASE_URL ?>/paginas/painel.php">
      <?= NOME_CURSO ?>
    </a>

    <nav class="topo-menu">
      <a href="<?= BASE_URL ?>/paginas/painel.php">Início</a>
     
     <?php if ($eh_professor): ?>
  <a href="<?= BASE_URL ?>/paginas/alunos.php">Alunos</a>
<?php endif; ?>

  <!-- Aqui entrarão depois: menu suspenso de módulos e página de vídeos -->
    </nav>
    </nav>
    
  <div class="topo-usuario">
  <span class="usuario-nome"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
  <span class="usuario-perfil"><?= ($_SESSION['perfil'] === 'professor') ? 'Professor' : 'Aluno' ?></span>
  <a class="link-senha" href="<?= BASE_URL ?>/paginas/trocar_senha.php">Alterar senha</a>
  <a class="botao-sair" href="<?= BASE_URL ?>/paginas/sair.php">Sair</a>
</div>
</div>

  </div>
</header>

<main class="conteudo">