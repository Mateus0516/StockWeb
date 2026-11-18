<?php
require_once __DIR__ . '/venda_funcoes.php';
require_once '/var/www/stockweb/config/database.php';

$vendas = $pdo->query('
    SELECT v.id, v.data_venda, v.valor_total, v.status, c.nome AS cliente
    FROM vendas v
    INNER JOIN clientes c ON c.id = v.cliente_id
    ORDER BY v.data_venda DESC, v.id DESC
')->fetchAll();

vendaCabecalho('Vendas', vendaAviso('vendas.php'));
?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="fw-bold mb-0">Vendas</h1>
            <a href="venda_nova.php" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nova venda</a>
        </div>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Venda</th>
                            <th scope="col">Data</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col" class="pe-4 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$vendas): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma venda registrada.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($vendas as $venda): ?>
                            <tr>
                                <th scope="row" class="ps-4">#<?= (int) $venda['id'] ?></th>
                                <td class="text-nowrap"><?= vendaEscapar(vendaData($venda['data_venda'])) ?></td>
                                <td><?= vendaEscapar($venda['cliente']) ?></td>
                                <td><?= vendaEscapar($venda['status']) ?></td>
                                <td class="text-end text-nowrap"><?= vendaMoeda($venda['valor_total']) ?></td>
                                <td class="pe-4 text-end">
                                    <a class="btn btn-outline-primary btn-sm"
                                       href="venda_detalhes.php?id=<?= (int) $venda['id'] ?>">Detalhes</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php vendaRodape();
