<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/conexao.php";

session_start();

// Quem não está logado volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

// Confere no banco, a cada página, se o usuário continua ativo
$stmt = $pdo->prepare("SELECT nome, perfil, ativo, trocar_senha FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario_atual = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario_atual || !$usuario_atual['ativo']) {
    session_destroy();   // desativado ou excluído: encerra o login na hora
    header("Location: " . BASE_URL . "/index.php?erro=desativado");
    exit;
}

// Mantém a sessão sempre igual ao banco
$_SESSION['usuario_nome'] = $usuario_atual['nome'];
$_SESSION['perfil']       = $usuario_atual['perfil'];
$_SESSION['trocar_senha'] = (int) $usuario_atual['trocar_senha'];

// Senha provisória: só a página de troca de senha é permitida
if (!empty($_SESSION['trocar_senha']) && empty($pagina_troca_senha)) {
    header("Location: " . BASE_URL . "/paginas/trocar_senha.php");
    exit;
}

// Facilita o uso nas páginas: $eh_professor vale true ou false
$eh_professor = ($_SESSION['perfil'] === 'professor');

// ---------- Proteção dos formulários (token CSRF) ----------
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Escreve o campo escondido com o token dentro de um formulário
function csrf_campo() {
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

// Confere se o formulário enviado trouxe o token correto
function csrf_ok() {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}