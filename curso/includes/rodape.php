</main>
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
</body>
</html>