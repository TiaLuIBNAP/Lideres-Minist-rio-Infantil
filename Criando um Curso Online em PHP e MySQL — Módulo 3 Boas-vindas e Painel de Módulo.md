# Criando um Curso Online em PHP e MySQL — Módulo 3: Boas-vindas e Painel de Módulos

Nov 24, 2026 · @Tia Lu

## Apresentação do módulo

Neste módulo o aluno ganha duas telas novas: a **tela de boas-vindas** e o **painel com os 9 cards dos módulos**, com bloqueio automático. Ao final, quem entra como aluno vê só a Introdução liberada; o professor vê tudo liberado.

### O que você vai aprender

- Criar uma página de apresentação com **imagem à esquerda e texto à direita**, usando fontes do Google Fonts.
- Acrescentar colunas a uma tabela já existente com `ALTER TABLE` e preenchê-las com `UPDATE`.
- Montar a **lógica de bloqueio em PHP**: um módulo só abre se o anterior foi concluído.
- Gerar os cards com um `foreach`, trocando o rótulo, a cor e o botão conforme a situação do módulo.
- Criar uma **grade responsiva** com CSS Grid (4 colunas no computador, menos no celular).
- Mostrar o **progresso do aluno** no rodapé, calculado a partir do banco de dados.
- Proteger a página do módulo para que ninguém entre digitando o endereço na barra.

### Novo caminho do aluno

Login → (troca de senha, no primeiro acesso) → **boas-vindas** → **painel de módulos** → módulo escolhido.

### Mapa dos arquivos deste módulo

| Arquivo | Pasta | Situação | Para que serve |
| --- | --- | --- | --- |
| `boas_vindas.php` | `paginas/` | novo | Tela de apresentação do curso |
| `boas_vindas.css` | `assets/css/` | novo | Visual da tela de boas-vindas |
| `logoCamisaSalina.png` | `assets/img/` | novo | Logo exibida à esquerda |
| `modulo3.sql` | `sql/` | novo | Colunas e textos dos módulos no banco |
| `introducao.jpg` … `conclusao.jpg` | `assets/img/modulos/` | novo | Uma imagem para cada card |
| `modulos.php` | `includes/` | novo | Regra de bloqueio e rótulos |
| `painel.php` | `paginas/` | reescrito | Os 9 cards |
| `modulo.php` | `paginas/` | novo (temporário) | Página do módulo com a trava de segurança |
| `estilo.css` | `assets/css/` | acrescentado | Estilo dos cards e do rodapé |
| `rodape.php` | `includes/` | alterado | Progresso e “Área do aluno” |
| `index.php` | raiz | alterado | Passa a levar à boas-vindas |

> Todo o material deste módulo está no arquivo `curso_painel_cards.zip`, já organizado nas pastas do projeto. Faça uma cópia do seu `painel.php` antes de substituir.

## Passo 1 — A tela de boas-vindas

A primeira impressão do curso: logo à esquerda; à direita a saudação, o título, o subtítulo, o versículo, a frase de impacto e o botão **Vamos lá!**. Ela **não usa o topo padrão**: é uma página de tela cheia, com visual próprio.

Figura 1 — Tela de boas-vindas completa, com logo, título, versículo e botão

&#91;image: image.png\]

### 1.1 Coloque a logo no projeto

Copie o arquivo `logoCamisaSalina.png` para a pasta:

```
C:\xampp\htdocs\curso\assets\img\logoCamisaSalina.png
```

### 1.2 O arquivo `paginas/boas_vindas.php`

```php
<?php
require_once __DIR__ . '/../includes/auth.php';

// Busca o nome no banco e pega só o primeiro nome
$st = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$st->execute([$_SESSION['usuario_id']]);
$primeiro_nome = explode(' ', trim($st->fetchColumn()))[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boas-vindas | <?= NOME_CURSO ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Dancing+Script:wght@600;700&family=Nunito:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/boas_vindas.css">
</head>
<body class="boas-vindas">

    <main class="bv-cartao">

        <div class="bv-logo">
            <img src="<?= BASE_URL ?>/assets/img/logoCamisaSalina.png" alt="Logo do curso">
        </div>

        <div class="bv-texto">
            <p class="bv-saudacao">Olá, <?= htmlspecialchars($primeiro_nome) ?>! Seja muito bem-vindo(a) ao curso</p>

            <h1 class="bv-titulo">Preparando Líderes para o Ministério Infantil</h1>

            <p class="bv-subtitulo">
                Neste curso você aprenderá como se tornar um verdadeiro líder do
                Ministério Infantil. Siga o conteúdo, assista aos vídeos, faça as
                atividades e não deixe de baixar os materiais de apoio.
            </p>

            <blockquote class="bv-versiculo">
                “Tudo o que fizerem, façam de todo o coração, como para o Senhor, ...”
                <cite>Colossenses 3:23,24</cite>
            </blockquote>

            <p class="bv-impacto">Servir com excelência é transformar o comum em extraordinário!</p>

            <a href="painel.php" class="bv-botao">Vamos lá! →</a>
        </div>

    </main>
</body>
</html>
```

> No arquivo completo o versículo aparece inteiro (versão NVI). Aqui as reticências apenas encurtam a listagem.

### 1.3 Explicação do código

