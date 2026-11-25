<?php
require_once '/var/www/stockweb/includes/relatorios_visual.php';

['tipo' => $tipo, 'titulos' => $titulos, 'campos' => $campos, 'filtros' => $filtros,
 'resultado' => $resultado, 'erro' => $erro] = relatorioPreparar($pdo, $_GET, true);
$geradoEm = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php stockwebHead($titulos[$tipo], true); ?>
<body class="report-print">
    <main class="container py-4">
        <header class="border-bottom pb-3 mb-3">
            <div class="sw-brand mb-2"><?php stockwebMarca(); ?></div>
            <h1 class="h2 fw-bold"><?= relatorioEscapar($titulos[$tipo]) ?></h1>
            <p class="mb-0">Gerado em <?= $geradoEm->format('d/m/Y H:i') ?> (horário de Brasília)</p>
        </header>
        <div class="d-print-none d-flex flex-wrap gap-2 mb-4">
            <a class="btn btn-outline-secondary" href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $tipo, $filtros)) ?>">Voltar ao relatório</a>
            <?php if ($resultado !== null && !$resultado['excedeu']): ?>
                <button class="btn btn-primary" type="button" onclick="window.print()">Imprimir / Salvar como PDF</button>
            <?php endif; ?>
        </div>

        <?php if ($erro !== null): ?>
            <?php stockwebAviso('danger', $erro); ?>
        <?php else: ?>
            <dl class="row mb-3">
                <?php foreach (relatorioDescricaoFiltros($tipo, $campos, $filtros, $resultado['opcoes']) as $rotulo => $valor): ?>
                    <dt class="col-sm-4 col-md-3"><?= relatorioEscapar($rotulo) ?></dt>
                    <dd class="col-sm-8 col-md-9"><?= relatorioEscapar($valor) ?></dd>
                <?php endforeach; ?>
            </dl>

            <?php if ($resultado['excedeu']): ?>
                <div class="alert alert-warning" role="alert">
                    O resultado contém <?= relatorioNumero($resultado['totais']['registros']) ?> registros.
                    Restrinja os filtros para imprimir até <?= relatorioNumero(RELATORIO_LIMITE_IMPRESSAO) ?> registros.
                </div>
            <?php else: ?>
                <?php if ($tipo === 'vendas'): ?>
                    <p>Unidades vendidas, faturamento e ticket médio consideram vendas concluídas.</p>
                <?php endif; ?>
                <?php relatorioIndicadores($tipo, $resultado['totais']); ?>
                <?php if ($tipo !== 'resumo'): ?>
                    <?php relatorioTabela($tipo, $resultado['linhas'], isset($filtros['produto_id']), true); ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>
