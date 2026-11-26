<?php
// Recebe UMA resposta (enviada por JavaScript), confere e devolve JSON.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

header('Content-Type: application/json; charset=utf-8');

function responder(array $dados, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) {
    responder(['ok' => false, 'erro' => 'Pedido inválido. Recarregue a página.'], 400);
}

$usuario_id = (int)$_SESSION['usuario_id'];
$questao_id = (int)($_POST['questao_id'] ?? 0);
$escolhida  = strtoupper(trim($_POST['resposta'] ?? ''));

if (!in_array($escolhida, ['A', 'B', 'C', 'D'], true)) {
    responder(['ok' => false, 'erro' => 'Escolha uma alternativa.'], 422);
}

// A questão existe?
$st = $pdo->prepare("SELECT modulo_id, correta, explicacao FROM questoes WHERE id = ? AND tipo = 'exercicio'");
$st->execute([$questao_id]);
$q = $st->fetch(PDO::FETCH_ASSOC);
if (!$q) responder(['ok' => false, 'erro' => 'Questão não encontrada.'], 404);

// O aluno pode entrar nesse módulo?
$modulo = buscar_modulo_do_aluno($pdo, (int)$q['modulo_id'], $eh_professor, $usuario_id);
if (!$modulo || !$modulo['liberado']) responder(['ok' => false, 'erro' => 'Módulo bloqueado.'], 403);

$certa = strtoupper(trim($q['correta']));

// Já respondeu antes? Então devolvemos a resposta que ficou registrada (não vale trocar).
$st = $pdo->prepare("SELECT escolhida, acertou FROM respostas WHERE usuario_id = ? AND questao_id = ?");
$st->execute([$usuario_id, $questao_id]);
$ja = $st->fetch(PDO::FETCH_ASSOC);

if ($ja) {
    $escolhida = $ja['escolhida'];
    $acertou   = (bool)$ja['acertou'];
} else {
    $acertou = ($escolhida === $certa);
    $st = $pdo->prepare("INSERT INTO respostas (usuario_id, questao_id, escolhida, acertou) VALUES (?, ?, ?, ?)");
    $st->execute([$usuario_id, $questao_id, $escolhida, $acertou ? 1 : 0]);
}

responder([
    'ok'         => true,
    'acertou'    => $acertou,
    'escolhida'  => $escolhida,
    'certa'      => $certa,
    'explicacao' => $q['explicacao'] ?? '',
]);