| Trecho | O que faz |
| --- | --- |
| `require_once ... auth.php` | Garante que só entra quem está logado. Sem login, o aluno volta para a tela de entrada. |
| `$st = $pdo->prepare(...)` | Prepara a consulta com `?`, como nos módulos anteriores, evitando SQL injection. |
| `fetchColumn()` | Devolve apenas uma coluna da primeira linha: o nome. |
| `explode(' ', ...)[0]` | Corta o nome nos espaços e fica com a primeira palavra. Ex.: “Maria da Silva” vira “Maria”. |
| `htmlspecialchars(...)` | Protege a página caso o nome tenha símbolos como `<` ou `&`. |
| `<?= NOME_CURSO ?>` | Atalho de `<?php echo ... ?>`. Lê a constante do `config.php`. |
| `<?= BASE_URL ?>` | Prefixo `/curso` do `config.php`. Ao publicar, só se altera em um lugar. |
| `<link ... fonts.googleapis.com>` | Carrega as fontes **Baloo 2** (títulos), **Dancing Script** (versículo) e **Nunito** (texto). Exige internet. |
| `class="bv-..."` | Prefixo `bv` (boas-vindas) nos nomes de classe, para não misturar com o resto do site. |
| `href="painel.php"` | O botão é um link comum para a próxima tela. |

### 1.4 O arquivo `assets/css/boas_vindas.css`

As cores ficam no topo, em **variáveis CSS**. Para mudar a paleta, altere só o bloco `:root`.

```css
:root {
    --cor-fundo-1: #fff7e6;
    --cor-fundo-2: #ffe3c2;
    --cor-titulo: #e8590c;
    --cor-saudacao: #1c7c8c;
    --cor-texto: #4a4a4a;
    --cor-versiculo: #2b6f5e;
    --cor-impacto: #c2255c;
    --cor-botao: #e8590c;
}

body.boas-vindas {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
    background: linear-gradient(135deg, var(--cor-fundo-1), var(--cor-fundo-2));
    font-family: 'Nunito', sans-serif;
    color: var(--cor-texto);
}

.bv-cartao {
    display: flex;
    align-items: center;
    gap: 50px;
    max-width: 1050px;
    background: #fff;
    border-radius: 24px;
    padding: 50px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
}

.bv-logo { flex: 0 0 38%; text-align: center; }
.bv-logo img { max-width: 100%; height: auto; }
.bv-texto { flex: 1; }

.bv-titulo {
    margin: 0 0 16px;
    font-family: 'Baloo 2', cursive;
    font-size: 2.6rem;
    font-weight: 800;
    line-height: 1.1;
    color: var(--cor-titulo);
}

.bv-versiculo {
    margin: 0 0 20px;
    padding: 16px 22px;
    border-left: 6px solid var(--cor-versiculo);
    background: #eefaf5;
    border-radius: 0 12px 12px 0;
    font-family: 'Dancing Script', cursive;
    font-size: 1.45rem;
    color: var(--cor-versiculo);
}

.bv-botao {
    display: inline-block;
    padding: 14px 38px;
    background: var(--cor-botao);
    color: #fff;
    border-radius: 50px;
    font-family: 'Baloo 2', cursive;
    font-size: 1.3rem;
    text-decoration: none;
    transition: transform .15s, box-shadow .15s;
}
.bv-botao:hover { transform: translateY(-3px); }

@media (max-width: 800px) {
    .bv-cartao { flex-direction: column; padding: 30px 22px; gap: 24px; }
    .bv-logo { flex: none; max-width: 240px; }
    .bv-titulo { font-size: 2rem; }
}
```

> A listagem acima está resumida; o arquivo completo também traz a saudação, o subtítulo, a frase de impacto e a sombra do botão.

### 1.5 Explicação do CSS

| Trecho | O que faz |
| --- | --- |
| `:root { --cor-... }` | Cria variáveis. Depois, `var(--cor-titulo)` usa o valor. Mudou a cor? Mude só no topo. |
| `min-height: 100vh` | A página ocupa no mínimo a altura inteira da tela (100% da “viewport height”). |
| `display: flex` + `align-items: center` | Alinha os filhos lado a lado e centraliza na vertical. Usado no corpo e no cartão. |
| `linear-gradient(135deg, ...)` | Fundo com degradê diagonal entre dois tons de creme. |
| `.bv-logo { flex: 0 0 38% }` | A logo ocupa 38% da largura e **não** encolhe nem cresce. O texto (`flex: 1`) pega o resto. |
| `max-width: 100%; height: auto` | A imagem nunca passa do espaço disponível e mantém a proporção. |
| `font-family: 'Baloo 2'` | Fonte de destaque do título e do botão. O “cursive” é a reserva se a fonte não carregar. |
| `border-left` + `background` no versículo | Faixa verde à esquerda e fundo suave: efeito de citação destacada. |
| `transition` + `:hover` | O botão sobe 3 px ao passar o mouse, com animação suave. |
| `@media (max-width: 800px)` | No celular, a linha vira coluna: logo em cima, texto embaixo. |

## Passo 2 — Preparar o banco de dados

Cada card precisa de duas informações novas: uma **descrição curta** e o **nome do arquivo da imagem**. Em vez de escrever isso dentro do PHP, guardamos na tabela `modulos`. Assim, para mudar um texto basta editar uma linha no phpMyAdmin, sem mexer no código.

### 2.1 Confira a ordem dos módulos

No phpMyAdmin, abra o banco `curso_treinamento`, vá na aba **SQL** e execute:

```sql
SELECT id, ordem, titulo FROM modulos ORDER BY ordem;
```

