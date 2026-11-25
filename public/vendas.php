<?php
require_once __DIR__ . '/venda_funcoes.php';
require_once '/var/www/stockweb/includes/layout.php';
require_once '/var/www/stockweb/config/database.php';

$vendas = $pdo->query('
    SELECT v.id, v.data_venda, v.valor_total, v.status, c.nome AS cliente
    FROM vendas v
    INNER JOIN clientes c ON c.id = v.cliente_id
    ORDER BY v.data_venda DESC, v.id DESC
')->fetchAll();

$aviso = vendaAviso('vendas.php');
stockwebLayoutAbrir('vendas', 'Vendas');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Vendas', 'Acompanhe as vendas registradas e consulte seus detalhes.'); ?>
    <a href="venda_nova.php" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nova venda</a>
</header>
<?php if ($aviso !== null): ?>
    <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
<?php endif; ?>
<section class="sw-panel" aria-label="Histórico de vendas">
    <?php stockwebTabelaCabecalho('Histórico de vendas', count($vendas)); ?>
    <div class="table-responsive sw-table-scroll" tabindex="0" role="region" aria-label="Tabela de vendas">
        <table class="table table-hover align-middle sw-table">
            <thead>
                <tr>
                    <th scope="col">Venda</th>
                    <th scope="col">Data</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end">Total</th>
                    <th scope="col" class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$vendas): ?>
                    <tr><td colspan="6"><?php stockwebEstadoVazio('cart3', 'Nenhuma venda registrada.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($vendas as $venda): ?>
                    <tr>
                        <th scope="row">#<?= (int) $venda['id'] ?></th>
                        <td class="text-nowrap"><?= vendaEscapar(vendaData($venda['data_venda'])) ?></td>
                        <td><?= vendaEscapar($venda['cliente']) ?></td>
                        <td><?php stockwebStatusVenda($venda['status']); ?></td>
                        <td class="text-end text-nowrap"><?= vendaMoeda($venda['valor_total']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-outline-primary btn-sm"
                               href="venda_detalhes.php?id=<?= (int) $venda['id'] ?>"><i class="bi bi-receipt" aria-hidden="true"></i> Detalhes</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php stockwebLayoutFechar(); ?>
