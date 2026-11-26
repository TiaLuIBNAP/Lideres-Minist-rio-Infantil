<?php
// Guarda a nota (1 a 5 estrelas) que o aluno deu ao módulo. Responde em JSON.
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
$modulo_id  = (int)($_POST['modulo_id'] ?? 0);
$nota       = (int)($_POST['nota'] ?? 0);

if ($nota < 1 || $nota > 5) responder(['ok' => false, 'erro' => 'Nota inválida.'], 422);

$modulo = buscar_modulo_do_aluno($pdo, $modulo_id, $eh_professor, $usuario_id);
if (!$modulo || !$modulo['liberado']) responder(['ok' => false, 'erro' => 'Módulo bloqueado.'], 403);

// Se já existe uma nota deste aluno para este módulo, ela é atualizada
$st = $pdo->prepare("INSERT INTO avaliacoes_modulo (usuario_id, modulo_id, nota) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE nota = VALUES(nota)");
$st->execute([$usuario_id, $modulo_id, $nota]);

responder(['ok' => true]);