A coluna `ordem` deve ir de **1 a 9**: 1 = Introdução, 2 a 8 = Módulos 1 a 7, 9 = Conclusão. O script seguinte usa esses números; se a sua tabela começar em 0, ajuste os `WHERE ordem=...`.

### 2.2 Acrescente as colunas

```sql
ALTER TABLE modulos
  ADD COLUMN descricao VARCHAR(300) NULL AFTER titulo,
  ADD COLUMN imagem VARCHAR(100) NULL AFTER descricao;
```

| Parte | Significado |
| --- | --- |
| `ALTER TABLE modulos` | Altera a estrutura de uma tabela que já existe. Os dados atuais são mantidos. |
| `ADD COLUMN descricao VARCHAR(300)` | Nova coluna de texto de até 300 caracteres. |
| `NULL` | Aceita vazio. Linhas antigas não dão erro. |
| `AFTER titulo` | Posiciona a coluna logo depois do título (só organiza a visualização). |

Figura 2 — phpMyAdmin: estrutura da tabela modulos com as colunas descricao e imagem

&#91;image: image.png\]

### 2.3 Preencha título, descrição e imagem de cada módulo

```sql
UPDATE modulos SET titulo='Conectando as Crianças a Jesus', imagem='introducao.jpg',
  descricao='Por que servir no Ministério Infantil é uma resposta direta ao chamado de Jesus: levar as crianças até Ele.'
  WHERE ordem=1;

UPDATE modulos SET titulo='Os atributos de um verdadeiro líder', imagem='modulo1.jpg',
  descricao='Cinco atributos que edificam a liderança: chamado de Deus, reverência, intercessão, sabedoria para ensinar e temperança.'
  WHERE ordem=2;

-- ... e assim por diante até ordem=9 (Conclusão)
```

O arquivo `sql/modulo3.sql` traz os 9 `UPDATE` prontos. O ponto mais importante está no final de cada comando: **`WHERE ordem=...`** diz qual linha será alterada. Sem o `WHERE`, **todas** as linhas receberiam o mesmo texto.

| Ordem | Rótulo no card | Título | Imagem |
| --- | --- | --- | --- |
| 1 | Introdução | Conectando as Crianças a Jesus | `introducao.jpg` |
| 2 | Módulo 1 | Os atributos de um verdadeiro líder | `modulo1.jpg` |
| 3 | Módulo 2 | Didática guiada pelo Espírito Santo | `modulo2.jpg` |
| 4 | Módulo 3 | Preparação do ambiente e boas práticas | `modulo3.jpg` |
| 5 | Módulo 4 | Técnicas e estratégias — Parte 1 | `modulo4.jpg` |
| 6 | Módulo 5 | Técnicas e estratégias — Parte 2 | `modulo5.jpg` |
| 7 | Módulo 6 | Como evitar a indisciplina | `modulo6.jpg` |
| 8 | Módulo 7 | Autoridade espiritual | `modulo7.jpg` |
| 9 | Conclusão | Conclusão | `conclusao.jpg` |

> O rótulo (“Módulo 1”, “Módulo 2”…) **não** é gravado no banco: o PHP calcula a partir da `ordem` (ordem − 1). Por isso a Introdução é ordem 1 e o Módulo 1 é ordem 2.

### 2.4 Como saber se funcionou

Rode `SELECT ordem, titulo, imagem FROM modulos ORDER BY ordem;`. Devem aparecer 9 linhas com título e imagem preenchidos. Se alguma ficou `NULL`, o `WHERE ordem=` daquele comando não encontrou a linha.

## Passo 3 — As imagens dos módulos

As figuras vieram da própria apostila (Word). Um arquivo `.docx` é um pacote compactado: ao descompactar, as imagens ficam na pasta `word/media`. Cada módulo da apostila tem uma imagem de abertura, e foi ela que virou a capa do card.

### 3.1 De onde veio cada imagem

| Card | Arquivo no projeto | Origem na apostila |
| --- | --- | --- |
| Introdução | `introducao.jpg` | Imagem “Conectando as Crianças a Jesus” |
| Módulo 1 | `modulo1.jpg` | Abertura do módulo 1 |
| Módulo 2 | `modulo2.jpg` | Abertura do módulo 2 |
| Módulo 3 | `modulo3.jpg` | Abertura do módulo 3 |
| Módulo 4 | `modulo4.jpg` | Abertura do módulo 4 |
| Módulo 5 | `modulo5.jpg` | Abertura do módulo 5 |
| Módulo 6 | `modulo6.jpg` | Abertura do módulo 6 |
| Módulo 7 | `modulo7.jpg` | Abertura do módulo 7 |
| Conclusão | `conclusao.jpg` | Cópia da Introdução, provisória, até haver imagem própria |

### 3.2 Padronização

As imagens originais tinham tamanhos diferentes (de 1024 a 1329 pixels de largura). Para os cards ficarem iguais e a página carregar rápido, cada uma foi:

1. reduzida para **800 pixels de largura**;
2. cortada no centro na proporção **16:9** (800 × 450);
3. salva em JPEG com qualidade 82, ficando entre 44 KB e 99 KB.

Três imagens leves por linha carregam bem até em internet lenta; a original de 1 MB tornaria o painel pesado.

### 3.3 Onde colocar

```
curso/
└── assets/
    └── img/
        ├── logoCamisaSalina.png
        └── modulos/
            ├── introducao.jpg
            ├── modulo1.jpg   (... até modulo7.jpg)
            └── conclusao.jpg
```

