# Apostila: Criando um Curso Online com PHP e MySQL – Módulo 2

Nov 23, 2026 · @Tia Lu

## Apresentação do módulo

No Módulo 1 você montou a base do curso: pastas, banco, login e layout. Neste módulo o professor passa a **cadastrar e gerenciar alunos**, e cada aluno troca a senha provisória no primeiro acesso. No final, você tem o ciclo completo de acesso funcionando, pronto para receber os cards dos módulos.

O módulo também traz **dois estudos de caso reais**: erros que apareceram durante a construção e o raciocínio usado para encontrar e corrigir cada um. Aprender a diagnosticar é tão importante quanto aprender a escrever código.

**Pré-requisito**

- Módulo 1 concluído e funcionando: login do professor, `auth.php`, `topo.php` e `rodape.php`
- XAMPP com Apache e MySQL ligados

**Os passos do módulo**

1. Cadastro de alunos pelo professor
2. Troca obrigatória de senha no primeiro acesso
3. Decisão de projeto: desativar em vez de excluir, e o `auth.php` definitivo
4. Tela de gestão de alunos: resetar senha, desativar, reativar, excluir e editar

**Arquivos novos e alterados**

| Arquivo | Situação | Para que serve |
| --- | --- | --- |
| `includes/somente_professor.php` | novo | Barreira: só o professor entra |
| `paginas/aluno_cadastrar.php` | novo | Formulário de cadastro de aluno |
| `paginas/trocar_senha.php` | novo | Troca de senha (obrigatória ou voluntária) |
| `paginas/alunos.php` | novo | Lista e ações sobre alunos |
| `paginas/aluno_desativar.php` | novo | Confirmação de desativação, com motivo |
| `paginas/aluno_editar.php` | novo | Edição de nome e e-mail |
| `includes/auth.php` | alterado | Passa a exigir troca de senha e conferir se o usuário está ativo |
| `index.php` | alterado | Login encaminha para a troca de senha e mostra aviso de desativado |
| `includes/topo.php` | alterado | Links Alunos e Alterar senha |
| `assets/css/estilo.css` | alterado | Estilos de formulários, tabelas e botões |

As **figuras numeradas** mostram como cada tela deve ficar no navegador, para você comparar com o seu resultado.

## Passo 1: cadastro de alunos pelo professor

**Objetivo:** criar uma tela, exclusiva do professor, para cadastrar alunos com nome, e-mail e senha provisória. O aluno nasce marcado com `trocar_senha = 1`, e é isso que vai obrigar a troca de senha no Passo 2.

**1.1 A barreira "só professor"**

Crie `includes/somente_professor.php`:

```php
<?php
require_once __DIR__ . "/auth.php";   // primeiro exige estar logado

// Se não for professor, volta para o painel
if (!$eh_professor) {
    header("Location: " . BASE_URL . "/paginas/painel.php");
    exit;
}
```

Esse arquivo reaproveita o `auth.php` e acrescenta uma segunda checagem. Toda página exclusiva do professor vai começar com ele. Como a verificação é feita no PHP, o aluno não consegue entrar digitando o endereço na mão.

**1.2 A página de cadastro**

Crie `paginas/aluno_cadastrar.php`:

```php
<?php
require_once "../includes/somente_professor.php";

$titulo_pagina = "Cadastrar aluno";
$erro    = "";
$sucesso = "";
$nome    = "";
$email   = "";
$senha   = "";

// Só executa quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = "Preencha todos os campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";

    } elseif (strlen($senha) < 6) {
        $erro = "A senha provisória precisa ter pelo menos 6 caracteres.";

    } else {
        // O e-mail é o login, então não pode se repetir
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = "Já existe um usuário com esse e-mail.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, senha, perfil, trocar_senha)
                 VALUES (?, ?, ?, 'aluno', 1)"
            );
            $stmt->execute([$nome, $email, $hash]);

            $sucesso = "Aluno cadastrado! Passe a ele o login <strong>"
                     . htmlspecialchars($email)
                     . "</strong> e a senha provisória <strong>"
                     . htmlspecialchars($senha) . "</strong>.";

            // Limpa o formulário para o próximo cadastro
            $nome = "";
            $email = "";
        }
    }
}

// Sugere uma senha provisória aleatória (o professor pode trocar)
$senha_campo = $erro ? $senha : substr(bin2hex(random_bytes(4)), 0, 8);

require "../includes/topo.php";
?>

<h1>Cadastrar aluno</h1>

<p><a href="alunos.php">&larr; Ver todos os alunos</a></p>

<?php if ($erro): ?>
  <p class="mensagem mensagem-erro"><?= $erro ?></p>
<?php endif; ?>

<?php if ($sucesso): ?>
  <p class="mensagem mensagem-ok"><?= $sucesso ?></p>
<?php endif; ?>

<form method="post" class="formulario">
  <label for="nome">Nome completo (como sairá no certificado)</label>
  <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>

  <label for="email">E-mail (será o login)</label>
  <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

  <label for="senha">Senha provisória</label>
  <input type="text" id="senha" name="senha" value="<?= htmlspecialchars($senha_campo) ?>" required>

  <button type="submit">Cadastrar aluno</button>
</form>

<?php require "../includes/rodape.php"; ?>
```

O link "Ver todos os alunos" aponta para a página `alunos.php`, que só será criada no Passo 4. Até lá, esse link vai dar erro 404, e isso é esperado.

**1.3 Link no menu, só para o professor**

Em `includes/topo.php`, dentro do `<nav class="topo-menu">`:

```php
<nav class="topo-menu">
  <a href="<?= BASE_URL ?>/paginas/painel.php">Início</a>

  <?php if ($eh_professor): ?>
    <a href="<?= BASE_URL ?>/paginas/aluno_cadastrar.php">Cadastrar alunos</a>
  <?php endif; ?>

  <!-- Aqui entrarão depois: menu suspenso de módulos e página de vídeos -->
</nav>
```

**1.4 CSS de formulários, mensagens e tabelas**

Acrescente no final do `assets/css/estilo.css`:

