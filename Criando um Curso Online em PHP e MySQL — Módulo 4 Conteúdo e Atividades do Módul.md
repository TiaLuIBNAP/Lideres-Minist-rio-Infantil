# Criando um Curso Online em PHP e MySQL — Módulo 4: Conteúdo e Atividades do Módulo

Nov 25, 2026 · @Tia Lu

## Apresentação do módulo

Neste módulo o curso ganha suas páginas mais importantes: a **página de conteúdo** (vídeo, texto e avaliação com estrelas) e a **página de atividades** (5 questões embaralhadas, com correção comentada e o botão **Concluir módulo**). Começamos pela Introdução; os outros oito módulos usarão exatamente as mesmas páginas, só mudando o conteúdo no banco de dados.

Figura 1 — Página de conteúdo: barra superior, imagem ao fundo, título e a folha com o vídeo

&#91;image: image.png\]

### O que você vai aprender

- Montar uma página com **imagem fixa ao fundo** e uma “folha” que rola por cima dela.
- Guardar o **texto de cada módulo no banco de dados** (coluna `conteudo`), para reaproveitar a mesma página nos 9 módulos.
- **Incorporar um vídeo do YouTube** a partir de um link comum.
- Receber a resposta do aluno com **JavaScript (`fetch`)** e conferir no servidor, sem recarregar a página.
- **Embaralhar** questões e alternativas a cada abertura, sem estragar a correção.
- **Concluir o módulo** gravando em `progresso`, e por que a regra precisa ser conferida no servidor.
- Criar uma avaliação com **estrelas** apenas com HTML e CSS.

### Novo caminho do aluno

Painel → **Iniciar módulo** (conteúdo) → **Ir para as atividades** → conferir as 5 questões → **Concluir módulo** → painel com o próximo módulo liberado.

### Mapa dos arquivos deste módulo

| Arquivo | Pasta | Situação | Para que serve |
| --- | --- | --- | --- |
| `modulo4.sql` | `sql/` | novo | Tabelas novas, texto, vídeo e questões da Introdução |
| `modulos.php` | `includes/` | acrescentado | 3 funções novas |
| `topo_modulo.php` | `includes/` | novo | Barra superior, capa e início da folha |
| `rodape_modulo.php` | `includes/` | novo | Fecha a folha e carrega o JavaScript |
| `modulo.php` | `paginas/` | reescrito | Página de conteúdo |
| `atividades.php` | `paginas/` | novo | Página das 5 questões |
| `conferir.php` | `paginas/` | novo | Confere uma resposta e devolve JSON |
| `avaliar.php` | `paginas/` | novo | Grava a nota em estrelas |
| `concluir.php` | `paginas/` | novo | Conclui o módulo e libera o próximo |
| `painel.php` | `paginas/` | alterado | Mensagem “Módulo concluído!” |
| `modulo.js` | `assets/js/` | novo | Estrelas e “Conferir resposta” |
| `modulo.css` | `assets/css/` | novo | Visual das duas páginas |
| `hero/*.jpg` | `assets/img/modulos/` | novo | Imagens grandes de fundo, uma por módulo |

> Todo o material está no arquivo `curso_modulo4_introducao.zip`, já nas pastas do projeto, com um `INSTALAR.txt`.

## Passo 1 — O banco de dados

Tudo o que muda de módulo para módulo (texto, vídeo, questões) fica no banco. As páginas PHP são escritas uma vez só. Para criar o Módulo 1, basta cadastrar novos dados, sem tocar no código.

No phpMyAdmin, abra o banco `curso_treinamento`, vá na aba **SQL** e rode o arquivo `sql/modulo4.sql`. Ele tem seis partes.

### 1.1 Tabela `respostas`

Guarda o que cada aluno marcou em cada questão.