**Para trocar uma imagem depois:** salve a nova com o **mesmo nome** na mesma pasta (ou mude o nome da imagem na coluna `imagem` da tabela `modulos`). Não é preciso mexer em PHP.

## Passo 4 — A lógica de bloqueio: `includes/modulos.php`

Este é o cérebro do painel. Ele responde, para cada módulo, duas perguntas: **o aluno já concluiu?** e **ele pode entrar?** Fica em um arquivo separado porque duas páginas usam a mesma regra: o painel (para desenhar os cards) e a página do módulo (para barrar quem não pode entrar). Regra escrita em um só lugar não fica diferente de uma página para outra.

### 4.1 O código

```php
<?php
// Funções dos módulos. Usadas pelo painel e pela página do módulo.

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

function rotulo_modulo(int $ordem): string
{
    if ($ordem === 1) return 'Introdução';
    if ($ordem === 9) return 'Conclusão';
    return 'Módulo ' . ($ordem - 1);
}
```

### 4.2 Explicação da consulta SQL

| Trecho | O que faz |
| --- | --- |
| `SELECT m.id, m.ordem, ...` | Pede os dados de cada módulo. O `m.` é o apelido da tabela `modulos`. |
| `(SELECT COUNT(*) FROM progresso p ...)` | Uma consulta **dentro** da consulta (subconsulta). Conta quantas linhas existem em `progresso` para este aluno e este módulo. Resultado: **0** (não concluiu) ou **1** (concluiu). |
| `AS concluido` | Dá nome à coluna calculada. |
| `ORDER BY m.ordem` | Entrega os módulos na sequência certa, da Introdução à Conclusão. |
| `?` | Marcador preenchido por `execute([$usuario_id])`. Seguro contra SQL injection. |
| `fetchAll(PDO::FETCH_ASSOC)` | Traz todas as linhas de uma vez, como lista de arrays com os nomes das colunas. |

Uma única consulta traz os 9 módulos já com a situação do aluno. A alternativa (uma consulta por módulo) faria 9 idas ao banco a cada abertura do painel.

### 4.3 Explicação da regra de bloqueio

| Trecho | O que faz |
| --- | --- |
| `function modulos_do_aluno(PDO $pdo, int $usuario_id, bool $eh_professor): array` | Declara a função e os tipos esperados. `: array` indica que ela devolve uma lista. |
| `$anterior_concluido = true` | Começa como “verdadeiro”: o primeiro módulo não depende de nenhum anterior. |
| `foreach ($lista as &$m)` | Percorre os módulos em ordem. O `&` permite **alterar** cada item dentro do laço. |
| `(int)$m['concluido'] > 0` | Converte a contagem em verdadeiro/falso. |
| `$eh_professor \|\| $anterior_concluido` | **A regra central.** Liberado se for professor **ou** se o módulo anterior está concluído. |
| `$anterior_concluido = $m['concluido']` | Guarda a situação deste módulo para o próximo item do laço decidir. |
| `unset($m)` | Desfaz o vínculo do `&`. Evita um bug clássico quando se reusa a variável depois. |
| `rotulo_modulo()` | Converte a `ordem` no texto do card: 1 = Introdução, 9 = Conclusão, o resto = “Módulo (ordem − 1)”. |

### 4.4 Acompanhe a regra com um exemplo

Aluno que concluiu a Introdução e o Módulo 1:

| Ordem | Card | `concluido` | `$anterior_concluido` ao chegar | `liberado` | Resultado |
| --- | --- | --- | --- | --- | --- |
| 1 | Introdução | sim | true (inicial) | sim | Concluído |
| 2 | Módulo 1 | sim | sim | sim | Concluído |
| 3 | Módulo 2 | não | sim | sim | Em andamento |
| 4 | Módulo 3 | não | não | não | Bloqueado |
| 5 a 9 | demais | não | não | não | Bloqueados |

O bloqueio é “em cadeia”: se o aluno não concluiu o Módulo 3, o 4 fica trancado; e como o 4 não foi concluído, o 5 também, e assim até o fim. Para o professor, o `||` torna tudo liberado sem mexer no restante da regra.

> **Decisão de projeto:** a trava é feita em PHP, no servidor. Se fosse só CSS ou JavaScript, o aluno poderia desbloquear com as ferramentas do navegador.

## Passo 5 — O painel com os 9 cards: `paginas/painel.php`

O painel fica enxuto. O nome do aluno já aparece no menu superior, então a saudação “Olá, nome!” saiu. O progresso vai para o rodapé.

Figura 3 — Painel visto pelo aluno: Introdução liberada e os demais módulos bloqueados

&#91;image: image.png\]

Figura 4 — Painel visto pelo professor: todos os módulos liberados

&#91;image: image.png\]

### 5.1 O código

