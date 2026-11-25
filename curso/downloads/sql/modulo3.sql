-- 1) CONFIRA ANTES: a Introdução deve ter ordem = 1 e a Conclusão ordem = 9
SELECT id, ordem, titulo FROM modulos ORDER BY ordem;

-- 2) Novas colunas nos módulos
ALTER TABLE modulos
  ADD COLUMN descricao VARCHAR(300) NULL AFTER titulo,
  ADD COLUMN imagem VARCHAR(100) NULL AFTER descricao;

-- 3) Textos e imagens de cada módulo (pela coluna ordem)
UPDATE modulos SET titulo='Conectando as Crianças a Jesus', imagem='introducao.jpg',
  descricao='Por que servir no Ministério Infantil é uma resposta direta ao chamado de Jesus: levar as crianças até Ele.' WHERE ordem=1;
UPDATE modulos SET titulo='Os atributos de um verdadeiro líder', imagem='modulo1.jpg',
  descricao='Cinco atributos que edificam a liderança: chamado de Deus, reverência, intercessão, sabedoria para ensinar e temperança.' WHERE ordem=2;
UPDATE modulos SET titulo='Didática guiada pelo Espírito Santo', imagem='modulo2.jpg',
  descricao='Cinco fundamentos para quem ensina: Palavra, vida espiritual, conhecimento bíblico, compromisso e intimidade com Deus.' WHERE ordem=3;
UPDATE modulos SET titulo='Preparação do ambiente e boas práticas', imagem='modulo3.jpg',
  descricao='Prepare o ambiente, o coração e a aula: chegue antes, acolha as crianças e conduza com decência e ordem.' WHERE ordem=4;
UPDATE modulos SET titulo='Técnicas e estratégias — Parte 1', imagem='modulo4.jpg',
  descricao='Como conduzir a criança e manter sua atenção: liderança, domínio de turma, tempo de atenção e objetivo da aula.' WHERE ordem=5;
UPDATE modulos SET titulo='Técnicas e estratégias — Parte 2', imagem='modulo5.jpg',
  descricao='Conte histórias de forma atrativa, recupere a atenção, fuja da monotonia e dependa de Deus em cada aula.' WHERE ordem=6;
UPDATE modulos SET titulo='Como evitar a indisciplina', imagem='modulo6.jpg',
  descricao='Entenda a raiz da indisciplina e aprenda a preveni-la com objetivos claros, intencionalidade e envolvimento das crianças.' WHERE ordem=7;
UPDATE modulos SET titulo='Autoridade espiritual', imagem='modulo7.jpg',
  descricao='O que sustenta o líder: oração, jejum e obediência, vivendo com coerência aquilo que se ensina.' WHERE ordem=8;
UPDATE modulos SET titulo='Conclusão', imagem='conclusao.jpg',
  descricao='Encerramento da jornada: revise o que aprendeu e prepare-se para a avaliação final.' WHERE ordem=9;
