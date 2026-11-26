<?php
// Funções dos módulos. Usadas pelo painel e pela página do módulo.

// Devolve os 9 módulos já com duas informações calculadas para o aluno:
//   'concluido' -> o aluno já concluiu este módulo?
//   'liberado'  -> o aluno pode entrar neste módulo?
function modulos_do_aluno(PDO $pdo, int $usuario_id, bool $eh_professor): array
{
    $sql = "SELECT m.id, m.ordem, m.titulo, m.descricao, m.imagem,
                   (SELECT COUNT(*) FROM progresso p
                     WHERE p.modulo_id = m.id AND p.usuario_id = ?) AS concluido
              FROM modulos m
             ORDER BY m.ordem";
    $st = $pdo->prepare($sql);
    $st->execute([$usuario_id]);
    $lista = $st->fetchAll(PDO::FETCH_ASSOC);

    $anterior_concluido = true;   // o primeiro módulo é sempre liberado
    foreach ($lista as &$m) {
        $m['id']        = (int)$m['id'];
        $m['ordem']     = (int)$m['ordem'];
        $m['concluido'] = ((int)$m['concluido'] > 0);
        // Liberado = professor OU módulo anterior concluído
        $m['liberado']  = $eh_professor || $anterior_concluido;
        $anterior_concluido = $m['concluido'];
    }
    unset($m);

    return $lista;
}

// Texto pequeno acima do título do card
function rotulo_modulo(int $ordem): string
{
    if ($ordem === 1) return 'Introdução';
    if ($ordem === 9) return 'Conclusão';
    return 'Módulo ' . ($ordem - 1);
}