```php
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modulos.php';

// 1) Busca os módulos e calcula quem está liberado
$modulos = modulos_do_aluno($pdo, (int)$_SESSION['usuario_id'], $eh_professor);

// 2) Números da barra de progresso (aparece no rodapé)
$total      = count($modulos);
$feitos     = count(array_filter($modulos, fn($m) => $m['concluido']));
$percentual = $total ? (int)round($feitos * 100 / $total) : 0;
$mostrar_progresso = true;

require __DIR__ . '/../includes/topo.php';
?>

<h1 class="painel-titulo">Módulos do curso</h1>

<?php if (($_GET['erro'] ?? '') === 'bloqueado'): ?>
    <p class="painel-aviso">Esse módulo ainda está bloqueado. Conclua o módulo anterior para liberá-lo.</p>
<?php endif; ?>

<div class="grade-cards">
<?php foreach ($modulos as $m):

    if ($m['concluido']) {
        $classe = 'concluido';  $rotulo = '✓ Concluído';
    } elseif ($m['liberado']) {
        $classe = 'andamento';  $rotulo = $eh_professor ? 'Liberado' : 'Em andamento';
    } else {
        $classe = 'bloqueado';  $rotulo = '🔒 Bloqueado';
    }
?>
    <article class="card-modulo <?= $classe ?>">
        <div class="card-imagem">
            <img src="<?= BASE_URL ?>/assets/img/modulos/<?= htmlspecialchars($m['imagem'] ?? 'introducao.jpg') ?>" alt="" loading="lazy">
            <span class="card-rotulo"><?= $rotulo ?></span>
        </div>

        <div class="card-corpo">
            <p class="card-etiqueta"><?= rotulo_modulo($m['ordem']) ?></p>
            <h2 class="card-titulo"><?= htmlspecialchars($m['titulo']) ?></h2>
            <p class="card-descricao"><?= htmlspecialchars($m['descricao'] ?? '') ?></p>

            <?php if ($m['liberado']): ?>
                <a class="card-botao" href="modulo.php?id=<?= $m['id'] ?>">
                    <?= $m['concluido'] ? 'Rever módulo' : 'Iniciar módulo' ?>
                </a>
            <?php else: ?>
                <span class="card-botao desativado">Conclua o módulo anterior</span>
            <?php endif; ?>
        </div>
    </article>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
```

### 5.2 Explicação: a parte de cima (PHP puro)

| Trecho | O que faz |
| --- | --- |
| `require_once ... modulos.php` | Carrega as funções do passo 4. |
| `modulos_do_aluno($pdo, ..., $eh_professor)` | Devolve os 9 módulos com `concluido` e `liberado`. `$pdo` vem do `conexao.php`; `$eh_professor`, do `auth.php`. |
| `array_filter($modulos, fn($m) => $m['concluido'])` | Mantém só os módulos concluídos. O `fn` é uma função curta (“seta”). |
| `count(...)` | Conta quantos sobraram: o total de concluídos (`$feitos`). |
| `round($feitos * 100 / $total)` | Percentual de progresso. O `$total ? ... : 0` evita divisão por zero. |
| `$mostrar_progresso = true` | Sinal para o rodapé desenhar a barra. As variáveis `$feitos`, `$total` e `$percentual` ficam visíveis para o `rodape.php`, que é incluído no mesmo arquivo. |

### 5.3 Explicação: o desenho dos cards (HTML + PHP)

| Trecho | O que faz |
| --- | --- |
| `$_GET['erro'] ?? ''` | Lê `?erro=` da barra de endereço. O `??` usa `''` se não existir, sem gerar aviso. |
| `foreach ($modulos as $m):` | Repete o bloco do `<article>` **uma vez por módulo**. Escrevemos o card uma vez; o PHP gera 9. |
| `if / elseif / else` | Define a situação: concluído, em andamento (liberado) ou bloqueado. Fixa a **classe CSS** (cor) e o **rótulo** (texto). |
| `$eh_professor ? 'Liberado' : 'Em andamento'` | Operador ternário: “se for professor, Liberado; se não, Em andamento”. O professor não está “em andamento” em todos os módulos. |
| `class="card-modulo <?= $classe ?>"` | A classe (`concluido`, `andamento` ou `bloqueado`) muda o visual no CSS. |
| `$m['imagem'] ?? 'introducao.jpg'` | Se o módulo não tem imagem no banco, usa uma padrão. |
| `htmlspecialchars(...)` | Protege contra caracteres especiais nos textos vindos do banco. |
| `<?php if ($m['liberado']): ?> ... <?php else: ?> ... <?php endif; ?>` | Se liberado, mostra o **link** (`<a>`). Se não, mostra um `<span>` cinza **sem link**: não há nada para clicar. |
| `modulo.php?id=<?= $m['id'] ?>` | Envia o número do módulo na URL. A página `modulo.php` lê esse `id`. |
| `$m['concluido'] ? 'Rever módulo' : 'Iniciar módulo'` | O texto do botão muda conforme o aluno já concluiu. |

> **Sintaxe alternativa:** `foreach (...): ... endforeach;` e `if (...): ... endif;` são a forma com dois-pontos, mais legível quando se mistura HTML e PHP. É equivalente às chaves `{ }`.

## Passo 6 — O CSS dos cards e o rodapé com progresso

### 6.1 Onde colocar

Abra `assets/css/painel.css` (do zip), copie **todo** o conteúdo e cole no **final** do `assets/css/estilo.css`. Depois use **Ctrl + F5** no navegador para limpar o cache.

### 6.2 A grade e o card

```css
.grade-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}

.card-modulo {
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, .10);
    transition: transform .2s, box-shadow .2s;
}
.card-modulo:not(.bloqueado):hover { transform: translateY(-4px); }

.card-imagem { position: relative; aspect-ratio: 16 / 9; background: #ddd; }
.card-imagem img { width: 100%; height: 100%; object-fit: cover; display: block; }

.card-rotulo {
    position: absolute; top: 10px; right: 10px;
    padding: 4px 12px; border-radius: 50px;
    font-size: .8rem; font-weight: 700; color: #fff;
}
.andamento .card-rotulo { background: #1c7c8c; }
.concluido .card-rotulo { background: #2b8a3e; }
.bloqueado .card-rotulo { background: #6c757d; }

.card-corpo { display: flex; flex-direction: column; flex: 1; padding: 18px; }
.card-descricao { flex: 1; margin: 0 0 16px; }

.bloqueado .card-imagem img { filter: grayscale(1); opacity: .55; }
```

