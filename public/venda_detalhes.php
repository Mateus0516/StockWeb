<?php
require_once __DIR__ . '/venda_funcoes.php';
require_once '/var/www/stockweb/includes/layout.php';

$id = vendaInteiro($_GET['id'] ?? null);
if ($id === false) {
    vendaRedirecionar('vendas.php', 'danger', 'Venda inválida.');
}

require_once '/var/www/stockweb/config/database.php';

$stmt = $pdo->prepare('
    SELECT v.data_venda, v.status, v.valor_total, c.nome AS cliente, u.nome AS vendedor
    FROM vendas v
    INNER JOIN clientes c ON c.id = v.cliente_id
    INNER JOIN usuarios u ON u.id = v.usuario_id
    WHERE v.id = ?
');
$stmt->execute([$id]);
$venda = $stmt->fetch();
if (!$venda) {
    vendaRedirecionar('vendas.php', 'danger', 'Venda não encontrada.');
}

$stmt = $pdo->prepare('
    SELECT p.nome, i.quantidade, i.preco_unitario, i.subtotal
    FROM itens_venda i
    INNER JOIN produtos p ON p.id = i.produto_id
    WHERE i.venda_id = ?
    ORDER BY i.id
');
$stmt->execute([$id]);
$itens = $stmt->fetchAll();

$aviso = vendaAviso('venda_detalhes.php?id=' . $id);
stockwebLayoutAbrir('vendas', 'Detalhes da venda');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Venda #' . $id, 'Consulte os itens, as quantidades e o valor registrado.'); ?>
    <a href="vendas.php" class="btn btn-outline-secondary">Voltar às vendas</a>
</header>
<?php if ($aviso !== null): ?>
    <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
<?php endif; ?>
<div class="sw-panel sw-form-panel mb-3">
    <div class="sw-form-section">
        <h2 class="sw-form-kicker"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-receipt" aria-hidden="true"></i></span> Dados da venda</h2>
    </div>
    <dl class="row g-3 mb-0 sw-sale-details">
            <div class="col-sm-6 col-xl-3"><dt>Cliente</dt><dd><?= vendaEscapar($venda['cliente']) ?></dd></div>
            <div class="col-sm-6 col-xl-3"><dt>Vendedor</dt><dd><?= vendaEscapar($venda['vendedor']) ?></dd></div>
            <div class="col-sm-6 col-xl-3"><dt>Data</dt><dd><?= vendaEscapar(vendaData($venda['data_venda'])) ?></dd></div>
            <div class="col-sm-6 col-xl-3"><dt>Status</dt><dd><?php stockwebStatusVenda($venda['status']); ?></dd></div>
    </dl>
</div>
<section class="sw-panel" aria-label="Itens da venda">
    <?php stockwebTabelaCabecalho('Itens da venda', count($itens)); ?>
    <div class="table-responsive sw-table-scroll" tabindex="0" role="region" aria-label="Tabela de itens da venda">
        <table class="table align-middle sw-table">
            <thead>
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col" class="text-end">Quantidade</th>
                    <th scope="col" class="text-end text-nowrap">Preço unitário</th>
                    <th scope="col" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$itens): ?>
                    <tr><td colspan="4"><?php stockwebEstadoVazio('cart3', 'Nenhum item registrado.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <th scope="row"><?= vendaEscapar($item['nome']) ?></th>
                        <td class="text-end"><?= (int) $item['quantidade'] ?></td>
                        <td class="text-end text-nowrap"><?= vendaMoeda($item['preco_unitario']) ?></td>
                        <td class="text-end text-nowrap"><?= vendaMoeda($item['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th scope="row" colspan="3" class="text-end">Total da venda</th>
                    <td class="text-end text-nowrap fw-bold"><?= vendaMoeda($venda['valor_total']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>
<?php stockwebLayoutFechar(); ?>
