// JavaScript das páginas de conteúdo e atividades.
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

    // Atualiza "x de y" e libera o botão Concluir quando todas foram conferidas
    function atualizarContador() {
        if (!contador) return;
        var feitas = document.querySelectorAll('form.questao.respondida').length;
        contador.textContent = feitas + ' de ' + forms.length;
        if (btnConcluir && feitas >= forms.length) btnConcluir.disabled = false;
    }

    // Pinta as alternativas e mostra o comentário
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
        // As alternativas são embaralhadas: a letra que o aluno vê (A, B, C, D) é a da posição na tela,
        // não a do banco. Lemos a letra do rótulo da alternativa certa.
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
            e.preventDefault();                       // não recarrega a página
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