| Trecho | O que faz |
| --- | --- |
| `display: grid` | Liga a grade. Os cards se organizam em linhas e colunas. |
| `repeat(auto-fill, minmax(280px, 1fr))` | **Grade responsiva sem media query:** cabem quantas colunas de pelo menos 280 px houver na tela e elas dividem o espaço. No computador são 4; no celular, 1. |
| `gap: 24px` | Espaço entre os cards. |
| `overflow: hidden` | Corta a imagem nos cantos arredondados do card. |
| `aspect-ratio: 16 / 9` | Todas as imagens ocupam o mesmo formato, mesmo com tamanhos originais diferentes. |
| `object-fit: cover` | A imagem preenche o espaço sem esticar, cortando o excesso. |
| `position: relative` / `absolute` | O rótulo “flutua” no canto superior direito **da imagem** (o pai é `relative`). |
| `.andamento .card-rotulo` | Seletor composto: cor do rótulo **dentro** de um card com a classe `andamento`. Aqui o PHP encontra o CSS. |
| `flex: 1` na descrição | Empurra o botão para a base: todos os botões ficam alinhados, mesmo com descrições de tamanhos diferentes. |
| `:not(.bloqueado):hover` | O efeito de subir ao passar o mouse vale só para cards liberados. |
| `filter: grayscale(1); opacity: .55` | Imagem em tons de cinza e apagada nos cards bloqueados. |

### 6.3 Rodapé: progresso à esquerda, “Área do aluno” à direita

No `includes/rodape.php`, troque o seu bloco `<footer> ... </footer>` por:

```php
<footer class="rodape">
    <div class="rodape-progresso">
        <?php if (!empty($mostrar_progresso) && !$eh_professor): ?>
            <span>Seu progresso: <?= $feitos ?> de <?= $total ?> módulos</span>
            <div class="barra"><div class="barra-preenchida" style="width: <?= $percentual ?>%"></div></div>
            <span><?= $percentual ?>%</span>
        <?php endif; ?>
    </div>
    <span class="rodape-area">Área do aluno</span>
</footer>
```

```css
.rodape { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
.rodape-progresso { display: flex; align-items: center; gap: 10px; font-size: .85rem; }
.barra { width: 180px; height: 10px; background: #dee2e6; border-radius: 10px; overflow: hidden; }
.barra-preenchida { height: 100%; background: #2b8a3e; border-radius: 10px; transition: width .4s; }
.rodape-area { font-size: .75rem; color: #adb5bd; }
```

| Trecho | O que faz |
| --- | --- |
| `!empty($mostrar_progresso)` | A barra só aparece nas páginas que definiram `$mostrar_progresso = true` (no momento, o painel). O `empty` evita erro nas páginas que não definem a variável. |
| `&& !$eh_professor` | O professor não tem progresso a acompanhar, então a barra fica oculta. |
| `style="width: <?= $percentual ?>%"` | **O truque da barra:** o PHP escreve a largura da parte verde. 22% → `width: 22%`. |
| `.barra` + `.barra-preenchida` | Duas caixas: a cinza (fundo) e a verde dentro dela (parte concluída). |
| `justify-content: space-between` | Progresso na ponta esquerda e “Área do aluno” na direita. |
| `flex-wrap: wrap` | No celular, os itens passam para a linha de baixo em vez de ficarem apertados. |
| cor `#adb5bd`, fonte `.75rem` | Cinza claro e pequeno: “bem discreto”, como pedido. |

## Passo 7 — A página do módulo com trava de segurança: `paginas/modulo.php`

Os botões dos cards levam a `modulo.php?id=N`. O conteúdo real (assunto, vídeo e exercícios) vem no próximo módulo da apostila. Por enquanto esta página é **temporária**, mas já cumpre a função mais importante: **barrar quem não pode entrar**.

### 7.1 Por que a trava precisa estar aqui também

No painel, o card bloqueado não tem link. Mas o aluno pode **digitar** `modulo.php?id=5` na barra de endereço. Se a página do módulo não conferir a regra, ele entra. A segurança não está em esconder o botão: está em conferir no servidor em **cada página**.

### 7.2 O código

```php
<?php
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

// TRAVA: módulo bloqueado não abre, mesmo digitando o endereço
if (!$atual['liberado']) { header('Location: painel.php?erro=bloqueado'); exit; }

require __DIR__ . '/../includes/topo.php';
?>

<p class="card-etiqueta"><?= rotulo_modulo($atual['ordem']) ?></p>
<h1 class="painel-titulo"><?= htmlspecialchars($atual['titulo']) ?></h1>
<p>Conteúdo em construção. Em breve: assunto, vídeo e exercícios.</p>
<p><a href="painel.php">← Voltar aos módulos</a></p>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
```

### 7.3 Explicação

