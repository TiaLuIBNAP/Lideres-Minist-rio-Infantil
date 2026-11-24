<?php
require_once __DIR__ . "/auth.php";   // primeiro exige estar logado

// Se não for professor, volta para o painel
if (!$eh_professor) {
    header("Location: " . BASE_URL . "/paginas/painel.php");
    exit;
}