```css
/* ---------- Formulários da área logada ---------- */
.formulario {
  background: #fff;
  padding: 25px;
  max-width: 480px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,.1);
  margin-bottom: 35px;
}

.formulario label { display: block; margin-top: 15px; font-weight: bold; }

.formulario input {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.formulario button {
  margin-top: 20px;
  padding: 12px 20px;
  background: #1e5fa8;
  color: #fff;
  border: 0;
  border-radius: 4px;
  cursor: pointer;
}

/* ---------- Mensagens ---------- */
.mensagem {
  padding: 12px 15px;
  border-radius: 4px;
  max-width: 480px;
}
.mensagem-ok   { background: #e3f5e8; color: #1b5e30; }
.mensagem-erro { background: #fdeaea; color: #b00020; }

/* ---------- Tabelas ---------- */
.tabela {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  box-shadow: 0 2px 10px rgba(0,0,0,.1);
}
.tabela th, .tabela td {
  padding: 10px 12px;
  text-align: left;
  border-bottom: 1px solid #e3e7ec;
}
.tabela th { background: #eef2f7; }
```

&#91;image: Figura 1. Tela Cadastrar aluno, com o formulário preenchido.\]

*Figura 1. Tela Cadastrar aluno, com o formulário preenchido.*

&#91;image: Figura 2. Mensagem verde de sucesso, com o login e a senha provisória do aluno.\]

*Figura 2. Mensagem verde de sucesso, com o login e a senha provisória do aluno.*

**1.5 Conceitos deste passo**

- **O PHP vem antes do `topo.php`.** Todo o processamento (validar, gravar, redirecionar) fica no começo, e o `require` do topo só depois. Isso evita o erro `headers already sent`, porque nada foi escrito na tela ainda.
- **Validações em camadas:** campos vazios, formato de e-mail (`filter_var`), tamanho mínimo da senha e e-mail repetido. A consulta de e-mail repetido evita o erro `Duplicate entry` do banco e mostra uma mensagem amigável.
- **`mb_strtolower` no e-mail:** `Maria@x.com` e `maria@x.com` viram o mesmo login.
- **`trocar_senha = 1`:** registra que a senha ainda é provisória. A senha é guardada criptografada, e o professor só vê o texto puro no momento do cadastro.
- **`random_bytes` e `bin2hex`:** geram uma senha provisória aleatória de 8 caracteres, que o professor pode trocar antes de cadastrar.

**1.6 Testes**

1. Entre como professor e confirme que aparece **Cadastrar alunos** no menu.
2. Cadastre um aluno de teste. Deve aparecer a mensagem verde com o login e a senha provisória.
3. Tente cadastrar o mesmo e-mail de novo. Deve aparecer "Já existe um usuário com esse e-mail."
4. Tente uma senha com 3 caracteres e um e-mail sem `@`, e veja as mensagens de erro.
5. No phpMyAdmin, confira na tabela `usuarios`: o aluno deve estar com `perfil = aluno`, `trocar_senha = 1` e a senha criptografada.
6. Em uma janela anônima, entre com o aluno de teste e confirme que o link **Cadastrar alunos não aparece**. Cole `http://localhost/curso/paginas/aluno_cadastrar.php` na barra de endereço: ele deve voltar ao painel.

## Passo 2: troca obrigatória de senha no primeiro acesso

**Objetivo:** quem entra com senha provisória é levado à tela de troca e não acessa mais nada do curso até trocar.

**2.1 Como o fluxo funciona**

1. O aluno entra com a senha provisória.
2. O login vê que `trocar_senha = 1` e manda o aluno para a tela de troca.
3. Enquanto não trocar, qualquer outra página protegida o devolve para essa tela.
4. Ao trocar, o PHP grava a nova senha criptografada, zera o `trocar_senha` e libera o painel.

Como a barreira já mora no `auth.php`, basta ensinar a ela uma regra nova, e todas as páginas protegidas passam a respeitá-la sem mexer nelas.

**2.2 O login guarda se a troca é obrigatória**

No `index.php`, localize o bloco que roda quando a senha confere e substitua por este:

```php
// Confere a senha digitada com a senha criptografada
if ($usuario && password_verify($senha, $usuario['senha'])) {
    session_regenerate_id(true);   // gera um novo código de sessão por segurança

    $_SESSION['usuario_id']   = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['perfil']       = $usuario['perfil'];
    $_SESSION['trocar_senha'] = (int) $usuario['trocar_senha'];

    // Senha provisória: vai direto para a troca
    if ($usuario['trocar_senha']) {
        header("Location: paginas/trocar_senha.php");
    } else {
        header("Location: paginas/painel.php");
    }
    exit;
} else {
    $erro = "E-mail ou senha incorretos.";
}
```

Duas novidades: o `trocar_senha` entra na sessão, e o `session_regenerate_id(true)` troca o código da sessão a cada login, o que dificulta o roubo de sessão.

**2.3 O `auth.php` passa a exigir a troca**

Em `includes/auth.php`, acrescente este bloco **depois** da verificação de login e **antes** da linha do `$eh_professor`:

```php
// Senha provisória: só a página de troca de senha é permitida
if (!empty($_SESSION['trocar_senha']) && empty($pagina_troca_senha)) {
    header("Location: " . BASE_URL . "/paginas/trocar_senha.php");
    exit;
}
```

A página de troca define `$pagina_troca_senha = true` antes de chamar o `auth.php`. Assim ela mesma não fica num ciclo de redirecionamento. O `sair.php` não usa o `auth.php`, então o botão Sair continua funcionando.

**2.4 A página de troca de senha**

Crie `paginas/trocar_senha.php`:

