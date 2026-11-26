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

// ---------------------------------------------------------------------
// ACRESCENTADO NO MÓDULO 4
// ---------------------------------------------------------------------

// Busca UM módulo (com texto e vídeo) já com 'concluido' e 'liberado'.
// Devolve null se o id não existir.
function buscar_modulo_do_aluno(PDO $pdo, int $id, bool $eh_professor, int $usuario_id): ?array
{
    foreach (modulos_do_aluno($pdo, $usuario_id, $eh_professor) as $m) {
        if ($m['id'] === $id) {
            // modulos_do_aluno() não traz o texto nem o vídeo; buscamos aqui
            $st = $pdo->prepare("SELECT conteudo, video_url FROM modulos WHERE id = ?");
            $st->execute([$id]);
            return $m + $st->fetch(PDO::FETCH_ASSOC);
        }
    }
    return null;
}

// Converte um link do YouTube no endereço de "embed" (o player dentro da página).
// Aceita: youtube.com/watch?v=CODIGO, youtu.be/CODIGO e youtube.com/embed/CODIGO.
// Devolve '' se o link estiver vazio ou for inválido.
function youtube_embed(?string $url): string
{
    if (!$url) return '';
    if (preg_match('~(?:v=|youtu\.be/|/embed/)([A-Za-z0-9_-]{11})~', $url, $r)) {
        return 'https://www.youtube-nocookie.com/embed/' . $r[1];
    }
    return '';
}

// Quantas questões de exercício o módulo tem e quantas o aluno já respondeu
function andamento_exercicios(PDO $pdo, int $modulo_id, int $usuario_id): array
{
    $st = $pdo->prepare("SELECT COUNT(*) FROM questoes WHERE modulo_id = ? AND tipo = 'exercicio'");
    $st->execute([$modulo_id]);
    $total = (int)$st->fetchColumn();

    $st = $pdo->prepare("SELECT COUNT(*) FROM respostas r
                           JOIN questoes q ON q.id = r.questao_id
                          WHERE r.usuario_id = ? AND q.modulo_id = ? AND q.tipo = 'exercicio'");
    $st->execute([$usuario_id, $modulo_id]);
    return ['total' => $total, 'respondidas' => (int)$st->fetchColumn()];
}