| Trecho | O que faz |
| --- | --- |
| `(int)($_GET['id'] ?? 0)` | Lê o `id` da URL e o converte em número inteiro. Se alguém digitar `id=abc` ou `id=1 OR 1=1`, vira `0` ou `1`: **nunca** texto perigoso. |
| `foreach ... break` | Procura na lista o módulo com aquele `id` e para assim que acha. |
| `if (!$atual)` | Não achou (`id` inválido): volta ao painel. |
| `if (!$atual['liberado'])` | **A trava.** Usa a mesma regra do painel, vinda de `modulos.php`. |
| `header('Location: ...'); exit;` | Redireciona **e interrompe** a página. Sem o `exit`, o PHP continuaria executando e enviaria o conteúdo mesmo assim. |
| `?erro=bloqueado` | O painel lê isso e mostra o aviso amarelo: “Esse módulo ainda está bloqueado…”. |

> **Regra de ouro:** toda página protegida confere o acesso **antes** de imprimir qualquer HTML. Por isso o `require` do topo vem depois das travas.

## Passo 8 — Ligar tudo e testar

### 8.1 Mudar o caminho depois do login

Hoje o login leva ao painel. Agora deve passar pela boas-vindas:

1. **`index.php`:** no login com sucesso, troque `paginas/painel.php` por `paginas/boas_vindas.php`.
2. **`paginas/trocar_senha.php`:** no redirecionamento depois de salvar a nova senha, aponte também para `boas_vindas.php`.

O caminho do aluno novo fica: login → trocar senha → boas-vindas → painel.

### 8.2 Roteiro de testes

Faça nesta ordem. Cada teste confirma uma parte do que foi construído.

| Nº | Teste | Resultado esperado |
| --- | --- | --- |
| 1 | Entre como **aluno novo** | Cai na boas-vindas com o primeiro nome. |
| 2 | Clique em **Vamos lá!** | Abre o painel com 9 cards. |
| 3 | Observe os cards do aluno novo | Só a Introdução liberada, com rótulo “Em andamento”; os outros 8 bloqueados e cinzas. |
| 4 | Observe o rodapé | “Seu progresso: 0 de 9 módulos”, barra vazia, 0%. |
| 5 | Digite `.../paginas/modulo.php?id=` com o id de um módulo bloqueado | Volta ao painel com o aviso amarelo. |
| 6 | No phpMyAdmin, na tabela `progresso`, insira `usuario_id` do aluno e `modulo_id` da Introdução | Ao atualizar: Introdução “Concluído”, Módulo 1 “Em andamento”, progresso 1 de 9 (11%). |
| 7 | Entre como **professor** | Todos os cards “Liberado” e sem barra de progresso. |
| 8 | Reduza a janela do navegador | Os cards passam de 4 colunas para 1. |

### 8.3 Como simular o aluno avançando

Até existir o botão “Concluir módulo”, o progresso é simulado no phpMyAdmin:

```sql
INSERT INTO progresso (usuario_id, modulo_id) VALUES (5, 1);
```

Troque `5` pelo `id` do aluno (tabela `usuarios`) e `1` pelo `id` do módulo (tabela `modulos`). Para desfazer:

```sql
DELETE FROM progresso WHERE usuario_id = 5;
```

> Cuidado com o `DELETE`: **sempre** com `WHERE`. Sem ele, apaga o progresso de todos os alunos.

## Estudos de caso: dois problemas reais

Os dois problemas apareceram durante a construção deste módulo. O método é o mesmo dos módulos anteriores: **ler a mensagem, achar o arquivo e a linha, entender a causa e só então corrigir**.

### Caso 1 — “Undefined array key “nome”” na tela de boas-vindas

**O sintoma.** A tela abriu com a logo e o título certos, mas o texto “Olá, ! Seja muito bem-vindo(a)” estava sem o nome. Além disso, no meio da página apareceu um aviso: `Warning: Undefined array key "nome" in C:\xampp\htdocs\curso\paginas\boas_vindas.php on line 5`. Como o corpo da página usa `display: flex`, o aviso virou uma terceira coluna e bagunçou o visual.

Figura 5 — Boas-vindas com o aviso “Undefined array key "nome"” no lado esquerdo

&#91;image: image.png\]

**Como ler a mensagem:**

| Parte da mensagem | O que significa |
| --- | --- |
| `Warning` | Aviso: o PHP continuou, mas algo não está certo. |
| `Undefined array key "nome"` | Tentamos ler a posição `"nome"` de um array, e ela não existe. |
| `boas_vindas.php on line 5` | O arquivo e a linha onde está o problema. |

**A causa.** A linha 5 era:

```php
$primeiro_nome = explode(' ', trim($_SESSION['nome']))[0];
```

Eu havia assumido que a sessão guardava o nome com a chave `'nome'`. Mas, no `auth.php` do projeto, o nome é gravado com **outra chave**. Chave inexistente = aviso e valor vazio.

**A correção.** Duas opções:

1. Abrir o `includes/auth.php`, achar a linha que grava o nome na sessão (algo como `$_SESSION['usuario_nome'] = $u['nome'];`) e usar **exatamente** essa chave.
2. Buscar o nome direto no banco, que não depende de chave nenhuma. Foi a opção adotada neste módulo:

```php
$st = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$st->execute([$_SESSION['usuario_id']]);
$primeiro_nome = explode(' ', trim($st->fetchColumn()))[0];
```

**A lição.**

- Nunca assuma o nome de uma chave de sessão: **confira onde ela é gravada**.
- A mensagem de erro do PHP já diz o arquivo e a linha: comece por ela.
- O `usuario_id` é a única chave que sabemos que existe com certeza (o `index.php` a cria); dela dá para buscar qualquer outro dado no banco.
- Um aviso na tela quebra o layout. Na versão publicada, o `display_errors` deve ficar desligado.

