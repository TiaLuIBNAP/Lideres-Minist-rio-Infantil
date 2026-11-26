<?php
// Conclui o módulo: grava em "progresso" e libera o próximo card.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) {
    header('Location: painel.php');
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];
$modulo_id  = (int)($_POST['modulo_id'] ?? 0);

$modulo = buscar_modulo_do_aluno($pdo, $modulo_id, $eh_professor, $usuario_id);
if (!$modulo || !$modulo['liberado']) {
    header('Location: painel.php?erro=bloqueado');
    exit;
}

// Só conclui quem conferiu TODAS as questões (a conferência é feita aqui, no servidor,
// não só pelo botão desabilitado da página)
$and = andamento_exercicios($pdo, $modulo_id, $usuario_id);
if ($and['respondidas'] < $and['total']) {
    header('Location: atividades.php?id=' . $modulo_id . '&erro=faltam');
    exit;
}

// INSERT IGNORE: se o aluno já tinha concluído, não dá erro nem duplica
$st = $pdo->prepare("INSERT IGNORE INTO progresso (usuario_id, modulo_id) VALUES (?, ?)");
$st->execute([$usuario_id, $modulo_id]);

header('Location: painel.php?concluido=1');
exit;