```php
<?php
$pagina_troca_senha = true;   // avisa o auth.php que esta página é permitida
require_once "../includes/auth.php";

$titulo_pagina = "Trocar senha";
$obrigatoria   = !empty($_SESSION['trocar_senha']);
$erro          = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atual    = $_POST['senha_atual'] ?? '';
    $nova     = $_POST['nova_senha'] ?? '';
    $confirma = $_POST['confirma_senha'] ?? '';

    // Busca a senha atual (criptografada) deste usuário
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $hash_atual = $stmt->fetchColumn();

    if (!$hash_atual || !password_verify($atual, $hash_atual)) {
        $erro = "A senha atual está incorreta.";

    } elseif (strlen($nova) < 6) {
        $erro = "A nova senha precisa ter pelo menos 6 caracteres.";

    } elseif ($nova !== $confirma) {
        $erro = "A confirmação não é igual à nova senha.";

    } elseif ($nova === $atual) {
        $erro = "A nova senha precisa ser diferente da atual.";

    } else {
        // Grava a nova senha e marca que a troca foi feita
        $novo_hash = password_hash($nova, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "UPDATE usuarios SET senha = ?, trocar_senha = 0 WHERE id = ?"
        );
        $stmt->execute([$novo_hash, $_SESSION['usuario_id']]);

        $_SESSION['trocar_senha'] = 0;   // libera o acesso ao curso

        header("Location: painel.php?senha=ok");
        exit;
    }
}

require "../includes/topo.php";
?>

<h1>Trocar senha</h1>

<?php if ($obrigatoria): ?>
  <p class="mensagem mensagem-aviso">
    Por segurança, troque a senha provisória antes de continuar.
  </p>
<?php endif; ?>

<?php if ($erro): ?>
  <p class="mensagem mensagem-erro"><?= $erro ?></p>
<?php endif; ?>

<form method="post" class="formulario">
  <label for="senha_atual">Senha atual<?= $obrigatoria ? ' (a provisória)' : '' ?></label>
  <input type="password" id="senha_atual" name="senha_atual" required>

  <label for="nova_senha">Nova senha (mínimo 6 caracteres)</label>
  <input type="password" id="nova_senha" name="nova_senha" required>

  <label for="confirma_senha">Repita a nova senha</label>
  <input type="password" id="confirma_senha" name="confirma_senha" required>

  <button type="submit">Salvar nova senha</button>
</form>

<?php require "../includes/rodape.php"; ?>
```

A mesma tela serve para duas situações: a troca **obrigatória** do primeiro acesso e a troca **voluntária**, quando qualquer usuário quiser mudar a senha depois. A única diferença é o aviso amarelo no topo.

**2.5 Link "Alterar senha" e mensagem no painel**

Em `includes/topo.php`, dentro de `<div class="topo-usuario">`, acrescente o link antes do botão Sair:

```php
<div class="topo-usuario">
  <span class="usuario-nome"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
  <span class="usuario-perfil"><?= ($_SESSION['perfil'] === 'professor') ? 'Professor' : 'Aluno' ?></span>
  <a class="link-senha" href="<?= BASE_URL ?>/paginas/trocar_senha.php">Alterar senha</a>
  <a class="botao-sair" href="<?= BASE_URL ?>/paginas/sair.php">Sair</a>
</div>
```

Em `paginas/painel.php`, logo abaixo do `<h1>`, acrescente a mensagem de confirmação:

```php
<?php if (($_GET['senha'] ?? '') === 'ok'): ?>
  <p class="mensagem mensagem-ok">Senha alterada com sucesso!</p>
<?php endif; ?>
```

**2.6 CSS**

Acrescente no final do `assets/css/estilo.css`:

```css
/* ---------- Aviso e link de senha ---------- */
.mensagem-aviso { background: #fff6dd; color: #7a5b00; }

.link-senha {
  color: #fff;
  font-size: .9rem;
  text-decoration: underline;
}
```

&#91;image: Figura 3. Tela Trocar senha, com o aviso amarelo do primeiro acesso do aluno.\]

*Figura 3. Tela Trocar senha, com o aviso amarelo do primeiro acesso do aluno.*

&#91;image: Figura 4. Painel do aluno, com a mensagem de senha alterada com sucesso.\]

*Figura 4. Painel do aluno, com a mensagem de senha alterada com sucesso.*

**2.7 Conceitos deste passo**

- **A regra mora no `auth.php`.** Você escreveu a verificação uma vez, e todas as páginas protegidas, inclusive as do professor, passam a segui-la.
- **`$pagina_troca_senha`** é uma "licença" que só a página de troca declara. Sem ela, a própria página redirecionaria para si mesma sem parar.
- **A senha atual é exigida de novo**, mesmo com o usuário logado. Isso protege quem deixou o computador aberto.
- **`fetchColumn()`** devolve só o valor da primeira coluna da consulta, útil quando você quer um único dado, como aqui o hash da senha.
- **O redirecionamento com `?senha=ok`** é um jeito simples de mostrar uma mensagem de sucesso na página seguinte. O `$_GET` lê o que vem na barra de endereço.
- **A sessão precisa ser atualizada** (`$_SESSION['trocar_senha'] = 0`). Se esquecêssemos, o `auth.php` continuaria achando que a troca é obrigatória, mesmo com o banco já atualizado.

**2.8 Testes**

1. Entre com um aluno que ainda tem a senha provisória, em janela anônima. Deve cair direto em **Trocar senha**, com o aviso amarelo.
2. Ainda sem trocar, clique em **Início** e tente abrir `http://localhost/curso/paginas/painel.php`. Nas duas vezes você deve voltar à tela de troca.
3. Teste os erros: senha atual errada, nova com menos de 6 caracteres, confirmação diferente e nova igual à atual.
4. Troque corretamente. Deve ir ao painel com a mensagem verde.
5. No phpMyAdmin, confira que o aluno ficou com `trocar_senha = 0` e que o texto da senha mudou.
6. Saia e entre de novo: a senha provisória deve falhar e a nova deve funcionar, indo direto ao painel.
7. Como professor, clique em **Alterar senha** no topo. Deve abrir a mesma tela, sem o aviso amarelo.

## Estudo de caso 1: o aluno que não trocava a senha

**O sintoma:** ao testar o Passo 2, um aluno novo entrou em uma janela anônima com login e senha provisória e caiu direto no painel, sem passar pela tela de troca.

**A lição:** quando algo não funciona, não se reescreve tudo. Descobre-se em que ponto da corrente a informação se perde. Aqui a corrente tem três elos: o banco, o login e o `auth.php`.

**Elo 1: o banco guarda a marca?**

