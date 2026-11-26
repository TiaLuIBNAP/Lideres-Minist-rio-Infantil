-- =====================================================================
-- MÓDULO 4 DA APOSTILA: conteúdo, atividades, avaliação e conclusão
-- Rode no phpMyAdmin (banco curso_treinamento), aba SQL.
-- =====================================================================

-- 1) Respostas dos alunos nos exercícios (uma linha por aluno e questão)
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

-- 2) Avaliação (1 a 5 estrelas) que o aluno dá a cada módulo
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

-- 3) Texto da Introdução (HTML guardado na coluna conteudo)
UPDATE modulos SET conteudo = '<p>Baseado nos ensinamentos de Jesus é nossa missão e responsabilidade bíblica conduzir as crianças ao conhecimento de Deus. Trabalhar com o ministério infantil não é apenas um serviço educacional, mas uma resposta direta ao chamado de Jesus de ir e pregar o Evangelho (<em>Ide</em>).</p>
<p>As atividades desenvolvidas na sala infantil não visam isolar as crianças do culto principal, mas oferecer um ambiente pedagogicamente adaptado, com uma linguagem acessível onde elas possam compreender a Palavra de Deus, ser edificadas e ter um encontro real com Jesus. Através da oração e do ensino dedicado, atuamos como os intermediários mencionados nas Escrituras:</p>
<blockquote class="destaque"><strong>aqueles que apresentam as crianças a Jesus e revelam o amor de Deus aos seus corações.</strong></blockquote>
<blockquote class="versiculo"><p>“Depois, trouxeram crianças a Jesus, para que lhes impusesse as mãos e orasse por elas, mas os discípulos os repreendiam. Então, Jesus disse: — Deixem vir a mim as crianças e não as impeçam, pois o reino dos céus pertence aos que são semelhantes a elas.”</p><cite>Mateus 19:13,14</cite></blockquote>'
WHERE ordem = 1;

-- 4) Vídeo do YouTube da Introdução: troque pelo link real e rode esta linha
-- UPDATE modulos SET video_url = 'https://www.youtube.com/watch?v=COLE_O_CODIGO_AQUI' WHERE ordem = 1;

-- 5) As 5 questões da Introdução (apagar antes evita duplicar se rodar de novo)
DELETE FROM questoes WHERE tipo = 'exercicio' AND modulo_id = (SELECT id FROM modulos WHERE ordem = 1);

INSERT INTO questoes (modulo_id, tipo, enunciado, alt_a, alt_b, alt_c, alt_d, correta, explicacao) VALUES
((SELECT id FROM modulos WHERE ordem = 1), 'exercicio',
 'Qual é o propósito central do Ministério Infantil apresentado na apostila?',
 'Manter as crianças ocupadas durante o culto',
 'Conectar as crianças a Jesus e ensinar a Palavra',
 'Substituir a responsabilidade da família',
 'Realizar atividades recreativas sem ensino bíblico',
 'B', 'A introdução apresenta como missão conectar as crianças a Jesus; a recreação pode apoiar o ensino, mas não substitui o propósito espiritual.'),
((SELECT id FROM modulos WHERE ordem = 1), 'exercicio',
 'O que deve orientar o trabalho com as crianças?',
 'A preferência pessoal do líder',
 'A quantidade de brincadeiras',
 'Os ensinamentos de Jesus e a Palavra de Deus',
 'A busca por terminar a aula rapidamente',
 'C', 'O curso coloca os ensinamentos de Jesus e a Palavra de Deus como fundamento do ministério.'),
((SELECT id FROM modulos WHERE ordem = 1), 'exercicio',
 'O líder deve entender seu serviço como:',
 'Uma tarefa sem planejamento',
 'Um chamado e uma responsabilidade diante de Deus',
 'Uma atividade para preencher o tempo',
 'Uma função que depende apenas de talento natural',
 'B', 'O material trata o serviço com as crianças como chamado e responsabilidade, que exigem dedicação.'),
((SELECT id FROM modulos WHERE ordem = 1), 'exercicio',
 'Qual atitude combina com a proposta da apostila?',
 'Ensinar sem considerar a criança',
 'Priorizar somente o entretenimento',
 'Ensinar a verdade bíblica de modo compreensível e aplicável',
 'Evitar perguntas e participação',
 'C', 'A verdade bíblica deve ser comunicada de modo que a criança compreenda e leve para a vida.'),
((SELECT id FROM modulos WHERE ordem = 1), 'exercicio',
 'O culto no Ministério Infantil deve buscar:',
 'Apenas silêncio absoluto',
 'A participação e o aprendizado da criança sobre Jesus',
 'Somente memorizar nomes bíblicos',
 'Preencher todo o tempo com atividades',
 'B', 'A meta é ensinar a Palavra e conduzir a criança a uma relação com Jesus, com participação significativa.');
