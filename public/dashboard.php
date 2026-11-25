<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '/var/www/stockweb/config/database.php';
require_once '/var/www/stockweb/includes/layout.php';

$nome = (string) ($_SESSION['usuario_nome'] ?? 'Usuário');
$indicadores = $pdo->query("
    SELECT COUNT(*) AS produtos, COALESCE(SUM(quantidade), 0) AS unidades,
           COUNT(CASE WHEN quantidade <= estoque_minimo THEN 1 END) AS estoque_baixo,
           (SELECT COUNT(*) FROM clientes) AS clientes,
           (SELECT COUNT(*) FROM vendas WHERE status = 'CONCLUIDA') AS vendas,
           CURRENT_DATE() AS hoje
    FROM produtos
    WHERE ativo = 1
")->fetch();

$hoje = new DateTimeImmutable($indicadores['hoje']);
$inicio = $hoje->modify('-6 days');
$fim = $hoje->modify('+1 day');
$stmt = $pdo->prepare("
    SELECT DATE(data_venda) AS dia, COUNT(*) AS vendas
    FROM vendas
    WHERE status = 'CONCLUIDA' AND data_venda >= ? AND data_venda < ?
    GROUP BY DATE(data_venda)
");
$stmt->execute([$inicio->format('Y-m-d'), $fim->format('Y-m-d')]);
$vendasPorDia = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$serie = [];
foreach (new DatePeriod($inicio, new DateInterval('P1D'), $fim) as $dia) {
    $serie[$dia->format('d/m')] = (int) ($vendasPorDia[$dia->format('Y-m-d')] ?? 0);
}
$passo = max(1, (int) ceil(max($serie) / 4));
$totalPeriodo = array_sum($serie);

$totalProdutos = (int) $indicadores['produtos'];
$estoqueBaixo = (int) $indicadores['estoque_baixo'];
$estoqueRegular = $totalProdutos - $estoqueBaixo;
$percentualRegular = $totalProdutos > 0 ? 100 * $estoqueRegular / $totalProdutos : 0;
$numero = static fn ($valor): string => number_format((int) $valor, 0, ',', '.');

$movimentacoes = $pdo->query('
    SELECT p.nome AS produto, m.tipo, m.quantidade, m.data_movimentacao
    FROM movimentacoes m
    INNER JOIN produtos p ON p.id = m.produto_id
    ORDER BY m.data_movimentacao DESC, m.id DESC
    LIMIT 5
')->fetchAll();

$alertas = $pdo->query('
    SELECT nome, quantidade, estoque_minimo
    FROM produtos
    WHERE ativo = 1 AND quantidade <= estoque_minimo
    ORDER BY quantidade, nome, id
    LIMIT 5
')->fetchAll();

$produtos = $pdo->query('
    SELECT p.id, p.nome, p.preco, p.quantidade, p.estoque_minimo, c.nome AS categoria
    FROM produtos p
    INNER JOIN categorias c ON c.id = p.categoria_id
    WHERE p.ativo = 1
    ORDER BY p.id DESC
    LIMIT 10
')->fetchAll();

$cartoes = [
    ['Produtos ativos', $totalProdutos, $numero($indicadores['unidades']) . ' un. em estoque', 'box-seam', 'indigo', 'produtos.php'],
    ['Vendas concluídas', $indicadores['vendas'], 'Total acumulado', 'cart3', 'success', 'vendas.php'],
    ['Estoque baixo', $estoqueBaixo, 'No mínimo ou abaixo', 'exclamation-triangle', 'danger', '#estoque-baixo'],
    ['Clientes cadastrados', $indicadores['clientes'], '', 'people', 'violet', 'clientes.php'],
];

stockwebLayoutAbrir('dashboard', 'Dashboard');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Dashboard', 'Bem-vindo, ' . $nome . '! Acompanhe seu estoque e suas vendas.'); ?>
    <div class="sw-page-actions sw-dashboard-actions">
        <a href="produto_novo.php" class="btn btn-outline-secondary"><i class="bi bi-box-seam" aria-hidden="true"></i> Novo produto</a>
        <a href="venda_nova.php" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nova venda</a>
    </div>
</header>

<section class="sw-dashboard-kpis" aria-label="Indicadores">
    <?php foreach ($cartoes as [$rotulo, $valor, $descricao, $icone, $tom, $url]): ?>
        <article class="sw-panel sw-kpi">
            <span class="sw-icon-tile sw-tone-<?= $tom ?>"><i class="bi bi-<?= $icone ?>" aria-hidden="true"></i></span>
            <dl class="mb-0">
                <dt><a class="sw-kpi-link stretched-link" href="<?= $url ?>"><?= stockwebEscapar($rotulo) ?></a></dt>
                <dd><?= $numero($valor) ?></dd>
            </dl>
            <?php if ($descricao !== ''): ?>
                <p><?= stockwebEscapar($descricao) ?></p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<div class="sw-dashboard-grid">
    <section class="sw-panel" aria-labelledby="titulo-vendas">
        <header class="sw-panel-header">
            <div>
                <h2 id="titulo-vendas">Vendas por dia</h2>
                <p>Quantidade de vendas concluídas</p>
            </div>
            <span class="sw-period"><i class="bi bi-calendar3" aria-hidden="true"></i> Últimos 7 dias</span>
        </header>
        <div class="sw-sales-chart">
            <p class="sw-chart-summary"><strong><?= $numero($totalPeriodo) ?></strong> <?= $totalPeriodo === 1 ? 'venda no período' : 'vendas no período' ?></p>
            <div class="sw-chart" role="group" aria-label="Vendas concluídas por data">
                <div class="sw-chart-scale" aria-hidden="true">
                    <?php for ($nivel = 4; $nivel >= 0; $nivel--): ?>
                        <span><?= $numero($nivel * $passo) ?></span>
                    <?php endfor; ?>
                </div>
                <ul class="sw-chart-bars" role="list">
                    <?php foreach ($serie as $dia => $vendas): ?>
                        <li title="<?= $dia ?> · <?= $numero($vendas) ?> <?= $vendas === 1 ? 'venda' : 'vendas' ?>">
                            <div class="sw-bar-track" aria-hidden="true">
                                <span class="sw-bar-fill" style="--sw-bar-height: <?= 25 * $vendas / $passo ?>%"></span>
                            </div>
                            <span class="sw-bar-date"><?= $dia ?></span>
                            <span class="visually-hidden"><?= $numero($vendas) ?> <?= $vendas === 1 ? 'venda' : 'vendas' ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php if ($totalPeriodo === 0): ?>
                <p class="sw-chart-note">Nenhuma venda concluída neste período.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="sw-panel" aria-labelledby="titulo-situacao">
        <header class="sw-panel-header">
            <div>
                <h2 id="titulo-situacao">Situação do estoque</h2>
                <p>Distribuição dos produtos ativos por saldo</p>
            </div>
        </header>
        <div class="sw-stock-chart">
            <div class="sw-donut<?= $totalProdutos === 0 ? ' sw-donut-empty' : '' ?>"
                 style="--sw-stock-ok: <?= $percentualRegular ?>%">
                <div class="sw-donut-center"><strong><?= $numero($totalProdutos) ?></strong> produtos ativos</div>
            </div>
            <dl class="sw-chart-legend">
                <div><dt><span class="sw-legend-dot" aria-hidden="true"></span>Acima do mínimo</dt><dd><?= $numero($estoqueRegular) ?></dd></div>
                <div><dt><span class="sw-legend-dot sw-legend-dot-low" aria-hidden="true"></span>No mínimo ou abaixo</dt><dd><?= $numero($estoqueBaixo) ?></dd></div>
            </dl>
        </div>
    </section>
</div>

<div class="sw-dashboard-grid">
    <section class="sw-panel" aria-labelledby="titulo-atividade">
        <header class="sw-panel-header">
            <h2 id="titulo-atividade">Movimentações recentes</h2>
            <a class="sw-text-link" href="movimentacoes.php">Ver histórico</a>
        </header>
        <?php if (!$movimentacoes): ?>
            <?php stockwebEstadoVazio('arrow-left-right', 'Nenhuma movimentação registrada.'); ?>
        <?php else: ?>
            <ul class="sw-activity">
                <?php foreach ($movimentacoes as $item): ?>
                    <?php $entrada = $item['tipo'] === 'ENTRADA'; ?>
                    <li>
                        <span class="sw-icon-tile sw-tone-<?= $entrada ? 'success' : 'neutral' ?>">
                            <i class="bi bi-<?= $entrada ? 'arrow-down-left' : 'arrow-up-right' ?>" aria-hidden="true"></i>
                        </span>
                        <div class="sw-activity-info">
                            <strong><?= stockwebEscapar($item['produto']) ?></strong>
                            <small><?= $entrada ? 'Entrada' : 'Saída' ?> · <?= (new DateTimeImmutable($item['data_movimentacao']))->format('d/m/Y H:i') ?></small>
                        </div>
                        <span class="sw-activity-quantity<?= $entrada ? ' text-success' : '' ?>"><?= $entrada ? '+' : '−' ?><?= $numero($item['quantidade']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="sw-panel" id="estoque-baixo" aria-labelledby="titulo-alertas">
        <header class="sw-panel-header">
            <h2 id="titulo-alertas">Alertas de estoque</h2>
            <a class="sw-text-link" href="relatorios.php?relatorio=estoque&amp;ativo=1">Ver estoque</a>
        </header>
        <?php if (!$alertas): ?>
            <?php stockwebEstadoVazio('check2-circle', 'Nenhum alerta de estoque baixo.'); ?>
        <?php else: ?>
            <ul class="sw-activity">
                <?php foreach ($alertas as $produto): ?>
                    <li>
                        <span class="sw-icon-tile sw-tone-danger"><i class="bi bi-exclamation-circle" aria-hidden="true"></i></span>
                        <div class="sw-activity-info">
                            <strong><?= stockwebEscapar($produto['nome']) ?></strong>
                            <small>Mínimo de <?= $numero($produto['estoque_minimo']) ?> un.</small>
                        </div>
                        <span class="sw-activity-quantity text-danger"><?= $numero($produto['quantidade']) ?> un.</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<section class="sw-panel" aria-labelledby="titulo-produtos">
    <header class="sw-panel-header sw-table-header">
        <h2 id="titulo-produtos">Produtos cadastrados recentemente</h2>
        <a class="sw-text-link" href="produtos.php">Ver produtos</a>
    </header>
    <div class="table-responsive sw-table-scroll" tabindex="0" role="region" aria-labelledby="titulo-produtos">
        <table class="table table-hover align-middle sw-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Produto</th>
                    <th scope="col">Categoria</th>
                    <th scope="col" class="text-end">Preço</th>
                    <th scope="col" class="text-end">Estoque</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$produtos): ?>
                    <tr><td colspan="6"><?php stockwebEstadoVazio('box-seam', 'Nenhum produto ativo cadastrado.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($produtos as $produto): ?>
                    <?php $baixo = (int) $produto['quantidade'] <= (int) $produto['estoque_minimo']; ?>
                    <tr>
                        <td>#<?= (int) $produto['id'] ?></td>
                        <th scope="row"><span class="sw-row-name"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-box-seam" aria-hidden="true"></i></span><?= stockwebEscapar($produto['nome']) ?></span></th>
                        <td><?= stockwebEscapar($produto['categoria']) ?></td>
                        <td class="text-end text-nowrap">R$ <?= number_format((float) $produto['preco'], 2, ',', '.') ?></td>
                        <td class="text-end"><?= $numero($produto['quantidade']) ?></td>
                        <td><?php stockwebStatus($baixo ? 'Estoque baixo' : 'Disponível', $baixo ? 'danger' : 'success'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php stockwebLayoutFechar(); ?>