### Caso 2 — O painel apareceu sem formatação

**O sintoma.** O painel carregou, mas sem estilo: o título “Módulos do curso” em preto, a **imagem gigante no tamanho original**, o rótulo “Liberado” solto ao lado da figura e o link “Iniciar módulo” sublinhado em azul, tudo em uma coluna só.

Figura 6 — Painel sem o CSS dos cards: imagem em tamanho original, textos soltos

&#91;image: image.png\]

**O que isso diz.** O HTML e o PHP estavam **corretos**: os 9 módulos foram lidos do banco, os títulos e descrições apareceram, as imagens foram achadas e o professor viu “Liberado”. O que faltou foram as **regras de estilo** dos cards. O topo azul continuava estilizado porque vem de um trecho antigo do `estilo.css`.

**A causa.** O conteúdo de `painel.css` não havia sido colado dentro do `estilo.css`, ou foi colado em um ponto errado.

**O diagnóstico.** Abrir `127.0.0.1/curso/assets/css/estilo.css` no navegador e rolar até o fim. Se o código dos cards não aparece ali, o arquivo não foi salvo com a colagem.

**A correção:**

1. Copiar **todo** o `painel.css`.
2. Colar no **final** do `estilo.css`, depois da última chave `}`.
3. Salvar e atualizar com **Ctrl + F5**.

**A lição.**

- “HTML aparece, mas sem visual” quase sempre é problema no CSS, não no PHP. Isso poupa tempo: não precisa mexer em código que está certo.
- Abrir o próprio arquivo CSS pelo navegador é o jeito mais rápido de saber se ele está sendo entregue.
- O navegador guarda o CSS antigo no cache: depois de alterar, use **Ctrl + F5**.
- Um `/*` de comentário sem o `*/` correspondente, ou uma chave `}` faltando, faz o navegador ignorar tudo que vem depois.

### O que os dois casos têm em comum

Nenhum dos dois foi erro de lógica. O primeiro foi um **palpite** (o nome da chave); o segundo, um **passo manual** (a colagem). Quando o resultado não aparece como esperado, comece conferindo suposições e arquivos antes de reescrever código.

## Erros comuns e checklist final

### Erros comuns deste módulo

| Sintoma | Causa provável | Como resolver |
| --- | --- | --- |
| `Undefined array key "nome"` na boas-vindas | A chave da sessão não existe com esse nome | Conferir no `auth.php` ou buscar o nome no banco pelo `usuario_id` |
| Painel sem formatação, imagens enormes | CSS dos cards não está no `estilo.css` | Colar `painel.css` no final do `estilo.css` e usar Ctrl + F5 |
| `Unknown column 'descricao'` | O `ALTER TABLE` não foi executado | Rodar o passo 2.2 no phpMyAdmin |
| Cards sem imagem (quadro cinza) | Pasta ou nome do arquivo errado | Conferir `assets/img/modulos/` e a coluna `imagem` |
| Todos os cards bloqueados, inclusive a Introdução | `ordem` não começa em 1, ou há módulos fora de sequência | Rodar o `SELECT` do passo 2.1 e ajustar a `ordem` |
| Títulos e descrições antigos ou vazios | `WHERE ordem=` não encontrou a linha | Conferir os números de `ordem` e rodar o `UPDATE` de novo |
| `Undefined variable $pdo` ou `$eh_professor` | O `auth.php` ou o `conexao.php` usa outro nome | Abrir esses arquivos, ver o nome real e ajustar o painel |
| Barra de progresso não aparece | `$mostrar_progresso` não definido ou usuário é professor | Conferir o painel; a barra é oculta para o professor de propósito |
| Fontes diferentes da tela mostrada | Sem internet para o Google Fonts | Conectar; o site funciona, mas com fonte comum |
| Botão abre “página não encontrada” | `modulo.php` não foi copiado para `paginas/` | Copiar o arquivo do zip |

### Checklist final

Confira cada item antes de passar para o próximo módulo:

- [ ] A logo está em `assets/img/logoCamisaSalina.png`.
- [ ] `boas_vindas.php` e `boas_vindas.css` existem e a tela abre com o primeiro nome do aluno.
- [ ] O login e a troca de senha levam à boas-vindas, e o botão **Vamos lá!** leva ao painel.
- [ ] A tabela `modulos` tem as colunas `descricao` e `imagem`, preenchidas nas 9 linhas.
- [ ] As 9 imagens estão em `assets/img/modulos/`.
- [ ] `includes/modulos.php` existe, e `painel.php` e `modulo.php` foram copiados.
- [ ] O conteúdo de `painel.css` está no final do `estilo.css`.
- [ ] O rodapé mostra o progresso (aluno) e “Área do aluno” discreto à direita.
- [ ] Aluno novo: só a Introdução liberada; os outros 8 bloqueados.
- [ ] Inserir progresso no banco libera o próximo módulo e a barra sobe.
- [ ] Digitar o endereço de um módulo bloqueado volta ao painel com o aviso.
- [ ] Professor: todos liberados, sem barra de progresso.
- [ ] Comparei as telas do meu projeto com as figuras 1 a 6.

### Próximo módulo

A página real do módulo: o assunto da apostila, o vídeo do YouTube incorporado, os 5 exercícios de múltipla escolha com correção comentada e o botão **Concluir módulo**, que grava o progresso na tabela `progresso` e libera o próximo card. A partir daí, o teste manual do passo 8.3 deixa de ser necessário.