No phpMyAdmin, abra a tabela `usuarios` e olhe a linha do aluno.

- Se `trocar_senha` for **0**, o problema está no cadastro: o aluno foi gravado sem a marca. Confira se o `INSERT` do `aluno_cadastrar.php` termina com `VALUES (?, ?, ?, 'aluno', 1)`.
- Se for **1**, o banco está certo e o erro está no PHP. Siga para o elo 2.

**Elo 2: o login encaminha para a troca?**

Esse era o suspeito mais provável, porque o bloco do login foi substituído "no meio" de um arquivo existente. Se ainda houver um `header("Location: paginas/painel.php");` solto, sem o `if` que decide entre as duas páginas, ele manda todo mundo ao painel. O trecho correto é o do item 2.2 deste módulo.

**Elo 3: o `auth.php` bloqueia quem precisa trocar?**

É o segundo cinto de segurança: mesmo que o login mande para o painel, o `auth.php` deveria devolver o aluno à troca. Confira se o bloco da troca obrigatória existe e se está **na ordem certa**:

```php
<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/conexao.php";

session_start();

// 1) Quem não está logado volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

// 2) Senha provisória: só a página de troca de senha é permitida
if (!empty($_SESSION['trocar_senha']) && empty($pagina_troca_senha)) {
    header("Location: " . BASE_URL . "/paginas/trocar_senha.php");
    exit;
}

// 3) Facilita o uso nas páginas
$eh_professor = ($_SESSION['perfil'] === 'professor');
```

Se o bloco 2 estiver faltando, ou escrito depois do `$eh_professor`, ele não protege.

Há ainda um detalhe: o `painel.php` precisa começar com `require_once "../includes/auth.php";`. Se o painel ainda for a versão provisória do Módulo 1, que não usa o `auth.php`, ele nunca vê essa regra.

**Depois de corrigir:** feche a janela anônima e abra outra. A sessão antiga pode ter ficado guardada com os valores errados, e o teste só vale com uma sessão nova.

**O que este caso ensina**

- **Dividir para achar:** testar um elo de cada vez, do banco para a tela, mostra em minutos onde a informação se perde.
- **Duas defesas valem mais que uma:** o login encaminha para a troca, e o `auth.php` garante que ninguém escape dela. Se uma falha, a outra segura.
- **Substituir "no meio" do arquivo é risco:** ao trocar um trecho, confira se sobrou código antigo logo antes ou depois dele.
- **Teste com sessão limpa:** sempre use uma janela anônima nova após mudar regras de login.

## Passo 3: decisão de projeto e `auth.php` definitivo

**Objetivo:** permitir que o professor resete a senha, desative e exclua alunos sem perder o histórico de quem já estudou. Antes de programar, decidimos *como* guardar quem saiu do curso.

**3.1 O problema e as alternativas**

O professor queria uma forma de excluir o aluno que não continua no curso, mas também manter a relação de todos os alunos já matriculados. A primeira ideia foi mover o aluno para outra tabela. Comparamos três caminhos:

| Alternativa | Histórico (progresso e notas) | Reversibilidade | Trabalho de manutenção |
| --- | --- | --- | --- |
| Apagar o aluno | Perdido, por causa do `ON DELETE CASCADE` | Nenhuma | Baixo |
| Mover para outra tabela | Fica sem dono, ou precisa ser copiado junto | Difícil | Alto: duas tabelas para manter coerentes |
| **Desativar** (`ativo = 0`) | Preservado | Total: basta reativar | Baixo: a coluna já existe |

**A decisão:** desativar em vez de apagar. A coluna `ativo` já existe e o login já recusa quem está com `ativo = 0`. A lista de todos os matriculados sai de graça, é só a mesma tabela com um filtro (Ativos, Desativados ou Todos). Se alguém pedir uma declaração meses depois, os dados continuam lá.

**Regras que adotamos junto com a decisão**

- **Excluir de verdade** só é permitido para quem não tem nenhum progresso nem tentativa de avaliação (contas de teste, cadastros duplicados). Quem já estudou só pode ser desativado.
- **Resetar senha** gera uma senha provisória nova, mostrada uma única vez, e marca `trocar_senha = 1`.
- **Editar** nome e e-mail, porque o nome conferido pelo professor é o que sai no certificado.
- **Guardar data e motivo** da desativação.
- **Confirmação** antes de qualquer ação destrutiva, e ações feitas por formulário (POST) com token de segurança, nunca por link.
- **O professor nunca aparece nas ações**, para não se desativar sem querer.

**3.2 Novas colunas no banco**

No phpMyAdmin, com o banco `curso_treinamento` selecionado, abra a aba **SQL** e execute:

```sql
ALTER TABLE usuarios
  ADD COLUMN desativado_em DATETIME NULL AFTER ativo,
  ADD COLUMN motivo_desativacao VARCHAR(255) NULL AFTER desativado_em;
```

Confira em **Estrutura** da tabela `usuarios` se as duas colunas apareceram.

&#91;image: Figura 5. Tabela usuarios no phpMyAdmin, já com as colunas desativado\_em e motivo\_desativacao.\]

*Figura 5. Tabela `usuarios` no phpMyAdmin, já com as colunas `desativado_em` e `motivo_desativacao`.*

**3.3 O `auth.php` definitivo**

Substitua **todo** o `includes/auth.php` por este. Ele passa a conferir no banco, a cada página, se o usuário continua ativo, e ganha o token de segurança dos formulários.

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
```

**O que mudou e por quê**

- **Consulta a cada página:** antes, o `auth.php` confiava só no que estava na sessão. Um aluno já logado continuaria entrando mesmo depois de desativado. Agora a desativação vale na hora.
- **Sessão espelha o banco:** se o professor resetar a senha de um aluno que está logado, ele é levado à troca na página seguinte.
- **Token CSRF:** o `csrf_campo()` coloca um código secreto em cada formulário, e o `csrf_ok()` confere na hora de gravar. Isso impede que outro site dispare uma exclusão em nome do professor enquanto ele está logado.
- **`hash_equals`:** compara dois textos de forma segura, sem vazar informação pelo tempo de resposta.

**3.4 Aviso de acesso desativado no login**

No `index.php`, logo abaixo de `$erro = "";`, acrescente:

```php
$erro = "";

