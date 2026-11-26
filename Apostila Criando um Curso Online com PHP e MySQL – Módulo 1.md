# Apostila: Criando um Curso Online com PHP e MySQL – Módulo 1

Nov 17, 2026 · @Tia Lu

## Apresentação do módulo

Neste módulo você constrói a base de um curso online feito do zero: as pastas, o banco de dados, o login e o layout reaproveitável. No final, você entra como professor e vê um painel com topo e rodapé prontos para receber as próximas telas.

O projeto é feito em PHP puro, sem framework, com uma tela por vez. Cada arquivo faz uma só coisa, o que facilita saber onde mexer quando algo precisar mudar.

**O que você precisa ter instalado**

- XAMPP, com Apache e MySQL ligados (botão Start em verde no painel do XAMPP)
- Um editor de código, como o VS Code
- Um navegador
- Conhecimento básico de HTML, CSS, JavaScript e PHP

**Os passos do módulo**

1. Criar a estrutura de pastas
2. Criar o banco e conectar o PHP a ele
3. Criar as tabelas do banco
4. Criar o usuário professor e a tela de login
5. Criar o layout base (topo, rodapé e proteção de páginas)

**Como usar esta apostila**

Cada passo traz o objetivo, o código, a explicação do que ele faz e um teste para você conferir. Digite o código em vez de copiar e colar sempre que puder, porque é assim que você aprende onde cada peça fica. As figuras numeradas mostram como cada tela deve ficar.

## Passo 1: estrutura de pastas

**Objetivo:** organizar o projeto para que cada tipo de arquivo tenha o seu lugar.

Dentro da pasta `C:\xampp\htdocs\`, crie a pasta `curso` com as subpastas abaixo. Por enquanto só as pastas e alguns arquivos; o restante entra nos próximos passos.

```
curso/
├── index.php              (tela de login)
├── config/
│   ├── conexao.php        (conexão com o banco)
│   └── config.php         (endereço e nome do curso)
├── includes/
│   ├── auth.php           (barreira de login)
│   ├── topo.php           (cabeçalho e menu)
│   └── rodape.php
├── paginas/
│   ├── painel.php
│   └── sair.php
├── assets/
│   ├── css/estilo.css
│   ├── js/
│   └── img/
└── downloads/             (apostila em PDF)
```

> **Figura 1 —** Print do Windows Explorer mostrando a pasta `curso` aberta, com as subpastas criadas.
>
> &#91;image: image.png\]

**Para que serve cada pasta**

| Pasta | O que guarda |
| --- | --- |
| `config/` | Arquivos de configuração, como a conexão com o banco. Ao publicar o site, é aqui que você altera os dados do servidor. |
| `includes/` | Pedaços de página reaproveitados: topo, rodapé e verificação de login. |
| `paginas/` | As telas do curso depois do login. |
| `assets/` | Estilo (`css`), scripts (`js`) e imagens (`img`). |
| `downloads/` | Arquivos que o aluno pode baixar, como a apostila em PDF. |

**Por que organizar assim:** se você quiser trocar o menu, altera um arquivo em `includes/` e todas as páginas mudam. Se o endereço do site mudar, altera um arquivo em `config/`.

**Teste:** confirme que a pasta `curso` está dentro de `htdocs` e que as subpastas aparecem exatamente com esses nomes, sem acento e em letras minúsculas. Isso evita problemas quando o site for para um servidor online.

## Passo 2: criar o banco e conectar o PHP

**Objetivo:** criar o banco `curso_treinamento` e fazer o PHP conversar com ele, usando um único arquivo de conexão.

**2.1 Criar o banco no phpMyAdmin**

1. Abra o painel do XAMPP e clique em **Start** no Apache e no MySQL.
2. Abra `http://localhost/phpmyadmin` no navegador.
3. Clique em **Novo** (ou na aba **Bancos de dados**).
4. Em "Nome do banco de dados", digite exatamente `curso_treinamento`.
5. Escolha a collation `utf8mb4_general_ci` e clique em **Criar**.

> **Figura 2 —** Print do phpMyAdmin na tela de criação do banco, com o nome `curso_treinamento` e a collation escolhida.
>
> &#91;image: image.png\]

**2.2 O arquivo de conexão**

Crie o arquivo `config/conexao.php`:

```php
<?php
// Dados de acesso ao banco (no XAMPP padrão)
$host    = "localhost";
$banco   = "curso_treinamento";
$usuario = "root";
$senha   = "";   // no XAMPP, a senha do root vem vazia

try {
    // Cria a conexão usando PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );
    // Faz o PHP avisar quando houver erro no SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
```

**O que cada parte faz**

- As quatro primeiras variáveis guardam os dados de acesso. Ao publicar o site, você só altera estas quatro linhas.
- `new PDO(...)` abre a conexão. O PDO é a forma moderna e segura de usar o MySQL no PHP.
- `charset=utf8mb4` garante que acentos e caracteres especiais sejam gravados corretamente.
- `ERRMODE_EXCEPTION` faz o PHP avisar quando um comando SQL falha, em vez de ficar em silêncio.
- O bloco `try/catch` captura erros de conexão e mostra a mensagem na tela.

**2.3 Testar a conexão**

Crie `teste.php` na raiz da pasta `curso`:

```php
<?php
require "config/conexao.php";
echo "Conectou com sucesso!";
```

Abra `http://localhost/curso/teste.php`. Deve aparecer a mensagem "Conectou com sucesso!". Depois do teste, apague o `teste.php`.

> **Figura 3 —** Print do navegador mostrando "Conectou com sucesso!".
>
> &#91;image: image.png\]

**Erro comum: Unknown database**

Se aparecer `SQLSTATE[HY000] [1049] Unknown database 'curso_treinamento'`, o PHP chegou ao MySQL, mas não achou o banco. Confira se ele foi criado e se o nome está idêntico ao do arquivo, incluindo maiúsculas e minúsculas e o sublinhado (`_`). Mais soluções na seção de erros comuns.

## Passo 3: criar as tabelas do banco

**Objetivo:** montar as cinco tabelas que guardam usuários, módulos, questões, progresso e notas.

**3.1 As regras do curso viram tabelas**

| Regra do curso | Como fica no banco |
| --- | --- |
| Cada módulo só abre depois do anterior | A coluna `ordem` define a sequência; a tabela `progresso` registra o que o aluno concluiu |
| 5 questões de múltipla escolha por módulo, sem nota mínima, com explicação do erro | A tabela `questoes` guarda a alternativa correta e o texto de explicação |
| Avaliação final com 10 questões, média 7 e 2 chances | Cada tentativa fica registrada em `tentativas_avaliacao` |
| O professor cadastra os alunos | Não há cadastro público; a coluna `perfil` separa aluno de professor |
| Certificado com o nome do aluno | O nome vem da coluna `nome` de `usuarios` |

**3.2 Executar o SQL**

No phpMyAdmin, selecione o banco `curso_treinamento` na lateral, abra a aba **SQL**, cole o código abaixo e clique em **Executar**.

```sql
-- 1) Usuários (alunos e professores)
CREATE TABLE usuarios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(150) NOT NULL,            -- nome que sairá no certificado
  email         VARCHAR(150) NOT NULL UNIQUE,     -- usado como login
  senha         VARCHAR(255) NOT NULL,            -- guardada criptografada
  perfil        ENUM('aluno','professor') NOT NULL DEFAULT 'aluno',
  trocar_senha  TINYINT(1) NOT NULL DEFAULT 1,    -- 1 = precisa trocar a senha no próximo login
  ativo         TINYINT(1) NOT NULL DEFAULT 1,    -- 0 = acesso desativado
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Módulos (Introdução, Módulo 1 a 7 e Conclusão)
CREATE TABLE modulos (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  ordem      INT NOT NULL UNIQUE,                 -- 1 a 9: define a sequência de desbloqueio
  titulo     VARCHAR(150) NOT NULL,
  conteudo   MEDIUMTEXT NULL,                     -- o assunto da apostila (em HTML)
  video_url  VARCHAR(255) NULL                    -- link do YouTube
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Questões (exercícios dos módulos e avaliação final)
CREATE TABLE questoes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  modulo_id   INT NOT NULL,
  tipo        ENUM('exercicio','avaliacao') NOT NULL DEFAULT 'exercicio',
  enunciado   TEXT NOT NULL,
  alt_a       VARCHAR(500) NOT NULL,
  alt_b       VARCHAR(500) NOT NULL,
  alt_c       VARCHAR(500) NOT NULL,
  alt_d       VARCHAR(500) NOT NULL,
  correta     CHAR(1) NOT NULL,                   -- 'a', 'b', 'c' ou 'd'
  explicacao  TEXT NULL,                          -- mostrada quando o aluno erra
  FOREIGN KEY (modulo_id) REFERENCES modulos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Progresso (qual aluno concluiu qual módulo)
CREATE TABLE progresso (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id    INT NOT NULL,
  modulo_id     INT NOT NULL,
  concluido_em  DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (usuario_id, modulo_id),                 -- impede registrar o mesmo módulo duas vezes
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (modulo_id)  REFERENCES modulos(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) Tentativas da avaliação final
CREATE TABLE tentativas_avaliacao (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT NOT NULL,
  nota        DECIMAL(4,2) NOT NULL,              -- de 0 a 10
  aprovado    TINYINT(1) NOT NULL,                -- 1 se nota >= 7
  ativa       TINYINT(1) NOT NULL DEFAULT 1,      -- vira 0 quando o aluno é obrigado a recomeçar
  feita_em    DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6) Os 9 módulos (os textos e vídeos entram depois)
INSERT INTO modulos (ordem, titulo) VALUES
 (1, 'Introdução'),
 (2, 'Módulo 1'), (3, 'Módulo 2'), (4, 'Módulo 3'), (5, 'Módulo 4'),
 (6, 'Módulo 5'), (7, 'Módulo 6'), (8, 'Módulo 7'),
 (9, 'Conclusão');
```

