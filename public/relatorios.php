<?php
require_once '/var/www/stockweb/includes/relatorios_visual.php';
require_once '/var/www/stockweb/includes/layout.php';

$relatorio = relatorioPreparar($pdo, $_GET);
['tipo' => $tipo, 'titulos' => $titulos, 'campos' => $campos, 'filtros' => $filtros,
 'resultado' => $resultado, 'erro' => $erro] = $relatorio;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios | StockWeb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php stockwebLayoutEstilos(); ?>
</head>
<body style="background-color: #f4f6fa">
    <?php stockwebLayoutAbrir('relatorios'); ?>
    <main class="container py-4 py-lg-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <h1 class="fw-bold mb-0">Relatórios</h1>
            <?php if ($resultado !== null): ?>
                <a href="<?= relatorioEscapar(relatorioUrl('relatorio_imprimir.php', $tipo, $filtros)) ?>"
                   class="btn btn-outline-primary" target="_blank" rel="noopener">
                    <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Exportar PDF
                </a>
            <?php endif; ?>
        </div>
        <nav class="nav nav-pills flex-wrap gap-2 mb-4" aria-label="Tipos de relatório">
            <?php foreach ($titulos as $chave => $titulo): ?>
                <a href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $chave)) ?>"
                   class="nav-link<?= $chave === $tipo ? ' active' : '' ?>"<?= $chave === $tipo ? ' aria-current="page"' : '' ?>>
                    <?= relatorioEscapar($titulo) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($erro !== null): ?>
            <div class="alert alert-danger" role="alert"><?= relatorioEscapar($erro) ?></div>
            <a class="btn btn-outline-secondary" href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $tipo)) ?>">Limpar filtros</a>
        <?php else: ?>
            <form method="GET" class="card border-0 rounded-4 shadow-sm p-3 mb-4">
                <input type="hidden" name="relatorio" value="<?= relatorioEscapar($tipo) ?>">
                <div class="row g-3 align-items-end">
                    <?php foreach ($campos as $nome => [$rotulo, $origem]): ?>
                        <div class="col-sm-6 col-xl">
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
                    <button class="btn btn-primary" type="submit">Filtrar</button>
                    <a class="btn btn-outline-secondary" href="<?= relatorioEscapar(relatorioUrl('relatorios.php', $tipo)) ?>">Limpar</a>
                </div>
            </form>

            <h2 class="h4 mb-3"><?= relatorioEscapar($titulos[$tipo]) ?></h2>
            <?php if ($tipo === 'vendas'): ?>
                <p class="text-secondary">Unidades vendidas, faturamento e ticket médio consideram vendas concluídas.</p>
            <?php endif; ?>
            <?php relatorioIndicadores($tipo, $resultado['totais']); ?>
            <?php if ($tipo !== 'resumo'): ?>
                <div class="card border-0 rounded-4 shadow-sm">
                    <?php relatorioTabela($tipo, $resultado['linhas'], isset($filtros['produto_id'])); ?>
                </div>
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
    </main>
    <?php stockwebLayoutFechar(); ?>
</body>
</html>