if (($_GET['erro'] ?? '') === 'desativado') {
    $erro = "Seu acesso foi desativado. Fale com o professor.";
}
```

&#91;image: Figura 6. Tela de login com o aviso exibido quando o acesso do aluno foi desativado.\]

*Figura 6. Tela de login com o aviso exibido quando o acesso do aluno foi desativado.*

Atenção à ordem: o `$erro = "";` vem **antes** do `if`. O estudo de caso 2 mostra o que acontece quando isso se perde.

## Passo 4: tela de gestão de alunos

**Objetivo:** uma página onde o professor vê todos os alunos, filtra por situação e age sobre cada um: editar, resetar senha, desativar, reativar e excluir.

**4.1 A página `alunos.php`**

Crie `paginas/alunos.php`:

```php
<?php
require_once "../includes/somente_professor.php";

$titulo_pagina = "Alunos";

// Filtro da lista: ativos (padrão), desativados ou todos
$filtro = $_GET['filtro'] ?? 'ativos';
if (!in_array($filtro, ['ativos', 'desativados', 'todos'])) {
    $filtro = 'ativos';
}

// Guarda uma mensagem na sessão e volta para a lista
function avisar($tipo, $texto, $filtro) {
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
    header("Location: alunos.php?filtro=" . $filtro);
    exit;
}

// ---------- Ações (resetar, reativar, excluir) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_ok()) {
        avisar('erro', 'Sessão expirada. Tente de novo.', $filtro);
    }

    $acao = $_POST['acao'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    // Só age sobre alunos: o professor nunca entra nessas ações
    $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE id = ? AND perfil = 'aluno'");
    $stmt->execute([$id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        avisar('erro', 'Aluno não encontrado.', $filtro);
    }

    $nome = htmlspecialchars($aluno['nome']);

    if ($acao === 'resetar') {
        // Gera uma senha provisória nova e obriga a troca no próximo login
        $nova = bin2hex(random_bytes(4));
        $hash = password_hash($nova, PASSWORD_DEFAULT);

        $pdo->prepare("UPDATE usuarios SET senha = ?, trocar_senha = 1 WHERE id = ?")
            ->execute([$hash, $id]);

        avisar('ok', "Senha de <strong>$nome</strong> redefinida. Nova senha provisória: "
            . "<strong>$nova</strong>. Anote agora: ela não será mostrada de novo.", $filtro);

    } elseif ($acao === 'reativar') {
        $pdo->prepare("UPDATE usuarios
                       SET ativo = 1, desativado_em = NULL, motivo_desativacao = NULL
                       WHERE id = ?")->execute([$id]);

        avisar('ok', "<strong>$nome</strong> foi reativado e retoma de onde parou.", $filtro);

    } elseif ($acao === 'excluir') {
        // Só exclui quem não tem nenhum progresso nem tentativa de avaliação
        $stmt = $pdo->prepare(
            "SELECT (SELECT COUNT(*) FROM progresso WHERE usuario_id = ?)
                  + (SELECT COUNT(*) FROM tentativas_avaliacao WHERE usuario_id = ?)"
        );
        $stmt->execute([$id, $id]);

        if ($stmt->fetchColumn() > 0) {
            avisar('erro', "<strong>$nome</strong> já tem histórico no curso. "
                . "Use Desativar para preservar os dados.", $filtro);
        }

        $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND perfil = 'aluno'")
            ->execute([$id]);

        avisar('ok', "<strong>$nome</strong> foi excluído definitivamente.", $filtro);
    }

    avisar('erro', 'Ação desconhecida.', $filtro);
}

// ---------- Mensagem da ação anterior (aparece uma vez só) ----------
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ---------- Lista de alunos ----------
// $where é montado só com textos fixos do próprio código, nunca com dados digitados
$where = "u.perfil = 'aluno'";
if ($filtro === 'ativos')      { $where .= " AND u.ativo = 1"; }
if ($filtro === 'desativados') { $where .= " AND u.ativo = 0"; }

$alunos = $pdo->query(
    "SELECT u.id, u.nome, u.email, u.ativo, u.criado_em,
            u.desativado_em, u.motivo_desativacao,
            (SELECT COUNT(*) FROM progresso p WHERE p.usuario_id = u.id) AS concluidos,
            (SELECT COUNT(*) FROM tentativas_avaliacao t WHERE t.usuario_id = u.id) AS tentativas
     FROM usuarios u
     WHERE $where
     ORDER BY u.nome"
)->fetchAll(PDO::FETCH_ASSOC);

$total_modulos = (int) $pdo->query("SELECT COUNT(*) FROM modulos")->fetchColumn();

require "../includes/topo.php";
?>

<h1>Alunos</h1>

<?php if ($flash): ?>
  <p class="mensagem mensagem-larga <?= $flash['tipo'] === 'ok' ? 'mensagem-ok' : 'mensagem-erro' ?>">
    <?= $flash['texto'] ?>
  </p>
<?php endif; ?>

<p><a class="btn-pequeno" href="aluno_cadastrar.php">+ Cadastrar novo aluno</a></p>

<div class="filtros">
  <a href="?filtro=ativos"      class="<?= $filtro === 'ativos' ? 'ativo' : '' ?>">Ativos</a>
  <a href="?filtro=desativados" class="<?= $filtro === 'desativados' ? 'ativo' : '' ?>">Desativados</a>
  <a href="?filtro=todos"       class="<?= $filtro === 'todos' ? 'ativo' : '' ?>">Todos</a>
  <span>(<?= count($alunos) ?> na lista)</span>
</div>

<?php if (count($alunos) === 0): ?>
  <p>Nenhum aluno nesta lista.</p>
<?php else: ?>
  <div class="tabela-rolagem">
  <table class="tabela">
    <thead>
      <tr>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Progresso</th>
        <th>Situação</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($alunos as $a): ?>
        <tr class="<?= $a['ativo'] ? '' : 'linha-inativa' ?>">
          <td><?= htmlspecialchars($a['nome']) ?></td>
          <td><?= htmlspecialchars($a['email']) ?></td>
          <td><?= $a['concluidos'] ?> de <?= $total_modulos ?></td>
          <td>
            <?php if ($a['ativo']): ?>
              <span class="etiqueta etiqueta-ativo">Ativo</span>
            <?php else: ?>
              <span class="etiqueta etiqueta-inativo">Desativado</span>
              <?php if ($a['desativado_em']): ?>
                <br><small>em <?= date('d/m/Y', strtotime($a['desativado_em'])) ?></small>
              <?php endif; ?>
              <?php if ($a['motivo_desativacao']): ?>
                <br><small><?= htmlspecialchars($a['motivo_desativacao']) ?></small>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td class="acoes">

            <a class="btn-pequeno btn-neutro" href="aluno_editar.php?id=<?= $a['id'] ?>">Editar</a>

            <form method="post" action="alunos.php?filtro=<?= $filtro ?>" class="form-inline"
                  data-confirma="<?= htmlspecialchars('Gerar uma nova senha provisória para ' . $a['nome'] . '?') ?>">
              <?= csrf_campo() ?>
              <input type="hidden" name="acao" value="resetar">
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <button type="submit" class="btn-pequeno btn-aviso">Resetar senha</button>
            </form>

            <?php if ($a['ativo']): ?>
              <a class="btn-pequeno btn-aviso" href="aluno_desativar.php?id=<?= $a['id'] ?>">Desativar</a>
            <?php else: ?>
              <form method="post" action="alunos.php?filtro=<?= $filtro ?>" class="form-inline"
                    data-confirma="<?= htmlspecialchars('Reativar ' . $a['nome'] . '?') ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="reativar">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" class="btn-pequeno">Reativar</button>
              </form>
            <?php endif; ?>

            <?php if ($a['concluidos'] == 0 && $a['tentativas'] == 0): ?>
              <form method="post" action="alunos.php?filtro=<?= $filtro ?>" class="form-inline"
                    data-confirma="<?= htmlspecialchars('EXCLUIR ' . $a['nome'] . ' definitivamente? Isso não pode ser desfeito.') ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" class="btn-pequeno btn-perigo">Excluir</button>
              </form>
            <?php endif; ?>

          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
<?php endif; ?>

<script>
// Pede confirmação antes de enviar qualquer formulário que tenha data-confirma
document.querySelectorAll('form[data-confirma]').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!confirm(form.dataset.confirma)) {
      e.preventDefault();
    }
  });
});
</script>