```sql
CREATE TABLE IF NOT EXISTS respostas (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id    INT NOT NULL,
  questao_id    INT NOT NULL,
  escolhida     CHAR(1) NOT NULL,
  acertou       TINYINT(1) NOT NULL,
  respondida_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_resposta (usuario_id, questao_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (questao_id) REFERENCES questoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

| Trecho | O que faz |
| --- | --- |
| `CREATE TABLE IF NOT EXISTS` | Cria a tabela só se ela ainda não existir. Rodar o script duas vezes não dá erro. |
| `escolhida CHAR(1)` | A letra que o aluno marcou (`A`, `B`, `C` ou `D`). |
| `acertou TINYINT(1)` | 1 = acertou, 0 = errou. O banco não tem “verdadeiro/falso”; usa 0 e 1. |
| `UNIQUE KEY uq_resposta (usuario_id, questao_id)` | Impede duas linhas para o mesmo aluno e a mesma questão. **Cada questão só vale uma vez.** |
| `FOREIGN KEY ... ON DELETE CASCADE` | Liga a resposta ao aluno e à questão. Se o aluno ou a questão for apagado, as respostas vão junto, sem sobrar lixo. |
| `ENGINE=InnoDB` | Tipo de tabela que aceita chaves estrangeiras. |

### 1.2 Tabela `avaliacoes_modulo`

```sql
CREATE TABLE IF NOT EXISTS avaliacoes_modulo (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT NOT NULL,
  modulo_id   INT NOT NULL,
  nota        TINYINT NOT NULL,
  avaliada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_avaliacao (usuario_id, modulo_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (modulo_id)  REFERENCES modulos(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

A nota (1 a 5) fica numa tabela própria. A chave única garante **uma nota por aluno e módulo**; se o aluno mudar de ideia, a nota é atualizada, não duplicada. O `ON UPDATE CURRENT_TIMESTAMP` atualiza a data sozinho a cada mudança.

> Se o phpMyAdmin acusar *“Cannot add foreign key constraint”*, o tipo da coluna `id` das tabelas ligadas é diferente de `INT`. Apague as linhas `FOREIGN KEY` do script: o restante funciona igual.

Figura 2 — phpMyAdmin: as duas tabelas novas na lateral e a estrutura da tabela respostas

&#91;image: image.png\]

### 1.3 O texto do módulo (coluna `conteudo`)

```sql
UPDATE modulos SET conteudo = '<p>Baseado nos ensinamentos de Jesus ...</p>
<blockquote class="destaque"><strong>aqueles que apresentam as crianças ...</strong></blockquote>
<blockquote class="versiculo"><p>“Depois, trouxeram crianças a Jesus, ...”</p><cite>Mateus 19:13,14</cite></blockquote>'
WHERE ordem = 1;
```

O texto é guardado **como HTML**: parágrafos em `<p>`, citações em `<blockquote>`, ênfases em `<strong>` e `<em>`. O CSS (passo 8) já sabe estilizar cada uma dessas marcações, então escrever um módulo novo é só escrever o texto com essas marcações. O `WHERE ordem = 1` escolhe a Introdução.

| Marcação | Resultado |
| --- | --- |
| `<p>...</p>` | Parágrafo normal |
| `<h2>...</h2>` | Subtítulo dentro do texto |
| `<blockquote class="destaque">` | Frase em destaque, centralizada |
| `<blockquote class="versiculo">` + `<cite>` | Versículo com a referência embaixo |
| `<strong>` / `<em>` | Negrito / itálico |

> **Por que o PHP pode imprimir esse HTML sem `htmlspecialchars`?** Porque só o professor, pelo phpMyAdmin, escreve nessa coluna. Texto digitado por alunos (nome, por exemplo) sempre passa pelo `htmlspecialchars`.

### 1.4 O vídeo do YouTube (coluna `video_url`)

```sql
UPDATE modulos SET video_url = 'https://youtu.be/UqB2e86rPOI' WHERE ordem = 1;
```

Basta colar o link que o YouTube mostra em **Compartilhar**. A página aceita os três formatos: `youtu.be/CODIGO`, `youtube.com/watch?v=CODIGO` e `youtube.com/embed/CODIGO`. Veja como isso funciona no passo 2.

### 1.5 As 5 questões

```sql
DELETE FROM questoes WHERE tipo = 'exercicio'
  AND modulo_id = (SELECT id FROM modulos WHERE ordem = 1);

INSERT INTO questoes (modulo_id, tipo, enunciado, alt_a, alt_b, alt_c, alt_d, correta, explicacao) VALUES
((SELECT id FROM modulos WHERE ordem = 1), 'exercicio',
 'Qual é o propósito central do Ministério Infantil apresentado na apostila?',
 'Manter as crianças ocupadas durante o culto',
 'Conectar as crianças a Jesus e ensinar a Palavra',
 'Substituir a responsabilidade da família',
 'Realizar atividades recreativas sem ensino bíblico',
 'B', 'A introdução apresenta como missão conectar as crianças a Jesus; ...'),
-- ... mais 4 questões, no mesmo formato
```

| Trecho | O que faz |
| --- | --- |
| `DELETE ... WHERE tipo='exercicio' AND modulo_id = ...` | Apaga as questões antigas deste módulo antes de inserir. Assim, rodar o script de novo não duplica nada. |
| `(SELECT id FROM modulos WHERE ordem = 1)` | Descobre o `id` do módulo pela `ordem`. Não precisamos decorar números. |
| `'exercicio'` | O tipo da questão. As 10 questões da avaliação final terão o tipo `avaliacao`. |
| `'B'` | A letra da alternativa certa, **como está gravada aqui no banco**. |
| `explicacao` | O comentário do gabarito. Aparece depois que o aluno confere. |

> Se o seu campo `correta` for do tipo `ENUM` com letras minúsculas, troque `'B'` por `'b'`. O PHP compara sem diferenciar maiúsculas de minúsculas.

### 1.6 Como saber se funcionou

```sql
SELECT modulo_id, COUNT(*) FROM questoes GROUP BY modulo_id;
SELECT ordem, titulo, video_url FROM modulos WHERE ordem = 1;
```

A primeira deve mostrar **5** questões para o módulo da Introdução. A segunda deve mostrar o link do vídeo preenchido.

## Passo 2 — Três funções novas em `includes/modulos.php`

O arquivo do Módulo 3 continua igual. No final dele, acrescente as três funções abaixo. Elas ficam aqui porque várias páginas as usam (conteúdo, atividades, conferir, avaliar e concluir).

```php
// Busca UM módulo (com texto e vídeo) já com 'concluido' e 'liberado'.
// Devolve null se o id não existir.
function buscar_modulo_do_aluno(PDO $pdo, int $id, bool $eh_professor, int $usuario_id): ?array
{
    foreach (modulos_do_aluno($pdo, $usuario_id, $eh_professor) as $m) {
        if ($m['id'] === $id) {
            $st = $pdo->prepare("SELECT conteudo, video_url FROM modulos WHERE id = ?");
            $st->execute([$id]);
            return $m + $st->fetch(PDO::FETCH_ASSOC);
        }
    }
    return null;
}

// Converte um link do YouTube no endereço do player ("embed").
function youtube_embed(?string $url): string
{
    if (!$url) return '';
    if (preg_match('~(?:v=|youtu\.be/|/embed/)([A-Za-z0-9_-]{11})~', $url, $r)) {
        return 'https://www.youtube-nocookie.com/embed/' . $r[1];
    }
    return '';
}

// Quantas questões o módulo tem e quantas o aluno já respondeu
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
```

### 2.1 `buscar_modulo_do_aluno()`

| Trecho | O que faz |
| --- | --- |
| `?array` | O “?” diz que a função devolve uma lista **ou** `null`. |
| `foreach (modulos_do_aluno(...))` | **Reaproveita** a função do Módulo 3 (que já calcula `concluido` e `liberado`) em vez de repetir a regra de bloqueio. |
| `$m['id'] === $id` | Procura o módulo pedido. O `===` compara valor **e** tipo; por isso `modulos.php` converte os ids com `(int)`. |
| `SELECT conteudo, video_url` | A lista do painel não traz o texto nem o vídeo (seria pesado para 9 módulos). Aqui buscamos só do módulo aberto. |
| `$m + $st->fetch(...)` | O operador `+` **junta** duas listas: o módulo e o texto/vídeo viram uma lista só. |

### 2.2 `youtube_embed()`

| Trecho | O que faz |
| --- | --- |
| `?string $url` | Aceita texto ou `null` (módulo ainda sem vídeo). |
| `if (!$url) return ''` | Sem link, devolve vazio. A página mostra a caixa “o vídeo será carregado aqui”. |
| `preg_match('~...~', $url, $r)` | Procura o padrão no link. Os `~` são apenas delimitadores da expressão. |
| `(?:v=\|youtu\.be/\|/embed/)` | Aceita os três formatos de link. |
| `([A-Za-z0-9_-]{11})` | O código do YouTube: **exatamente 11 caracteres** de letras, números, `_` ou `-`. O parêntese guarda o código em `$r[1]`. |
| `youtube-nocookie.com/embed/` | O mesmo player, no modo de privacidade (não grava cookies de rastreio sem o aluno clicar em play). |

O link `https://youtu.be/UqB2e86rPOI` vira `https://www.youtube-nocookie.com/embed/UqB2e86rPOI`. Como a função só devolve o código de 11 caracteres validado, ninguém consegue injetar outro endereço ou código na página pelo campo `video_url`.

### 2.3 `andamento_exercicios()`

Faz duas contagens: quantas questões o módulo tem e quantas o aluno já respondeu (o `JOIN` liga cada resposta à sua questão para filtrar pelo módulo). O `concluir.php` usa esse resultado para decidir se o aluno pode concluir.

## Passo 3 — O topo das páginas: `topo_modulo.php` e `rodape_modulo.php`

As páginas de conteúdo e de atividades têm um visual próprio (imagem ao fundo, folha por cima). Por isso ganham um topo e um rodapé separados dos usados no resto do site. Cada página define algumas variáveis e depois inclui o topo.

### 3.1 O que cada página informa antes de incluir o topo

| Variável | Exemplo | Uso |
| --- | --- | --- |
| `$titulo_pagina` | `Conectando as Crianças a Jesus` | Texto da aba do navegador |
| `$imagem_fundo` | `introducao.jpg` | Arquivo em `assets/img/modulos/hero/` |
| `$capa_etiqueta` | `Introdução` | Texto pequeno dourado acima do título |
| `$capa_titulo` | `Conectando as Crianças a Jesus` | Título grande sobre a imagem |

### 3.2 `includes/topo_modulo.php`

```php
<?php
$st = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$st->execute([$_SESSION['usuario_id']]);
$nome_usuario = (string)$st->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo_pagina) ?> | <?= NOME_CURSO ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modulo.css">
</head>
<body class="pagina-modulo"
      style="--imagem: url('<?= BASE_URL ?>/assets/img/modulos/hero/<?= htmlspecialchars($imagem_fundo) ?>')">

<header class="barra-topo">
    <a class="barra-logo" href="painel.php" title="Voltar aos módulos">
        <img src="<?= BASE_URL ?>/assets/img/logoCamisaSalina.png" alt="Logo do curso">
    </a>
    <div class="barra-titulo"><?= NOME_CURSO ?></div>
    <div class="barra-usuario">
        <span class="barra-nome"><?= htmlspecialchars($nome_usuario) ?></span>
        <a class="btn-sair" href="sair.php">Sair</a>
    </div>
</header>

<section class="capa">
    <p class="capa-etiqueta"><?= htmlspecialchars($capa_etiqueta) ?></p>
    <h1 class="capa-titulo"><?= htmlspecialchars($capa_titulo) ?></h1>
</section>

<main class="folha">
```

| Trecho | O que faz |
| --- | --- |
| Consulta do nome | Busca o nome no banco pelo `usuario_id` da sessão. Não dependemos do nome de nenhuma outra chave de sessão (lição do estudo de caso do Módulo 3). |
| `<body style="--imagem: url(...)">` | Cria uma **variável CSS** chamada `--imagem` com o endereço da imagem. O CSS usa `var(--imagem)`. É assim que o PHP “passa” uma imagem diferente para o mesmo CSS. |
| `<header class="barra-topo">` | Barra superior: logo à esquerda, título no centro, usuário e **Sair** à direita. |
| `<section class="capa">` | A área da imagem, com o título escrito sobre ela. |
| `<main class="folha">` | **Abre** a folha. Quem fecha é o `rodape_modulo.php`. |

### 3.3 `includes/rodape_modulo.php`

```php
</main>

<script src="<?= BASE_URL ?>/assets/js/modulo.js"></script>
</body>
</html>
```

Fecha a folha (`</main>`), carrega o JavaScript **por último** (para a página aparecer antes) e fecha o documento. O mesmo par topo/rodapé serve para qualquer página nova com esse visual.

## Passo 4 — A página de conteúdo: `paginas/modulo.php`

Substitui a página temporária do Módulo 3. Ela mostra o vídeo, o texto vindo do banco, a avaliação com estrelas e o botão das atividades.

Figura 3 — Página de conteúdo com o vídeo do YouTube carregado

&#91;image: image.png\]

Figura 4 — Final da folha: avaliação com estrelas e botão “Ir para as atividades”

&#91;image: image.png\]

### 4.1 O código

```php
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

$usuario_id = (int)$_SESSION['usuario_id'];
$modulo = buscar_modulo_do_aluno($pdo, (int)($_GET['id'] ?? 0), $eh_professor, $usuario_id);

if (!$modulo) { header('Location: painel.php'); exit; }
if (!$modulo['liberado']) { header('Location: painel.php?erro=bloqueado'); exit; }

// Nota que o aluno já deu (0 = ainda não avaliou)
$st = $pdo->prepare("SELECT nota FROM avaliacoes_modulo WHERE usuario_id = ? AND modulo_id = ?");
$st->execute([$usuario_id, $modulo['id']]);
$nota_atual = (int)$st->fetchColumn();

$embed = youtube_embed($modulo['video_url']);

$titulo_pagina = $modulo['titulo'];
$imagem_fundo  = $modulo['imagem'] ?: 'introducao.jpg';
$capa_etiqueta = rotulo_modulo($modulo['ordem']);
$capa_titulo   = $modulo['titulo'];

require __DIR__ . '/../includes/topo_modulo.php';
?>

<a class="link-voltar" href="painel.php">← Todos os módulos</a>

<div class="video-caixa">
    <?php if ($embed): ?>
        <iframe src="<?= htmlspecialchars($embed) ?>" title="Vídeo do módulo"
                allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen></iframe>
    <?php else: ?>
        <div class="video-vazio">
            <span class="video-play">▶</span>
            <p>O vídeo deste módulo será carregado aqui</p>
        </div>
    <?php endif; ?>
</div>

<div class="texto-modulo">
    <?= $modulo['conteudo'] ?: '<p>Conteúdo em preparação.</p>' ?>
</div>

<div class="folha-fim">
    <form class="form-avaliacao" action="avaliar.php" method="post">
        <?= csrf_campo() ?>
        <input type="hidden" name="modulo_id" value="<?= $modulo['id'] ?>">
        <span class="avaliacao-titulo">Avalie este módulo</span>
        <div class="estrelas">
            <?php for ($n = 5; $n >= 1; $n--): ?>
                <input type="radio" name="nota" id="nota<?= $n ?>" value="<?= $n ?>" <?= $nota_atual === $n ? 'checked' : '' ?>>
                <label for="nota<?= $n ?>" title="<?= $n ?> estrela(s)">★</label>
            <?php endfor; ?>
        </div>
        <span class="avaliacao-msg"><?= $nota_atual ? 'Sua avaliação foi salva.' : '' ?></span>
    </form>

    <a class="btn-principal" href="atividades.php?id=<?= $modulo['id'] ?>">Ir para as atividades →</a>
</div>

<?php require __DIR__ . '/../includes/rodape_modulo.php'; ?>
```

### 4.2 Explicação: a parte de PHP

| Trecho | O que faz |
| --- | --- |
| `(int)($_GET['id'] ?? 0)` | Lê o `id` do endereço e o converte em número inteiro. Texto malicioso vira `0`. |
| `buscar_modulo_do_aluno(...)` | Devolve o módulo com `liberado` e `concluido`, ou `null`. |
| Duas travas (`!$modulo` e `!$modulo['liberado']`) | Id inexistente volta ao painel; módulo bloqueado volta ao painel **com o aviso amarelo**. Mesmo se o aluno digitar o endereço na barra. |
| `(int)$st->fetchColumn()` | Sem nota salva, `fetchColumn()` devolve `false`; o `(int)` transforma em `0`. |
| `$modulo['imagem'] ?: 'introducao.jpg'` | O `?:` usa a imagem padrão se a coluna estiver vazia. |
| `rotulo_modulo(...)` | A função do Módulo 3 que devolve “Introdução”, “Módulo 1”… |

### 4.3 Explicação: o vídeo e o texto

| Trecho | O que faz |
| --- | --- |
| `<?php if ($embed): ?> ... <?php else: ?> ... <?php endif; ?>` | Com vídeo cadastrado mostra o `<iframe>` (o player do YouTube); sem vídeo mostra a caixa escura com o ▶. Assim os módulos ainda sem vídeo não quebram a página. |
| `<iframe src="..." allowfullscreen>` | Um “quadro” com a página do YouTube dentro. `allowfullscreen` permite o botão de tela cheia. |
| `htmlspecialchars($embed)` | Cuidado extra: o endereço já é seguro, mas o hábito de proteger tudo que sai na página evita erros futuros. |
| `<?= $modulo['conteudo'] ?: ... ?>` | Imprime o HTML do banco **sem** `htmlspecialchars`, de propósito: precisamos que `<p>` e `<blockquote>` funcionem. Sem texto cadastrado, mostra “Conteúdo em preparação”. |

### 4.4 Explicação: as estrelas

São cinco botões de rádio (`type="radio"`) com o mesmo `name="nota"`, então só um fica marcado por vez. Cada `<label for="notaN">` mostra o símbolo ★ e, ao ser clicado, marca o rádio de mesmo `id`.

| Trecho | O que faz |
| --- | --- |
| `for ($n = 5; $n >= 1; $n--)` | Escreve as estrelas **de 5 para 1**. O motivo está no passo 8: o CSS inverte a linha para o efeito de pintar as estrelas anteriores. |
| `<?= $nota_atual === $n ? 'checked' : '' ?>` | Se o aluno já avaliou, a estrela da nota dele já vem marcada. |
| `<?= csrf_campo() ?>` | O campo escondido de segurança (token CSRF) de sempre. O JavaScript o envia junto com a nota. |
| `action="avaliar.php"` | Quem recebe a nota. Quem envia é o JavaScript do passo 7, sem recarregar a página. |

## Passo 5 — A página de atividades e o embaralhamento: `paginas/atividades.php`

Mostra as 5 questões. A cada abertura da página, a **ordem das questões** e a **ordem das alternativas** são sorteadas, para o aluno não decorar posições.

Figura 5 — Página de atividades antes de conferir: questões com as alternativas e o botão “Conferir resposta”

&#91;image: image.png\]

Figura 6 — Questões já conferidas: alternativa certa em verde, errada em vermelho e o comentário

&#91;image: image.png\]

### 5.1 O código (parte de PHP)

```php
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

$usuario_id = (int)$_SESSION['usuario_id'];
$modulo = buscar_modulo_do_aluno($pdo, (int)($_GET['id'] ?? 0), $eh_professor, $usuario_id);

if (!$modulo) { header('Location: painel.php'); exit; }
if (!$modulo['liberado']) { header('Location: painel.php?erro=bloqueado'); exit; }

// 1) As questões. Repare: NÃO buscamos a coluna "correta" aqui.
$st = $pdo->prepare("SELECT id, enunciado, alt_a, alt_b, alt_c, alt_d
                       FROM questoes
                      WHERE modulo_id = ? AND tipo = 'exercicio'
                      ORDER BY id");
$st->execute([$modulo['id']]);
$questoes = $st->fetchAll(PDO::FETCH_ASSOC);

// EMBARALHAR: a cada vez que a página abre, as questões mudam de ordem
shuffle($questoes);

// 2) O que o aluno já respondeu (para reabrir a página já corrigida)
$st = $pdo->prepare("SELECT q.id, r.escolhida, r.acertou, q.correta, q.explicacao
                       FROM respostas r
                       JOIN questoes q ON q.id = r.questao_id
                      WHERE r.usuario_id = ? AND q.modulo_id = ? AND q.tipo = 'exercicio'");
$st->execute([$usuario_id, $modulo['id']]);
$respondidas = [];
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { $respondidas[(int)$r['id']] = $r; }

$total   = count($questoes);
$feitas  = count($respondidas);
$tudo_ok = ($total > 0 && $feitas >= $total);
```

| Trecho | O que faz |
| --- | --- |
| ` SELECT id, enunciado, alt_a ...  ` (sem `correta`) | **A resposta certa nunca vai para o navegador** antes de o aluno conferir. Quem abrir “Exibir código-fonte” não encontra o gabarito. |
| `shuffle($questoes)` | Embaralha a lista de questões **no lugar**. Cada vez que o PHP roda, o sorteio é novo. |
| Segunda consulta com `JOIN` | Traz as respostas já dadas **e** a letra certa + comentário, mas **só das questões já respondidas**. As não respondidas continuam sem gabarito. |
| `$respondidas[(int)$r['id']] = $r` | Transforma a lista numa tabela de busca: dado o id da questão, acha a resposta. |
| `$tudo_ok` | Verdadeiro quando todas as questões foram conferidas. Libera o botão Concluir. |

### 5.2 O código (cada questão)

```php
<?php foreach ($questoes as $i => $q):
    $resp = $respondidas[(int)$q['id']] ?? null;   // null = ainda não respondeu

    $colunas = ['A' => 'alt_a', 'B' => 'alt_b', 'C' => 'alt_c', 'D' => 'alt_d'];
    $ordem   = array_keys($colunas);   // ['A','B','C','D']
    shuffle($ordem);                    // por exemplo ['C','A','D','B']
?>
    <form class="questao <?= $resp ? 'respondida' : '' ?>" action="conferir.php" method="post">
        <?= csrf_campo() ?>
        <input type="hidden" name="questao_id" value="<?= (int)$q['id'] ?>">

        <p class="enunciado"><span class="numero"><?= $i + 1 ?></span> <?= htmlspecialchars($q['enunciado']) ?></p>

        <?php foreach ($ordem as $posicao => $letra):
            $letra_tela = chr(65 + $posicao);          // 0->A, 1->B, 2->C, 3->D
            $classe = '';
            if ($resp) {
                if ($letra === strtoupper(trim($resp['correta']))) $classe = 'alt-certa';
                elseif ($letra === $resp['escolhida'])             $classe = 'alt-errada';
            }
        ?>
            <label class="alt <?= $classe ?>">
                <input type="radio" name="resposta" value="<?= $letra ?>"
                       <?= $resp && $resp['escolhida'] === $letra ? 'checked' : '' ?>
                       <?= $resp ? 'disabled' : '' ?>>
                <span class="letra"><?= $letra_tela ?></span>
                <span><?= htmlspecialchars($q[$colunas[$letra]]) ?></span>
            </label>
        <?php endforeach; ?>

        <button type="submit" class="btn-conferir" <?= $resp ? 'hidden' : '' ?>>Conferir resposta</button>

        <div class="feedback <?= $resp ? ($resp['acertou'] ? 'certo' : 'errado') : '' ?>" <?= $resp ? '' : 'hidden' ?>>
            <?php if ($resp):
                $letra_certa_tela = chr(65 + array_search(strtoupper(trim($resp['correta'])), $ordem, true));
            ?>
                <strong><?= $resp['acertou'] ? '✓ Resposta correta!' : '✗ Não foi dessa vez. A resposta certa é a letra ' . $letra_certa_tela . '.' ?></strong>
                <p><?= htmlspecialchars($resp['explicacao'] ?? '') ?></p>
            <?php endif; ?>
        </div>
    </form>
<?php endforeach; ?>
```

### 5.3 A ideia central do embaralhamento

Existem **duas letras** para cada alternativa:

| Letra | Onde vive | Muda a cada abertura? | Para que serve |
| --- | --- | --- | --- |
| **Letra original** (`$letra`) | No banco (`alt_a`, `alt_b`… e a coluna `correta`) | Não | Identifica a alternativa. É o `value` enviado ao servidor. |
| **Letra de exibição** (`$letra_tela`) | Só na tela, pela posição | Sim | É o que o aluno lê: a 1ª alternativa mostrada é sempre “A”, a 2ª “B”… |

Exemplo: o banco diz que a certa é a `B`. Num sorteio, a ordem fica `['C','A','D','B']`; a alternativa `B` aparece em **4º lugar**, com o rótulo **D** na tela. O aluno marca “D”, o navegador envia `B`, o servidor compara `B` com `B` e confere certinho.

| Trecho | O que faz |
| --- | --- |
| `array_keys($colunas)` | Pega só as chaves: `['A','B','C','D']`. |
| `shuffle($ordem)` | Embaralha essas letras. |
| `foreach ($ordem as $posicao => $letra)` | Percorre na ordem sorteada. `$posicao` vai de 0 a 3. |
| `chr(65 + $posicao)` | Converte o número em letra: `chr(65)` = A, `chr(66)` = B… É a letra de exibição. |
| `$q[$colunas[$letra]]` | Do `$letra` (`'B'`) acha a coluna (`'alt_b'`) e depois o texto naquela coluna. |
| `value="<?= $letra ?>"` | Envia a letra **original**, não a da tela. |
| `array_search(..., $ordem, true)` | Descobre em que posição ficou a alternativa certa nesta abertura, para escrever “a resposta certa é a letra D” com a letra que o aluno enxerga. |
| `<?= $resp ? 'disabled' : '' ?>` | Questão já respondida: os rádios ficam travados. |
| `<?= $resp ? 'hidden' : '' ?>` | Questão já respondida: esconde o botão “Conferir resposta”. |

> **Ao escrever questões novas**, evite alternativas como “Todas as anteriores” ou “Nenhuma das alternativas”. Com as opções embaralhadas, elas perdem o sentido. Também não cite letras no comentário (“a letra B está certa porque…”), já que a letra muda.

### 5.4 O código (final da página)

```php
<div class="folha-fim">
    <a class="btn-secundario" href="modulo.php?id=<?= $modulo['id'] ?>">← Voltar ao conteúdo</a>

    <div class="concluir-area">
        <span class="contador">Questões conferidas: <strong id="contador"><?= $feitas ?> de <?= $total ?></strong></span>
        <form action="concluir.php" method="post">
            <?= csrf_campo() ?>
            <input type="hidden" name="modulo_id" value="<?= $modulo['id'] ?>">
            <button type="submit" id="btn-concluir" class="btn-principal" <?= $tudo_ok || $total === 0 ? '' : 'disabled' ?>>
                Concluir módulo ✓
            </button>
        </form>
    </div>
</div>
```

O botão **Concluir módulo** nasce desabilitado (`disabled`) e só é liberado quando todas as questões foram conferidas. O contador mostra o andamento (“3 de 5”). **O `disabled` é só conforto visual:** a regra de verdade está no `concluir.php`, como mostra o próximo passo.

## Passo 6 — Quem decide é o servidor: `conferir.php`, `avaliar.php` e `concluir.php`

O navegador do aluno **não é confiável**: ele pode ser alterado por quem sabe usar as ferramentas do navegador. Por isso, cada decisão importante (a resposta está certa? o aluno pode concluir?) é tomada em PHP.

Os dois primeiros arquivos respondem em **JSON** (um formato de texto que o JavaScript entende), porque quem os chama é o JavaScript, sem recarregar a página. O `concluir.php` é um envio comum de formulário.

### 6.1 `paginas/conferir.php`

```php
<?php
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

$st = $pdo->prepare("SELECT modulo_id, correta, explicacao FROM questoes WHERE id = ? AND tipo = 'exercicio'");
$st->execute([$questao_id]);
$q = $st->fetch(PDO::FETCH_ASSOC);
if (!$q) responder(['ok' => false, 'erro' => 'Questão não encontrada.'], 404);

$modulo = buscar_modulo_do_aluno($pdo, (int)$q['modulo_id'], $eh_professor, $usuario_id);
if (!$modulo || !$modulo['liberado']) responder(['ok' => false, 'erro' => 'Módulo bloqueado.'], 403);

$certa = strtoupper(trim($q['correta']));

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
```

| Trecho | O que faz |
| --- | --- |
| `header('Content-Type: application/json')` | Avisa ao navegador que a resposta é JSON, não uma página. |
| `function responder(...)` | Atalho: define o código HTTP, imprime o JSON e `exit`. Evita repetir essas três linhas. |
| `!csrf_ok()` | Recusa pedidos que não vieram dos formulários do site (token de segurança). Código **400** = pedido inválido. |
| `in_array($escolhida, ['A','B','C','D'], true)` | Só aceita uma das quatro letras. Qualquer outra coisa recebe **422**. |
| `WHERE id = ? AND tipo = 'exercicio'` | Só aceita questões de exercício. A avaliação final terá sua própria página. |
| `buscar_modulo_do_aluno(...)` + `liberado` | **A trava de novo:** um aluno não consegue conferir questão de módulo bloqueado, nem mandando o pedido à mão. Código **403** = proibido. |
| `SELECT ... FROM respostas` (bloco `$ja`) | **Cada questão vale uma vez.** Se já foi respondida, devolvemos a resposta que ficou gravada e **não** gravamos de novo. Sem isso, o aluno poderia testar as quatro letras até acertar. |
| `$acertou = ($escolhida === $certa)` | A comparação central, feita com as letras originais do banco. |
| `INSERT INTO respostas` | Grava a resposta (com `?` e `execute`, como sempre). |
| `JSON_UNESCAPED_UNICODE` | Mantém os acentos legíveis no JSON. |

### 6.2 `paginas/avaliar.php`

Tem a mesma estrutura do anterior (mesma `responder()`, mesma verificação de CSRF e de módulo liberado). A parte que muda:

```php
$nota = (int)($_POST['nota'] ?? 0);
if ($nota < 1 || $nota > 5) responder(['ok' => false, 'erro' => 'Nota inválida.'], 422);

$st = $pdo->prepare("INSERT INTO avaliacoes_modulo (usuario_id, modulo_id, nota) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE nota = VALUES(nota)");
$st->execute([$usuario_id, $modulo_id, $nota]);

responder(['ok' => true]);
```

| Trecho | O que faz |
| --- | --- |
| `$nota < 1 \|\| $nota > 5` | Rejeita qualquer valor fora de 1 a 5. |
| `ON DUPLICATE KEY UPDATE nota = VALUES(nota)` | “Se já existe uma linha para este aluno e este módulo (a chave única da tabela), **atualize a nota** em vez de dar erro.” Um comando só serve para a primeira avaliação e para as mudanças de opinião. |

### 6.3 `paginas/concluir.php`

```php
<?php
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

// Só conclui quem conferiu TODAS as questões
$and = andamento_exercicios($pdo, $modulo_id, $usuario_id);
if ($and['respondidas'] < $and['total']) {
    header('Location: atividades.php?id=' . $modulo_id . '&erro=faltam');
    exit;
}

$st = $pdo->prepare("INSERT IGNORE INTO progresso (usuario_id, modulo_id) VALUES (?, ?)");
$st->execute([$usuario_id, $modulo_id]);

header('Location: painel.php?concluido=1');
exit;
```

| Trecho | O que faz |
| --- | --- |
| Verificação do método e do CSRF | Quem abre `concluir.php` digitando o endereço (método GET) volta ao painel. Só vale o envio do botão. |
| `andamento_exercicios(...)` | A **verdadeira trava** do botão Concluir: se faltam questões conferidas, volta às atividades com o aviso amarelo. |
| `INSERT IGNORE INTO progresso` | Grava a conclusão. O `IGNORE` evita erro (e duplicata) se o aluno clicar duas vezes ou concluir um módulo que já concluiu antes. |
| `header('Location: painel.php?concluido=1')` | Volta ao painel. O módulo seguinte já aparece liberado, porque o painel calcula isso a partir da tabela `progresso`. |

No `painel.php`, um bloco novo mostra a faixa verde quando existe `?concluido=1`:

```php
<?php if (($_GET['concluido'] ?? '') === '1'): ?>
    <p class="painel-ok">Módulo concluído! O próximo módulo foi liberado.</p>
<?php endif; ?>
```

Figura 7 — Painel depois de concluir a Introdução: faixa verde, Introdução “Concluído” e Módulo 1 liberado

&#91;image: image.png\]

## Passo 7 — O JavaScript: `assets/js/modulo.js`

Sem JavaScript, cada clique em “Conferir resposta” recarregaria a página inteira (e o embaralhamento mudaria tudo de lugar no meio da atividade!). Com `fetch`, o navegador envia a resposta **em segundo plano**, recebe o JSON do `conferir.php` e só pinta a questão. Crie a pasta `assets/js/` se ela não existir.

```javascript
document.addEventListener('DOMContentLoaded', function () {

    // ---------- 1) AVALIAÇÃO COM ESTRELAS (página de conteúdo) ----------
    var formAv = document.querySelector('.form-avaliacao');
    if (formAv) {
        formAv.addEventListener('change', function () {
            var msg = formAv.querySelector('.avaliacao-msg');
            fetch(formAv.action, { method: 'POST', body: new FormData(formAv) })
                .then(function (r) { return r.json(); })
                .then(function (d) { msg.textContent = d.ok ? 'Obrigado pela avaliação!' : (d.erro || 'Não foi possível salvar.'); })
                .catch(function () { msg.textContent = 'Sem conexão. Tente de novo.'; });
        });
    }

    // ---------- 2) CONFERIR RESPOSTA (página de atividades) ----------
    var forms = document.querySelectorAll('form.questao');
    var btnConcluir = document.getElementById('btn-concluir');
    var contador = document.getElementById('contador');

    function atualizarContador() {
        if (!contador) return;
        var feitas = document.querySelectorAll('form.questao.respondida').length;
        contador.textContent = feitas + ' de ' + forms.length;
        if (btnConcluir && feitas >= forms.length) btnConcluir.disabled = false;
    }

    function mostrarResultado(form, d) {
        form.classList.add('respondida');
        form.querySelectorAll('input[name="resposta"]').forEach(function (i) { i.disabled = true; });
        form.querySelectorAll('label.alt').forEach(function (l) {
            var v = l.querySelector('input').value;
            if (v === d.certa) l.classList.add('alt-certa');
            else if (v === d.escolhida) l.classList.add('alt-errada');
        });
        form.querySelector('.btn-conferir').hidden = true;

        var fb = form.querySelector('.feedback');
        fb.className = 'feedback ' + (d.acertou ? 'certo' : 'errado');
        fb.hidden = false;
        fb.innerHTML = '';
        var titulo = document.createElement('strong');
        var rotuloCerto = form.querySelector('input[value="' + d.certa + '"]').parentNode.querySelector('.letra');
        titulo.textContent = d.acertou ? '✓ Resposta correta!'
            : '✗ Não foi dessa vez. A resposta certa é a letra ' + rotuloCerto.textContent + '.';
        var texto = document.createElement('p');
        texto.textContent = d.explicacao || '';
        fb.appendChild(titulo);
        fb.appendChild(texto);
    }

    function aviso(form, texto) {
        var fb = form.querySelector('.feedback');
        fb.className = 'feedback aviso';
        fb.hidden = false;
        fb.textContent = texto;
    }

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!form.querySelector('input[name="resposta"]:checked')) {
                aviso(form, 'Escolha uma alternativa antes de conferir.');
                return;
            }
            var botao = form.querySelector('.btn-conferir');
            botao.disabled = true;
            fetch(form.action, { method: 'POST', body: new FormData(form) })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.ok) { mostrarResultado(form, d); atualizarContador(); }
                    else { aviso(form, d.erro || 'Não foi possível conferir.'); botao.disabled = false; }
                })
                .catch(function () { aviso(form, 'Sem conexão. Tente de novo.'); botao.disabled = false; });
        });
    });
});
```

### 7.1 Explicação: as estrelas

| Trecho | O que faz |
| --- | --- |
| `DOMContentLoaded` | Só executa depois que a página terminou de carregar. |
| `document.querySelector('.form-avaliacao')` | Procura o formulário das estrelas. Se não existe (estamos na página de atividades), o `if` impede erro. **O mesmo arquivo serve para as duas páginas.** |
| `addEventListener('change', ...)` | Dispara quando o aluno marca uma estrela. |
| `new FormData(formAv)` | Empacota todos os campos do formulário (nota, id do módulo e **o token CSRF**) sem precisarmos conhecer o nome de cada campo. |
| `fetch(url, {method:'POST', body})` | Envia o pedido ao PHP sem recarregar a página. |
| `.then(r => r.json())` | Quando a resposta chega, converte o JSON em objeto. |
| `.catch(...)` | Se faltar internet, mostra aviso em vez de falhar em silêncio. |

### 7.2 Explicação: conferir a resposta

| Trecho | O que faz |
| --- | --- |
| `e.preventDefault()` | Cancela o envio normal do formulário (que recarregaria a página). |
| `:checked` | Confere se alguma alternativa foi marcada. Se não, mostra aviso amarelo e não envia nada. |
| `botao.disabled = true` | Impede duplo clique enquanto a resposta viaja. Se der erro, o botão volta a funcionar. |
| `mostrarResultado(form, d)` | Recebe o JSON do servidor (`d`) e pinta a questão. |
| `v === d.certa` / `v === d.escolhida` | Compara o `value` de cada alternativa (a letra **original**) com a letra certa e com a escolhida; verde para a certa, vermelho para a errada. |
| `form.querySelector('input[value="..."]').parentNode.querySelector('.letra')` | Acha o rótulo (letra de **exibição**) da alternativa certa para escrever “a resposta certa é a letra C” com a letra que o aluno enxerga. |
| `document.createElement` + `textContent` | Monta o comentário como **texto puro**, nunca como HTML. Assim, um comentário com `<` ou `>` não quebra nem injeta código. |
| `atualizarContador()` | Atualiza “3 de 5” e, quando chega ao total, **habilita o botão Concluir**. |

## Passo 8 — O visual: `assets/css/modulo.css`

O arquivo completo tem cerca de 230 linhas e está no zip. Aqui estão as três ideias que dão o efeito pedido: **imagem fixa atrás**, **folha por cima** e **estrelas só com CSS**.

### 8.1 Cores e largura em um só lugar

```css
:root {
    --escuro: #0f1b2d;        /* barra superior e faixa do final da folha */
    --folha: #fbf8f2;         /* cor da folha */
    --dourado: #f2b632;       /* botões principais */
    --texto: #2d2a26;
    --suave: #6b665e;
    --certo: #2b8a3e;
    --errado: #c92a2a;
    --largura-folha: 860px;
}
```

Para mudar as cores do curso inteiro, ou deixar a folha mais larga, altere só este bloco.

### 8.2 A imagem fixa ao fundo

```css
body.pagina-modulo {
    background-color: var(--escuro);
    background-image: linear-gradient(rgba(10, 20, 35, .30), rgba(10, 20, 35, .55)), var(--imagem);
    background-size: cover;
    background-position: center top;
    background-attachment: fixed;
}
```

| Trecho | O que faz |
| --- | --- |
| `var(--imagem)` | A imagem escolhida pelo PHP no `topo_modulo.php` (cada módulo tem a sua). |
| `linear-gradient(rgba(...), rgba(...))` | Uma **camada escura translúcida** por cima da foto, para o título branco ficar legível. As duas camadas ficam separadas por vírgula; a primeira fica por cima. |
| `background-size: cover` | A imagem cobre a tela toda, sem deformar. |
| `background-attachment: fixed` | **O truque:** a imagem não rola com a página. A folha rola e a foto fica parada atrás. |

### 8.3 A folha por cima da imagem

```css
.capa {
    min-height: 36vh;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    width: min(var(--largura-folha), 92%);
    margin: 0 auto;
    padding: 40px 0 78px;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0, 0, 0, .55);
}

.folha {
    width: min(var(--largura-folha), 92%);
    margin: -44px auto 56px;
    padding: 38px 48px 0;
    background: var(--folha);
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, .35);
}

.folha-fim {
    display: flex;
    justify-content: space-between;
    margin: 34px -48px 0;
    padding: 22px 48px;
    background: var(--escuro);
    border-radius: 0 0 18px 18px;
}
```

| Trecho | O que faz |
| --- | --- |
| `.capa` com `min-height: 36vh` | Reserva um espaço em branco (36% da altura da tela) onde a imagem aparece e o título fica sobre ela. |
| `justify-content: flex-end` | Empurra o título para a base da capa, perto da folha. |
| `width: min(860px, 92%)` | A folha tem **no máximo 860 px**, mas em telas pequenas ocupa 92%. É isso que deixa a imagem aparecendo dos dois lados no computador. |
| `margin: -44px auto 56px` | **`-44px` no topo faz a folha subir sobre a imagem.** O `auto` nas laterais centraliza; `56px` embaixo deixa um pedaço de imagem visível abaixo da folha. |
| `box-shadow` | Sombra: dá a sensação de que a folha está flutuando sobre a foto. |
| `margin: 34px -48px 0` (faixa final) | A margem **negativa** nas laterais iguala o `padding` da folha. A faixa escura ocupa a largura inteira, encostando nas bordas. |
| `border-radius: 0 0 18px 18px` | Arredonda só os cantos de baixo, acompanhando a folha. |

### 8.4 As estrelas só com CSS

```css
.estrelas { display: inline-flex; flex-direction: row-reverse; justify-content: flex-end; }
.estrelas input { position: absolute; opacity: 0; pointer-events: none; }
.estrelas label { font-size: 1.9rem; color: #4a5870; cursor: pointer; }
.estrelas input:checked ~ label,
.estrelas label:hover,
.estrelas label:hover ~ label { color: var(--dourado); }
```

O problema: o CSS só consegue pintar elementos que vêm **depois** do selecionado (`~` significa “irmãos seguintes”). Para pintar “a estrela marcada e todas as anteriores”, o HTML escreve as estrelas **de 5 até 1** (por isso o `for ($n = 5; $n >= 1; $n--)` do passo 4) e o CSS usa `flex-direction: row-reverse` para exibí-las **de 1 até 5** na tela. Assim, “as seguintes no código” são “as anteriores na tela”.

| Trecho | O que faz |
| --- | --- |
| `opacity: 0` no `input` | Esconde o botão redondo do rádio. Quem aparece é o ★ do `label`. |
| `input:checked ~ label` | Pinta de dourado as estrelas que vêm depois do rádio marcado. |
| `label:hover ~ label` | Ao passar o mouse, pinta a estrela e todas as anteriores (na tela). |

### 8.5 As alternativas das questões

```css
.alt {
    display: flex; align-items: flex-start; gap: 12px;
    margin-bottom: 8px; padding: 12px 14px;
    background: #fff; border: 2px solid #e6dfd0; border-radius: 12px;
    cursor: pointer;
}
.alt:hover { border-color: var(--dourado); }
.alt-certa  { border-color: var(--certo);  background: #e8f5e9; }
.alt-errada { border-color: var(--errado); background: #fdecea; }
```

Cada alternativa é um `<label>` com borda: clicar em qualquer ponto dela marca o rádio. As classes `alt-certa` e `alt-errada` são colocadas pelo PHP (ao reabrir a página) ou pelo JavaScript (ao conferir). O CSS só define como cada uma fica.

### 8.6 Celular

```css
@media (max-width: 700px) {
    body.pagina-modulo { background-attachment: scroll; }
    .barra-topo { grid-template-columns: auto 1fr auto; padding: 8px 14px; }
    .barra-nome { display: none; }
    .folha { padding: 26px 22px 0; }
    .folha-fim { margin: 28px -22px 0; padding: 20px 22px; }
}
```

Em celulares, `background-attachment: fixed` costuma falhar; por isso a imagem volta a rolar normalmente. O nome do aluno some da barra para sobrar espaço, e os recuos da folha diminuem (a margem negativa da faixa final acompanha: −22 px).

## Passo 9 — Ligar tudo e testar

### 9.1 Imagens de fundo

Copie a pasta `assets/img/modulos/hero/` do zip para o projeto. São 9 imagens maiores (até 1400 px de largura), uma por card, com o mesmo nome do arquivo usado no card (`introducao.jpg`, `modulo1.jpg`…). As imagens do card (800 px, no passo 3 do Módulo 3) continuam em `assets/img/modulos/`. As duas pastas existem porque a foto do card pode ser menor e mais leve do que a de fundo.

### 9.2 Título do curso na barra superior

No `config/config.php`, ajuste:

```php
define('NOME_CURSO', 'Preparando Líderes para o Ministério Infantil');
```

### 9.3 Roteiro de testes

| Nº | Teste | Resultado esperado |
| --- | --- | --- |
| 1 | Entre como aluno e clique em **Iniciar módulo** na Introdução | Abre a página de conteúdo com a imagem ao fundo e a folha por cima. |
| 2 | Confira o vídeo | O player do YouTube aparece e toca. Sem vídeo cadastrado, aparece a caixa escura com ▶. |
| 3 | Role até o final | A faixa escura com as estrelas e o botão **Ir para as atividades**. |
| 4 | Clique em uma estrela | Aparece “Obrigado pela avaliação!”. Atualize a página: a estrela continua marcada. |
| 5 | Clique em **Ir para as atividades** | Abre as 5 questões. **Concluir módulo** está desabilitado; contador “0 de 5”. |
| 6 | Clique em **Conferir resposta** sem marcar nada | Aviso amarelo “Escolha uma alternativa…”. |
| 7 | Marque uma alternativa e confira | Certa em verde ou errada em vermelho (com a certa em verde) e o comentário. Contador sobe. |
| 8 | Atualize a página (F5) | **Questões e alternativas trocam de lugar.** As já conferidas continuam coloridas. |
| 9 | Confira as 5 | Contador “5 de 5” e o botão **Concluir módulo** habilita. |
| 10 | Clique em **Concluir módulo** | Volta ao painel com a faixa verde; Introdução “Concluído” e Módulo 1 liberado. |
| 11 | Tente abrir `modulo.php?id=` de um módulo bloqueado | Volta ao painel com o aviso amarelo. |
| 12 | Entre como professor | Todos os módulos abrem. |

### 9.4 Refazer o teste do zero

Para testar de novo com o mesmo aluno, limpe o que ele fez (troque `5` pelo `id` dele na tabela `usuarios`):

```sql
DELETE FROM respostas WHERE usuario_id = 5;
DELETE FROM progresso WHERE usuario_id = 5;
DELETE FROM avaliacoes_modulo WHERE usuario_id = 5;
```

> Sempre com `WHERE`. Sem ele, o comando apaga os dados de **todos** os alunos.

## Estudos de caso: decisões que evitam problemas

Neste módulo não houve um erro de execução para consertar. Em vez disso, dois problemas **foram evitados por decisões de projeto**. São o tipo de falha que não aparece nos testes comuns e só se revela depois, com alunos de verdade usando o curso. Por isso valem como estudos de caso.

### Caso 1 — Como embaralhar sem estragar a correção

**O pedido.** “As questões e as alternativas devem abrir de forma aleatória, para o aluno não guardar a posição.”

**A armadilha.** A solução mais curta seria embaralhar as quatro alternativas e mostrar as letras na ordem do banco. Nesse caso, a tela poderia mostrar as opções **C, A, D, B** de cima para baixo. O aluno decorava “a primeira opção” e perdia o sentido do exercício, e o comentário “a resposta certa é a letra B” apontaria para a **terceira** linha da tela. Um caos.

**A causa do problema.** A letra tinha dois papéis ao mesmo tempo: **identificar** a alternativa (no banco) e **numerar** a posição (na tela). Quando a ordem muda, os dois papéis entram em conflito.

**A decisão.** Separar os dois papéis:

| Papel | Letra | Quem usa |
| --- | --- | --- |
| Identificar a alternativa | **Original** (`A` a `D`, do banco) | O `value` do rádio, o servidor e a coluna `correta` |
| Numerar a posição | **De exibição** (`chr(65 + $posicao)`) | Só o texto que o aluno lê |

O servidor só enxerga letras originais, então a correção não muda com o sorteio. Quando precisa citar a letra para o aluno (“a certa é a D”), o PHP e o JavaScript **traduzem** a letra original para a letra de exibição daquela abertura.

**Como testar.** Abra as atividades várias vezes: a ordem das questões e das alternativas muda sempre. Marque a primeira alternativa mostrada, confira, e veja se a que ficou verde é mesmo a certa e se a letra citada no aviso bate com a tela.

**A lição.**

- Quando um dado tem dois significados, separe-os em dois dados.
- Embaralhar é só apresentação: a regra de negócio (qual é a certa) nunca depende da ordem na tela.
- Texto de questão e de comentário não deve citar letras nem dizer “todas as anteriores”.

### Caso 2 — O botão desabilitado não é segurança

**O pedido.** “O botão Concluir módulo só pode funcionar depois que o aluno conferir as atividades.”

**A armadilha.** A primeira ideia é deixar o botão `disabled` até as 5 questões serem conferidas, e só isso. Funciona na tela. Mas o HTML pertence ao navegador do aluno: com as ferramentas do navegador (F12) qualquer pessoa remove o `disabled` com dois cliques, ou envia o formulário direto para o servidor. O módulo seria concluído sem nenhuma atividade feita, e o próximo seria liberado.

**A decisão.** O `disabled` ficou como **conforto visual**, e a regra verdadeira foi para o servidor:

```php
$and = andamento_exercicios($pdo, $modulo_id, $usuario_id);
if ($and['respondidas'] < $and['total']) {
    header('Location: atividades.php?id=' . $modulo_id . '&erro=faltam');
    exit;
}
```

O `concluir.php` conta, no banco, quantas questões o aluno realmente respondeu. Não importa o que o navegador enviou: sem as 5 respostas gravadas, volta para as atividades com o aviso amarelo.

**O mesmo princípio aparece em outros pontos do módulo:**

| Onde | O que o navegador não decide | Quem decide |
| --- | --- | --- |
| `conferir.php` | Se a resposta está certa | O servidor compara com a coluna `correta` |
| `conferir.php` | Se o aluno pode responder de novo | A chave única da tabela `respostas` e a consulta `$ja` |
| `atividades.php` | Qual é a resposta certa | A coluna `correta` nem é enviada à página antes de o aluno conferir |
| `modulo.php`, `atividades.php`, `conferir.php`, `avaliar.php`, `concluir.php` | Se o módulo está liberado | A função `buscar_modulo_do_aluno()` |

**Como testar.** Na página de atividades, com 2 questões conferidas, habilite o botão pelas ferramentas do navegador e clique. O servidor deve devolver para as atividades com o aviso “Confira todas as questões antes de concluir o módulo”.

**A lição.**

- Tudo que está na tela pode ser alterado por quem a vê. Regra importante se confere **no servidor**.
- Desabilitar um botão ajuda o aluno honesto; a verificação no PHP protege o curso.
- Perguntar sempre: “se alguém mandasse este pedido à mão, o que aconteceria?”.

## Erros comuns e checklist final

### Erros comuns deste módulo

| Sintoma | Causa provável | Como resolver |
| --- | --- | --- |
| `Table 'respostas' doesn't exist` | O `modulo4.sql` não foi executado | Rodar o script no phpMyAdmin (passo 1) |
| `Cannot add foreign key constraint` | Tipo de `id` diferente nas tabelas ligadas | Apagar as linhas `FOREIGN KEY` do script |
| `Unknown column 'conteudo'` ou `video_url` | O banco do Módulo 1 foi criado com outros nomes | Conferir a estrutura da tabela `modulos` e ajustar o nome nos arquivos |
| Página sem imagem de fundo | Pasta `hero/` não copiada, ou nome do arquivo diferente da coluna `imagem` | Conferir `assets/img/modulos/hero/` |
| Página sem formatação | `modulo.css` não copiado, ou cache do navegador | Conferir `assets/css/modulo.css` e usar **Ctrl + F5** |
| Caixa “o vídeo será carregado aqui” mesmo com link | A coluna `video_url` está vazia ou o link não tem o código de 11 caracteres | Rodar de novo o `UPDATE` do passo 1.4 |
| O player do YouTube mostra erro | O dono do vídeo bloqueou a incorporação, ou o vídeo está privado | No YouTube, em Compartilhar → Incorporar, confira se está liberado |
| “Conferir resposta” recarrega a página | `modulo.js` não carregou | Confira `assets/js/modulo.js` e abra o console do navegador (F12) para ver o erro |
| “Pedido inválido. Recarregue a página.” | Falha na verificação CSRF | Recarregue com Ctrl + F5; confira se `<?= csrf_campo() ?>` está em cada formulário |
| Todas as respostas aparecem como erradas | A letra em `correta` está diferente do padrão (por exemplo, com espaço) | `SELECT id, correta FROM questoes;` e corrija |
| O botão Concluir não habilita | Uma questão não foi conferida ou o JavaScript falhou | Confira o contador “X de 5” e o console (F12) |
| Cache da página antiga após alterar o JavaScript | O navegador guardou o arquivo anterior | Ctrl + F5 |

### Checklist final

Confira cada item antes de passar para o próximo módulo:

- [ ] As tabelas `respostas` e `avaliacoes_modulo` existem no banco.
- [ ] A Introdução tem texto (`conteudo`), vídeo (`video_url`) e 5 questões.
- [ ] As três funções novas estão no final do `includes/modulos.php`.
- [ ] `topo_modulo.php` e `rodape_modulo.php` estão em `includes/`.
- [ ] `modulo.php`, `atividades.php`, `conferir.php`, `avaliar.php` e `concluir.php` estão em `paginas/`.
- [ ] `modulo.js` está em `assets/js/` e `modulo.css` em `assets/css/`.
- [ ] A pasta `assets/img/modulos/hero/` foi copiada.
- [ ] O título do curso aparece no centro da barra superior.
- [ ] A imagem fica fixa ao fundo e a folha sobe por cima dela.
- [ ] O vídeo do YouTube aparece e toca dentro da página.
- [ ] A avaliação com estrelas é salva e continua marcada ao atualizar.
- [ ] Cada vez que a página de atividades abre, questões e alternativas trocam de lugar.
- [ ] Conferir mostra certa em verde, errada em vermelho e o comentário.
- [ ] Uma questão conferida não pode ser respondida de novo.
- [ ] Concluir só funciona com as 5 conferidas, mesmo se o botão for habilitado à força.
- [ ] Após concluir, o painel libera o Módulo 1.
- [ ] Comparei as telas do meu projeto com as figuras 1 a 7.

### Próximo módulo

Com a Introdução completa, o modelo está pronto: cada novo módulo do curso é só **cadastrar dados** (texto em HTML, link do vídeo e 5 questões) no banco, sem escrever PHP novo. O próximo módulo da apostila cadastra o conteúdo do Módulo 1 em diante e depois constrói a **avaliação final** (10 questões, média 7, duas chances) e o **certificado**.