**3.3 Como ler esse SQL**

- `AUTO_INCREMENT PRIMARY KEY`: cada linha ganha um número único automaticamente. É a identidade da linha.
- `UNIQUE`: o valor não pode se repetir. No e-mail, impede dois usuários com o mesmo login. Em `progresso`, impede registrar o mesmo módulo duas vezes para o mesmo aluno.
- `FOREIGN KEY ... REFERENCES`: liga uma tabela à outra. Uma linha de `progresso` só existe se o aluno e o módulo existirem.
- `ON DELETE CASCADE`: ao excluir um aluno, o progresso e as notas dele são apagados junto, sem sobrar lixo no banco.
- `ENUM('aluno','professor')`: o campo só aceita um dos valores listados.
- `ordem` nos módulos: é a base do desbloqueio. O módulo de ordem 3 só abre quando o de ordem 2 estiver concluído.
- `ativa` nas tentativas: se o aluno reprova na segunda chance, o PHP apaga o progresso dele e marca as tentativas como `ativa = 0`. O histórico fica guardado, mas ele ganha duas chances novas.

**3.4 Teste**

Na lateral do phpMyAdmin, expanda o banco `curso_treinamento`. Devem aparecer as tabelas `usuarios`, `modulos`, `questoes`, `progresso` e `tentativas_avaliacao`. Clique em `modulos` e confira os 9 registros.

> **Figura 4 —** Print da lateral do phpMyAdmin com as 5 tabelas listadas.
>
> &#91;image: image.png\]
>
> **Figura 5 —** Print da tabela `modulos` com os 9 registros (Introdução, Módulo 1 a 7 e Conclusão).
>
> &#91;image: image.png\]

## Passo 4: usuário professor e tela de login

**Objetivo:** criar o seu usuário professor e uma tela de login que guarda quem entrou.

**4.1 Criar o usuário professor**

A senha nunca é guardada como o usuário digitou. Ela passa por `password_hash`, que a transforma em um texto embaralhado. Por isso criamos o primeiro usuário com um pequeno script.

Crie `criar_professor.php` na raiz da pasta `curso`:

```php
<?php
require "config/conexao.php";

// Troque pelos seus dados
$nome  = "Seu Nome Completo";
$email = "professor@teste.com";
$senha = "troque123";

// Criptografa a senha (nunca se guarda a senha "pura" no banco)
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Os ? serão trocados pelos valores de forma segura
$sql  = "INSERT INTO usuarios (nome, email, senha, perfil, trocar_senha)
         VALUES (?, ?, ?, 'professor', 0)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nome, $email, $hash]);

echo "Professor criado!";
```

Abra `http://localhost/curso/criar_professor.php` **uma única vez**. Se abrir de novo, dá erro, porque o e-mail é único. Depois confira a tabela `usuarios`: a senha deve aparecer como um texto longo e embaralhado.

**Apague o `criar_professor.php` em seguida.** Se ele ficasse no servidor, qualquer pessoa que o encontrasse poderia criar professores.

> **Figura 6 —** Print da tabela `usuarios` no phpMyAdmin com o professor criado e a senha criptografada visível.
>
> &#91;image: image.png\]

**4.2 A tela de login**