<?php require "../includes/rodape.php"; ?>
```

&#91;image: Figura 7. Tela Alunos, com filtro, progresso e botões de ação.\]

*Figura 7. Tela Alunos, com filtro, progresso e botões de ação.*

**4.2 Confirmação de desativação, com motivo**

Crie `paginas/aluno_desativar.php`:

```php
<?php
require_once "../includes/somente_professor.php";

$titulo_pagina = "Desativar aluno";
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE id = ? AND perfil = 'aluno'");
$stmt->execute([$id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'texto' => 'Aluno não encontrado.'];
    header("Location: alunos.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $erro = "Sessão expirada. Tente de novo.";
    } else {
        $motivo = mb_substr(trim($_POST['motivo'] ?? ''), 0, 255);

        $pdo->prepare("UPDATE usuarios
                       SET ativo = 0, desativado_em = NOW(), motivo_desativacao = ?
                       WHERE id = ? AND perfil = 'aluno'")
            ->execute([$motivo === '' ? null : $motivo, $id]);

        $_SESSION['flash'] = [
            'tipo'  => 'ok',
            'texto' => '<strong>' . htmlspecialchars($aluno['nome']) . '</strong> foi desativado. '
                     . 'O histórico dele foi preservado e você pode reativá-lo quando quiser.'
        ];
        header("Location: alunos.php");
        exit;
    }
}

require "../includes/topo.php";
?>

<h1>Desativar aluno</h1>

<?php if ($erro): ?>
  <p class="mensagem mensagem-erro"><?= $erro ?></p>
<?php endif; ?>

<form method="post" class="formulario">
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= $aluno['id'] ?>">

  <p>
    Você vai desativar <strong><?= htmlspecialchars($aluno['nome']) ?></strong>
    (<?= htmlspecialchars($aluno['email']) ?>).
    Ele não conseguirá entrar no curso, mas o progresso e as notas ficam guardados.
  </p>

  <label for="motivo">Motivo (opcional)</label>
  <input type="text" id="motivo" name="motivo" maxlength="255"
         placeholder="Ex.: desistiu do curso">

  <button type="submit">Confirmar desativação</button>
  <a href="alunos.php" class="link-cancelar">Cancelar</a>
</form>

<?php require "../includes/rodape.php"; ?>
```

**4.3 Editar nome e e-mail**

Crie `paginas/aluno_editar.php`:

```php
<?php
require_once "../includes/somente_professor.php";

$titulo_pagina = "Editar aluno";
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE id = ? AND perfil = 'aluno'");
$stmt->execute([$id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'texto' => 'Aluno não encontrado.'];
    header("Location: alunos.php");
    exit;
}

$erro  = "";
$nome  = $aluno['nome'];
$email = $aluno['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = mb_strtolower(trim($_POST['email'] ?? ''));

    if (!csrf_ok()) {
        $erro = "Sessão expirada. Tente de novo.";

    } elseif ($nome === '' || $email === '') {
        $erro = "Preencha todos os campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";

    } else {
        // O e-mail não pode ser de OUTRO usuário (o do próprio aluno pode repetir)
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ?");
        $stmt->execute([$email, $id]);

        if ($stmt->fetch()) {
            $erro = "Já existe outro usuário com esse e-mail.";
        } else {
            $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ? AND perfil = 'aluno'")
                ->execute([$nome, $email, $id]);

            $_SESSION['flash'] = [
                'tipo'  => 'ok',
                'texto' => 'Dados de <strong>' . htmlspecialchars($nome) . '</strong> atualizados.'
            ];
            header("Location: alunos.php");
            exit;
        }
    }
}

require "../includes/topo.php";
?>

<h1>Editar aluno</h1>

<?php if ($erro): ?>
  <p class="mensagem mensagem-erro"><?= $erro ?></p>
<?php endif; ?>

<form method="post" class="formulario">
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= $aluno['id'] ?>">

  <label for="nome">Nome completo (como sairá no certificado)</label>
  <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>

  <label for="email">E-mail (é o login)</label>
  <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

  <button type="submit">Salvar alterações</button>
  <a href="alunos.php" class="link-cancelar">Cancelar</a>
</form>

<?php require "../includes/rodape.php"; ?>
```

**4.4 Ajuste no menu**

Em `includes/topo.php`, troque o link "Cadastrar alunos" por este. O cadastro passa a ser acessado pelo botão dentro da tela Alunos.

```php
<?php if ($eh_professor): ?>
  <a href="<?= BASE_URL ?>/paginas/alunos.php">Alunos</a>
<?php endif; ?>
```

Se o seu `aluno_cadastrar.php` ainda tiver a lista de alunos no final (a consulta `$alunos = $pdo->query(...)` e o bloco `<h2>Alunos cadastrados`), remova os dois. A lista agora mora só em `alunos.php`.

**4.5 CSS**

Acrescente no final do `assets/css/estilo.css`:

```css
/* ---------- Gestão de alunos ---------- */
.mensagem-larga { max-width: none; }

.filtros { margin: 15px 0; }
.filtros a {
  margin-right: 14px;
  color: #1e5fa8;
  text-decoration: none;
  padding-bottom: 3px;
}
.filtros a.ativo { font-weight: bold; border-bottom: 2px solid #1e5fa8; }

.tabela-rolagem { overflow-x: auto; }
.acoes { white-space: nowrap; }
.form-inline { display: inline; }

.btn-pequeno {
  display: inline-block;
  padding: 5px 9px;
  font-size: .8rem;
  border: 0;
  border-radius: 4px;
  cursor: pointer;
  background: #1e5fa8;
  color: #fff;
  text-decoration: none;
}
.btn-aviso  { background: #c77700; }
.btn-perigo { background: #b00020; }
.btn-neutro { background: #607d8b; }

.etiqueta {
  padding: 2px 8px;
  border-radius: 10px;
  font-size: .8rem;
}
.etiqueta-ativo   { background: #e3f5e8; color: #1b5e30; }
.etiqueta-inativo { background: #eceff1; color: #546e7a; }

.linha-inativa td { color: #78909c; }

.link-cancelar { margin-left: 15px; color: #607d8b; }
```

**4.6 Como as peças funcionam**

- **`avisar()` e `$_SESSION['flash']`:** depois de uma ação, o PHP guarda a mensagem na sessão e redireciona. Na página seguinte ele lê a mensagem e a apaga. Se o professor apertar F5, a ação não se repete. Isso é essencial no reset, onde a senha só aparece uma vez.
- **`WHERE id = ? AND perfil = 'aluno'`:** em toda ação, mesmo que alguém forje o número do `id`, o professor nunca é afetado.
- **`data-confirma` e o pequeno JavaScript:** um único script serve para todos os botões. Cada formulário declara a pergunta que quer fazer, e o script mostra o `confirm()` antes de enviar.
- **Botão Excluir condicional:** ele só é desenhado se `concluidos` e `tentativas` forem 0, e o PHP confere de novo na hora de apagar. A regra vale mesmo que alguém tente burlar a tela.
- **Subconsultas no `SELECT`:** `(SELECT COUNT(*) ...)` calcula, em cada linha, quantos módulos o aluno concluiu e quantas tentativas fez, em uma única consulta.
- **`$where` montado com textos fixos:** o filtro só aceita três valores conhecidos (`in_array`), então nada digitado pelo usuário entra no SQL.

**4.7 Testes**

1. Entre como professor. O menu deve mostrar **Alunos**. Confira as colunas, o filtro e os botões.
2. **Resetar senha:** clique, confirme e veja a mensagem verde com a senha nova. Aperte F5 e confirme que ela não gera outra. Entre com esse aluno em janela anônima: a senha antiga deve falhar, a nova deve funcionar e levar à troca obrigatória.
3. **Editar:** mude o nome de um aluno e confira na lista. Tente usar o e-mail de outro aluno e veja o erro.
4. **Desativar:** desative um aluno com um motivo. Ele deve sair de Ativos e aparecer em Desativados, com data e motivo. Se ele estiver logado em outra janela, clique em qualquer link ali: deve ir ao login com "Seu acesso foi desativado".
5. **Reativar:** reative o mesmo aluno e confira que ele volta a entrar.
6. **Excluir:** o botão só aparece para alunos sem histórico. Exclua um aluno de teste e confirme que sumiu do banco. Para testar a trava, insira manualmente uma linha em `progresso` para um aluno e veja o botão Excluir desaparecer da linha dele.
7. **Segurança:** como aluno, cole `http://localhost/curso/paginas/alunos.php` na barra de endereço. Ele deve voltar ao painel.

## Estudo de caso 2: o aviso "Undefined variable $erro"

**O sintoma:** depois do Passo 3, ao abrir a tela de login normalmente, apareceu uma mensagem em negrito acima do formulário:

```
Warning: Undefined variable $erro in C:\xampp\htdocs\curso\index.php on line 57
```

**Como ler a mensagem**

Uma mensagem do PHP tem sempre as mesmas partes. Aprender a lê-las resolve a maioria dos problemas:

| Trecho | O que significa |
| --- | --- |
| `Warning` | Nível do aviso. Não derruba a página, mas indica algo errado |
| `Undefined variable $erro` | O problema: a variável `$erro` foi usada sem nunca ter sido criada |
| `index.php` | O arquivo onde aconteceu |
| `line 57` | A linha onde a variável foi lida |

**O diagnóstico**

A linha 57 era o `<?php if ($erro): ?>` do formulário, que lê a variável `$erro`. Ela só existe se alguém a criou antes. O aviso aparece quando a página é aberta normalmente, e não aparece quando a senha é enviada, o que dá a pista: algo cria a variável só em alguns caminhos.

O trecho novo do Passo 3.4 tinha entrado **no lugar** do `$erro = "";`, e não abaixo dele. Sem o `?erro=desativado` na barra de endereço, o `if` não roda, e a variável nunca nasce.

**O código com defeito**

```php
// $erro = "";  <- esta linha sumiu

if (($_GET['erro'] ?? '') === 'desativado') {
    $erro = "Seu acesso foi desativado. Fale com o professor.";
}
```

**A correção**

O `$erro = "";` precisa existir **antes** de qualquer lugar que use a variável:

```php
$erro = "";

if (($_GET['erro'] ?? '') === 'desativado') {
    $erro = "Seu acesso foi desativado. Fale com o professor.";
}
```

Confira também que esse `$erro = "";` está antes do bloco `if ($_SERVER['REQUEST_METHOD'] === 'POST')`, e não depois.

**O que este caso ensina**

- **Toda variável precisa nascer antes de ser lida.** Dar um valor inicial (`$erro = "";`) é o jeito de garantir que ela exista em todos os caminhos do programa.
- **A mensagem aponta o arquivo e a linha.** Comece sempre por ali, antes de mexer em qualquer outra parte.
- **Mudou algo e apareceu um aviso novo? A causa está na última alteração.** Revise só o que foi alterado.
- **Avisos e erros são aliados.** Em desenvolvimento, eles ficam visíveis de propósito. Em um servidor online, a exibição de erros deve ficar desligada, para o visitante não ver caminhos internos do sistema.

## Erros comuns e boas práticas

| Erro ou sintoma | Causa mais provável | Como resolver |
| --- | --- | --- |
| Aluno novo cai no painel sem trocar a senha | `trocar_senha` gravado como 0, `header` solto no login ou bloco ausente no `auth.php` | Siga os três elos do estudo de caso 1 |
| `Undefined variable $erro` | A variável foi usada sem receber valor inicial | Coloque `$erro = "";` antes de qualquer uso |
| Loop de redirecionamento na tela de troca de senha | A página não declara `$pagina_troca_senha = true` antes de chamar o `auth.php` | Declare a variável na primeira linha do arquivo |
| `Unknown column 'desativado_em'` | O `ALTER TABLE` do Passo 3.2 não foi executado | Execute o SQL no phpMyAdmin e confira a Estrutura da tabela |
| `Call to undefined function csrf_campo()` | A página não carrega o `auth.php` definitivo | Confira se a página usa `somente_professor.php` e se o `auth.php` foi substituído por inteiro |
| "Sessão expirada. Tente de novo." | Token CSRF ausente no formulário ou sessão nova | Confira se o formulário tem `<?= csrf_campo() ?>` e recarregue a página |
| Botão Excluir não aparece | O aluno já tem progresso ou tentativa de avaliação | Comportamento esperado: use Desativar |
| Aluno desativado continua entrando | O `auth.php` antigo, que não consulta o banco | Substitua pelo `auth.php` definitivo do Passo 3.3 |
| `headers already sent` | Algo foi escrito na tela antes de um `header(...)` | Mantenha todo o processamento antes do `require` do topo |
| Os botões de ação não pedem confirmação | O `<script>` do final de `alunos.php` não foi incluído | Confira o trecho com `data-confirma` antes do `rodape.php` |

**Boas práticas de segurança que aplicamos neste módulo**

- **Senhas sempre criptografadas** com `password_hash` e conferidas com `password_verify`. Nunca guardadas em texto puro.
- **Senha provisória obrigatória de ser trocada**, e exibida uma única vez ao professor.
- **Consultas com `?`** (prepared statements) em todo o código, nunca montando SQL com texto digitado.
- **`htmlspecialchars`** em todo texto que vem do banco ou do usuário e é mostrado na tela.
- **Verificação no PHP**, e não só na tela: esconder um botão não impede o acesso, então cada página confere o perfil por conta própria.
- **Ações destrutivas por POST com token CSRF**, nunca por link.
- **Validar de novo no servidor** o que a tela já esconde, como o botão Excluir.
- **Desativar antes de apagar:** quando houver dúvida, prefira o que pode ser desfeito.

**Hábitos que evitam dor de cabeça**

- Teste depois de cada arquivo criado, não só no final.
- Altere uma coisa por vez. Se quebrar, você sabe onde olhar.
- Ao substituir um trecho, confira se não sobrou código antigo ao lado.
- Use sempre uma janela anônima nova para testar login.
- Guarde uma cópia da pasta `curso` antes de mudanças grandes.

## Checklist final do módulo

Marque cada item depois de testar. Se todos estiverem marcados, o ciclo de acesso do curso está completo.

- [ ] `includes/somente_professor.php` criado e funcionando
- [ ] O professor cadastra alunos e o aluno nasce com `trocar_senha = 1`
- [ ] O aluno com senha provisória é levado à tela de troca e não acessa mais nada antes de trocar
- [ ] Após a troca, o painel mostra "Senha alterada com sucesso!"
- [ ] O link **Alterar senha** funciona para aluno e professor
- [ ] As colunas `desativado_em` e `motivo_desativacao` existem na tabela `usuarios`
- [ ] O `auth.php` definitivo está em uso e encerra o login de quem foi desativado
- [ ] A tela **Alunos** lista, filtra e mostra o progresso de cada aluno
- [ ] Resetar senha gera nova senha provisória, mostrada uma só vez
- [ ] Desativar e reativar funcionam, com data e motivo
- [ ] Excluir só aparece para alunos sem histórico
- [ ] Editar nome e e-mail funciona e recusa e-mail repetido
- [ ] Um aluno não consegue abrir as páginas do professor pela barra de endereço
- [ ] A tela de login não mostra nenhum aviso `Warning`

**O que vem no Módulo 3**

Com o acesso resolvido, o próximo módulo constrói o coração do curso: o painel com os cards da Introdução, dos Módulos 1 a 7 e da Conclusão, a barra de progresso, o bloqueio dos módulos ainda não concluídos e o menu suspenso para o aluno revisitar o que já estudou.
