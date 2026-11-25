<?php
require_once '/var/www/stockweb/includes/relatorios_visual.php';

$relatorio = relatorioPreparar($pdo, $_GET);
['tipo' => $tipo, 'titulos' => $titulos, 'campos' => $campos, 'filtros' => $filtros,
 'resultado' => $resultado, 'erro' => $erro] = $relatorio;

stockwebLayoutAbrir('relatorios', 'Relatórios');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Relatórios', 'Consulte indicadores e filtre os registros para análise ou exportação.'); ?>
    <?php if ($resultado !== null): ?>
        <a href="<?= relatorioEscapar(relatorioUrl('relatorio_imprimir.php', $tipo, $filtros)) ?>"
           class="btn btn-outline-primary" target="_blank" rel="noopener">
            <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Exportar PDF
        </a>
    <?php endif; ?>
</header>
<nav class="nav nav-pills sw-report-tabs sw-panel" aria-label="Tipos de relatório">
    <?php foreach ($titulos as $chave => $titulo): ?>
        <a href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $chave)) ?>"
           class="nav-link<?= $chave === $tipo ? ' active' : '' ?>"<?= $chave === $tipo ? ' aria-current="page"' : '' ?>>
            <?= relatorioEscapar($titulo) ?>
        </a>
    <?php endforeach; ?>
</nav>

<?php if ($erro !== null): ?>
    <?php stockwebAviso('danger', $erro); ?>
    <a class="btn btn-outline-secondary" href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $tipo)) ?>">Limpar filtros</a>
<?php else: ?>
    <form method="GET" class="sw-panel sw-filter">
        <div class="sw-form-section">
            <h2 class="sw-form-kicker"><i class="bi bi-sliders" aria-hidden="true"></i> Filtros</h2>
        </div>
        <input type="hidden" name="relatorio" value="<?= relatorioEscapar($tipo) ?>">
        <div class="row g-3 align-items-end">
            <?php foreach ($campos as $nome => [$rotulo, $origem]): ?>
                <div class="col-sm-6 col-xl-4 col-xxl">
                    <label class="form-label" for="filtro-<?= relatorioEscapar($nome) ?>"><?= relatorioEscapar($rotulo) ?></label>
                    <?php if ($origem === null): ?>
                        <input type="date" class="form-control" id="filtro-<?= relatorioEscapar($nome) ?>"
                               name="<?= relatorioEscapar($nome) ?>" value="<?= relatorioEscapar($filtros[$nome] ?? '') ?>">
                    <?php else: ?>
                        <select class="form-select" id="filtro-<?= relatorioEscapar($nome) ?>" name="<?= relatorioEscapar($nome) ?>">
                            <option value="">Todos</option>
                            <?php foreach ($resultado['opcoes'][$nome] as $valor => $opcao): ?>
                                <option value="<?= relatorioEscapar($valor) ?>"<?= (string) $valor === (string) ($filtros[$nome] ?? '') ? ' selected' : '' ?>>
                                    <?= relatorioEscapar($opcao) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
            <a class="btn btn-outline-secondary" href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $tipo)) ?>">Limpar</a>
        </div>
    </form>

    <h2 class="sw-section-title mb-3"><?= relatorioEscapar($titulos[$tipo]) ?></h2>
    <?php if ($tipo === 'vendas'): ?>
        <p class="text-secondary">Unidades vendidas, faturamento e ticket médio consideram vendas concluídas.</p>
    <?php endif; ?>
    <?php relatorioIndicadores($tipo, $resultado['totais']); ?>
    <?php if ($tipo !== 'resumo'): ?>
        <section class="sw-panel" aria-label="Resultados do relatório">
            <?php stockwebTabelaCabecalho('Resultados', (int) $resultado['totais']['registros']); ?>
            <?php relatorioTabela($tipo, $resultado['linhas'], isset($filtros['produto_id'])); ?>
        </section>
        <?php if ($resultado['paginas'] > 1): ?>
            <?php ['pagina' => $pagina, 'paginas' => $paginas] = $resultado; ?>
            <nav class="d-flex flex-wrap gap-3 justify-content-between align-items-center mt-3" aria-label="Páginas do relatório">
                <span>Página <?= $pagina ?> de <?= $paginas ?></span>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($pagina > 1): ?>
                        <a class="btn btn-outline-primary btn-sm" href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $tipo, $filtros, $pagina - 1)) ?>">Anterior</a>
                    <?php endif; ?>
                    <?php if ($pagina < $paginas): ?>
                        <a class="btn btn-outline-primary btn-sm" href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $tipo, $filtros, $pagina + 1)) ?>">Próxima</a>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
<?php stockwebLayoutFechar(); ?>