Se já existir um `index.php` de uma versão antiga, guarde uma cópia antes. Substitua o conteúdo por:

```php
<?php
session_start();                       // liga o uso de sessão (memória do login)
require "config/conexao.php";

// Se já está logado, vai direto para o painel
if (isset($_SESSION['usuario_id'])) {
    header("Location: paginas/painel.php");
    exit;
}

$erro = "";

// Só executa quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Procura o usuário pelo e-mail
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Confere a senha digitada com a senha criptografada
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id']   = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['perfil']       = $usuario['perfil'];
        header("Location: paginas/painel.php");
        exit;
    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Curso</title>
  <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="pagina-login">
  <main class="caixa-login">
    <h1>Acesse o curso</h1>

    <?php if ($erro): ?>
      <p class="erro"><?= $erro ?></p>
    <?php endif; ?>

    <form method="post">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" required>

      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" required>

      <button type="submit">Entrar</button>
    </form>
  </main>
</body>
</html>
```

**4.3 Painel provisório e botão de sair**

Crie `paginas/painel.php`. Este arquivo será refeito no Passo 5; por enquanto serve para confirmar que o login funciona.

```php
<?php
session_start();

// Barreira: quem não está logado volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel</title>
  <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
  <h1>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>
  <p>Perfil: <?= $_SESSION['perfil'] ?></p>
  <a href="sair.php">Sair</a>
</body>
</html>
```

Crie `paginas/sair.php`:

```php
<?php
session_start();
session_destroy();                 // apaga o login da memória
header("Location: ../index.php");
exit;
```

**4.4 CSS inicial**

Em `assets/css/estilo.css`:

```css
* { box-sizing: border-box; }

body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 20px;
}

.pagina-login {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #eef2f7;
}

.caixa-login {
  background: #fff;
  padding: 30px;
  width: 100%;
  max-width: 360px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,.15);
}

.caixa-login label { display: block; margin-top: 15px; }

.caixa-login input {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.caixa-login button {
  width: 100%;
  margin-top: 20px;
  padding: 12px;
  background: #1e5fa8;
  color: #fff;
  border: 0;
  border-radius: 4px;
  cursor: pointer;
}

.erro { color: #b00020; }
```

> **Figura 7 —** Print da tela de login no navegador.
>
> &#91;image: image.png\]

**4.5 Conceitos deste passo**

- `session_start()` é a memória do servidor sobre cada visitante. Depois do login, `$_SESSION['usuario_id']` fica guardado enquanto o navegador estiver aberto. Essa linha vem antes de qualquer HTML.
- `password_verify` compara a senha digitada com a senha embaralhada do banco. Por isso não existe "recuperar" a senha, apenas redefinir uma nova.
- Os `?` no SQL (prepared statements) impedem que alguém digite código malicioso no campo de e-mail. Use sempre esse padrão.
- `header(...)` seguido de `exit`: o `exit` garante que o PHP pare logo depois do redirecionamento.
- `htmlspecialchars` impede que um texto com `<` ou `>` quebre a página ou injete código.

**4.6 Testes**

1. Entre com o e-mail e a senha do professor. Deve aparecer "Olá, \[seu nome\]!".
2. Clique em **Sair** e confira se volta ao login.
3. Tente uma senha errada e veja a mensagem de erro.
4. Sem estar logado, abra direto `http://localhost/curso/paginas/painel.php`. Você deve ser mandado de volta ao login.

> **Figura 8 —** Print da mensagem "Olá, \[nome\]!" depois do login.
>
> &#91;image: image.png\]

## Passo 5: layout base (topo, rodapé e proteção)

**Objetivo:** escrever o topo e o rodapé uma só vez e usar em todas as páginas, além de concentrar a verificação de login em um arquivo.

Daqui para a frente, toda página protegida segue este formato: `auth.php`, depois o título, depois `topo.php`, o conteúdo e `rodape.php`.

**5.1 Configuração geral**

Os links do menu precisam funcionar de qualquer pasta. Para isso, criamos constantes com o endereço base e o nome do curso. Ao publicar o site, só este arquivo muda.

Crie `config/config.php`:

```php
<?php
// Endereço base do site.
// No XAMPP o site fica em http://localhost/curso
// No servidor online, se ficar na raiz do domínio, use apenas ""
define('BASE_URL', '/curso');

// Nome do curso (aparece no topo e no título das páginas)
define('NOME_CURSO', 'Nome do Seu Curso');
```

**5.2 A barreira de login em um arquivo só**

