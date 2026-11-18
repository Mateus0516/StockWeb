<?php
require_once __DIR__ . '/venda_funcoes.php';

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

vendaCabecalho('Detalhes da venda', vendaAviso('venda_detalhes.php?id=' . $id));
?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="fw-bold mb-0">Venda #<?= $id ?></h1>
            <a href="vendas.php" class="btn btn-outline-secondary">Voltar às vendas</a>
        </div>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
            <div class="card-body p-4">
                <dl class="row g-3 mb-0">
                    <div class="col-sm-6 col-xl-3"><dt>Cliente</dt><dd><?= vendaEscapar($venda['cliente']) ?></dd></div>
                    <div class="col-sm-6 col-xl-3"><dt>Vendedor</dt><dd><?= vendaEscapar($venda['vendedor']) ?></dd></div>
                    <div class="col-sm-6 col-xl-3"><dt>Data</dt><dd><?= vendaEscapar(vendaData($venda['data_venda'])) ?></dd></div>
                    <div class="col-sm-6 col-xl-3"><dt>Status</dt><dd><?= vendaEscapar($venda['status']) ?></dd></div>
                </dl>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Produto</th>
                            <th scope="col" class="text-end">Quantidade</th>
                            <th scope="col" class="text-end text-nowrap">Preço unitário</th>
                            <th scope="col" class="text-end pe-4">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$itens): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhum item registrado.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <th scope="row" class="ps-4"><?= vendaEscapar($item['nome']) ?></th>
                                <td class="text-end"><?= (int) $item['quantidade'] ?></td>
                                <td class="text-end text-nowrap"><?= vendaMoeda($item['preco_unitario']) ?></td>
                                <td class="text-end text-nowrap pe-4"><?= vendaMoeda($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th scope="row" colspan="3" class="text-end">Total da venda</th>
                            <td class="text-end text-nowrap fw-bold pe-4"><?= vendaMoeda($venda['valor_total']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
<?php vendaRodape();
