<?php
require_once __DIR__ . '/../includes/auth.php';

// Primeiro nome do aluno, para a saudação
$primeiro_nome = explode(' ', trim($_SESSION['usuario_nome']))[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boas-vindas | <?= NOME_CURSO ?></title>

    <!-- Fontes do Google: uma para o título e outra para o versículo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Dancing+Script:wght@600;700&family=Nunito:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/boas_vindas.css">
</head>
<body class="boas-vindas">

    <main class="bv-cartao">

        <!-- LADO ESQUERDO: logo -->
        <div class="bv-logo">
            <img src="<?= BASE_URL ?>/assets/img/logoCamisaSalina.png" alt="Logo do curso">
        </div>

        <!-- LADO DIREITO: textos -->
        <div class="bv-texto">
            <p class="bv-saudacao">Olá, <?= htmlspecialchars($primeiro_nome) ?>! Seja muito bem-vindo(a) ao curso</p>

            <h1 class="bv-titulo">Preparando Líderes para o Ministério Infantil</h1>

            <p class="bv-subtitulo">
                Neste curso você aprenderá como se tornar um verdadeiro líder do
                Ministério Infantil. Siga o conteúdo, assista aos vídeos, faça as
                atividades e não deixe de baixar os materiais de apoio.
            </p>

            <blockquote class="bv-versiculo">
                “Tudo o que fizerem, façam de todo o coração, como para o Senhor,
                e não para os homens, uma vez que vocês sabem que receberão do
                Senhor a herança como recompensa. É a Cristo, o Senhor, que
                vocês estão servindo.”
                <cite>Colossenses 3:23,24</cite>
            </blockquote>

            <p class="bv-impacto">Servir com excelência é transformar o comum em extraordinário!</p>

            <a href="painel.php" class="bv-botao">Vamos lá! →</a>
        </div>

    </main>

</body>
</html>