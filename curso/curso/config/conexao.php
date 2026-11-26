<?php
// Dados de acesso ao banco (no XAMP padrão)

$host = "localhost";
$banco = "curso_treinamento";
$usuario = "root";
$senha = "";

try {
// Cria a conexão usando PDO;
$pdo = new PDO(
		"mysql:host=$host;dbname=$banco;charset=utf8mb4",
		$usuario,
		$senha
	);

// Faz o PHP avisar quando houver erro no SQL
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
	die("Erro na conexão: " . $e->getMessage());
}