Crie `includes/auth.php`:

```php
<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/conexao.php";

session_start();

// Quem não está logado volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

// Facilita o uso nas páginas: $eh_professor vale true ou false
$eh_professor = ($_SESSION['perfil'] === 'professor');
```

`__DIR__` é a pasta do arquivo atual. Usar esse caminho evita erro quando o `auth.php` é chamado de páginas em pastas diferentes.

**5.3 O topo**

Crie `includes/topo.php`:

```php
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo_pagina ?? 'Curso') ?> - <?= NOME_CURSO ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/estilo.css">
</head>
<body>

<header class="topo">
  <div class="topo-conteudo">

    <a class="topo-logo" href="<?= BASE_URL ?>/paginas/painel.php">
      <?= NOME_CURSO ?>
    </a>

    <nav class="topo-menu">
      <a href="<?= BASE_URL ?>/paginas/painel.php">Início</a>
      <!-- Aqui entrarão depois: menu suspenso de módulos e página de vídeos -->
    </nav>

    <div class="topo-usuario">
      <span class="usuario-nome"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
      <span class="usuario-perfil"><?= ($_SESSION['perfil'] === 'professor') ? 'Professor' : 'Aluno' ?></span>
      <a class="botao-sair" href="<?= BASE_URL ?>/paginas/sair.php">Sair</a>
    </div>

  </div>
</header>

<main class="conteudo">
```

**5.4 O rodapé**

Crie `includes/rodape.php`:

```php
</main>

<footer class="rodape">
  <p>&copy; <?= date('Y') ?> <?= NOME_CURSO ?> - Treinamento sem fins lucrativos</p>
</footer>

</body>
</html>
```

O `topo.php` abre `<html>`, `<body>` e `<main>`, e o `rodape.php` fecha tudo. Cada página escreve apenas o que vai no meio.

**5.5 O painel usando as três peças**

Substitua `paginas/painel.php` por:

```php
<?php
require_once "../includes/auth.php";

$titulo_pagina = "Início";
require "../includes/topo.php";
?>

<h1>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>
<p>Aqui entrarão os cards dos módulos.</p>

<?php require "../includes/rodape.php"; ?>
```

**5.6 CSS do topo e do rodapé**

Em `assets/css/estilo.css`, **substitua** o bloco `body` atual por este. O `padding: 20px` sai, porque agora o topo ocupa a largura toda:

```css
body {
  font-family: Arial, sans-serif;
  margin: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f4f6f9;
}
```

E **acrescente no final** do arquivo:

```css
/* ---------- Topo ---------- */
.topo {
  background: #1e5fa8;
  color: #fff;
}

.topo-conteudo {
  max-width: 1100px;
  margin: 0 auto;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  gap: 25px;
  flex-wrap: wrap;
}

.topo-logo {
  font-size: 1.2rem;
  font-weight: bold;
  color: #fff;
  text-decoration: none;
}

.topo-menu { flex: 1; }          /* empurra o usuário para a direita */

.topo-menu a {
  color: #fff;
  text-decoration: none;
  margin-right: 18px;
}
.topo-menu a:hover { text-decoration: underline; }

.topo-usuario {
  display: flex;
  align-items: center;
  gap: 10px;
}

.usuario-perfil {
  background: rgba(255,255,255,.2);
  padding: 3px 8px;
  border-radius: 10px;
  font-size: .8rem;
}

.botao-sair {
  background: #fff;
  color: #1e5fa8;
  padding: 6px 12px;
  border-radius: 4px;
  text-decoration: none;
  font-size: .9rem;
}

/* ---------- Conteúdo e rodapé ---------- */
.conteudo {
  flex: 1;                       /* ocupa o espaço e mantém o rodapé embaixo */
  width: 100%;
  max-width: 1100px;
  margin: 0 auto;
  padding: 25px 20px;
}

.rodape {
  background: #263238;
  color: #cfd8dc;
  text-align: center;
  padding: 15px;
  font-size: .85rem;
}
```

> **Figura 9 —** Print do painel com o topo azul, o nome do usuário, a etiqueta "Professor", o botão Sair e o rodapé escuro.
>
> &#91;image: image.png\]

**5.7 Conceitos deste passo**

