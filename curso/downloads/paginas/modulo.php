<?php
// PÁGINA TEMPORÁRIA do módulo: por enquanto só confere a trava de segurança.
// O conteúdo (assunto, vídeo e exercícios) virá no próximo módulo da apostila.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

$id = (int)($_GET['id'] ?? 0);
$modulos = modulos_do_aluno($pdo, (int)$_SESSION['usuario_id'], $eh_professor);

// Procura o módulo pedido na lista
$atual = null;
foreach ($modulos as $m) {
    if ($m['id'] === $id) { $atual = $m; break; }
}

// Módulo que não existe -> volta ao painel
if (!$atual) { header('Location: painel.php'); exit; }

// TRAVA: módulo bloqueado não abre, mesmo digitando o endereço na barra
if (!$atual['liberado']) { header('Location: painel.php?erro=bloqueado'); exit; }

require __DIR__ . '/../includes/topo.php';
?>

<p class="card-etiqueta"><?= rotulo_modulo($atual['ordem']) ?></p>
<h1 class="painel-titulo"><?= htmlspecialchars($atual['titulo']) ?></h1>
<p>Conteúdo em construção. Em breve: assunto, vídeo e exercícios.</p>
<p><a href="painel.php">← Voltar aos módulos</a></p>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
