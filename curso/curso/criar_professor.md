<?php
require "config/conexao.php";

// Troque pelos seus dados
$nome  = "Wilian Dutra da Silva";
$email = "will@teste.com";
$senha = "wds#123";

// Criptografa a senha (nunca se guarda a senha "pura" no banco)
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Os ? serão trocados pelos valores de forma segura
$sql  = "INSERT INTO usuarios (nome, email, senha, perfil, trocar_senha)
         VALUES (?, ?, ?, 'professor', 0)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nome, $email, $hash]);

echo "Professor criado!";