- `require_once` carrega o arquivo uma única vez, mesmo que seja chamado duas vezes. Use para configuração e conexão. Já o `require` carrega sempre, e serve para topo e rodapé.
- `$titulo_pagina` é definida na página antes de chamar o `topo.php`, e o topo a lê no `<title>`. O `?? 'Curso'` é o valor reserva, caso a variável não exista.
- `flex: 1` no `.conteudo` mantém o rodapé no fim da tela, mesmo em páginas curtas.

**5.8 Testes**

1. Faça login como professor. Devem aparecer a barra azul com o nome do curso, o seu nome com a etiqueta "Professor", o botão Sair e o rodapé escuro.
2. Clique em **Sair** e confira se volta ao login.
3. Abra `http://localhost/curso/paginas/painel.php` sem estar logado. Deve ir para o login.
4. Mude o `NOME_CURSO` em `config/config.php` e veja o nome mudar no topo e no rodapé.

## Erros comuns e como resolver

Quando algo não funcionar, leia a mensagem de erro com calma: ela quase sempre diz o arquivo e a linha do problema.

| Erro ou sintoma | Causa mais provável | Como resolver |
| --- | --- | --- |
| `Unknown database 'curso_treinamento'` | O banco não foi criado ou o nome está diferente no `conexao.php` | Crie o banco no phpMyAdmin ou corrija `$banco` para o nome exato, incluindo maiúsculas e `_` |
| `Access denied for user 'root'` | Senha do MySQL diferente da que está no arquivo | Ajuste `$senha` em `conexao.php`. No XAMPP padrão ela fica vazia |
| Página não carrega ou "não foi possível conectar" | Apache ou MySQL desligados | Abra o painel do XAMPP e clique em Start nos dois |
| `404 Not Found` | Pasta ou arquivo com nome diferente da URL | Confira que `curso` está dentro de `htdocs` e o nome do arquivo na URL |
| `Table ... already exists` | O SQL foi executado duas vezes | As tabelas já existem. Confira na lateral e só apague e recrie se tiver certeza |
| `Duplicate entry` ao criar o professor | O `criar_professor.php` foi aberto duas vezes | O e-mail já existe. Troque o e-mail ou apague a linha na tabela `usuarios` |
| `headers already sent` | Há texto, espaço ou linha em branco antes de `<?php` ou antes de um `header(...)` | Remova o que vem antes. `session_start()` e `header()` vêm antes de qualquer HTML |
| O CSS não aparece | Caminho do `estilo.css` errado ou `BASE_URL` incorreto | Confira `BASE_URL` em `config.php` e o nome da pasta `assets/css` |
| Acentos aparecem estranhos | Arquivo salvo em outra codificação | Salve os arquivos como UTF-8 e mantenha `<meta charset="UTF-8">` |
| Volta ao login mesmo com a senha certa | O `session_start()` não está sendo chamado antes do HTML | Confira se ele está na primeira linha de PHP do `index.php` e do `auth.php` |
| Tela branca sem mensagem | Erro de sintaxe no PHP (aspas, ponto e vírgula, chave) | Revise a última linha que você editou. Os logs ficam em `xampp/apache/logs/error.log` |

**Hábitos que evitam dor de cabeça**

- Teste depois de cada arquivo criado, não só no final.
- Altere uma coisa por vez. Se quebrar, você sabe onde olhar.
- Guarde uma cópia da pasta `curso` antes de mudanças grandes.
- Nunca deixe arquivos de teste, como `teste.php` e `criar_professor.php`, em um servidor online.

## Checklist final do módulo

Marque cada item depois de testar. Se todos estiverem marcados, a base do curso está pronta.

- [ ] A pasta `curso` e as subpastas existem em `htdocs`
- [ ] O banco `curso_treinamento` foi criado com collation `utf8mb4_general_ci`
- [ ] `config/conexao.php` funciona e o `teste.php` foi apagado
- [ ] As 5 tabelas existem e `modulos` tem 9 registros
- [ ] O usuário professor foi criado e o `criar_professor.php` foi apagado
- [ ] O login funciona com a senha certa e mostra erro com a senha errada
- [ ] O painel só abre para quem está logado
- [ ] O botão Sair encerra o login
- [ ] Topo e rodapé aparecem no painel, com o nome do usuário e o perfil
- [ ] `NOME_CURSO` e `BASE_URL` estão em `config/config.php`

**O que vem no Módulo 2**

No próximo módulo, o professor passa a cadastrar alunos e os alunos trocam a senha provisória no primeiro acesso. Em seguida, o painel ganha os cards dos 9 módulos com a barra de progresso e o bloqueio dos módulos ainda não concluídos.
