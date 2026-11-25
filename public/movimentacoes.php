<?php
require_once __DIR__ . '/movimentacao_funcoes.php';
require_once '/var/www/stockweb/includes/layout.php';
require_once '/var/www/stockweb/config/database.php';

function movimentacaoDataValida($valor): bool {
    if (!is_string($valor) || preg_match('/\A([1-9][0-9]{3})-([0-9]{2})-([0-9]{2})\z/', $valor, $partes) !== 1) {
        return false;
    }

    return checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1]);
}

$produtos = $pdo->query('SELECT id, nome FROM produtos ORDER BY nome, id')->fetchAll();
$produto = $_GET['produto_id'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$inicio = $_GET['inicio'] ?? '';
$fim = $_GET['fim'] ?? '';
$produtoId = movimentacaoInteiro($produto);
$filtros = [];
$parametros = [];
$aviso = movimentacaoAviso('movimentacoes.php');
$movimentacoes = [];

if (($produto !== '' && $produtoId === false) ||
    !in_array($tipo, ['', 'ENTRADA', 'SAIDA'], true) ||
    ($inicio !== '' && !movimentacaoDataValida($inicio)) ||
    ($fim !== '' && !movimentacaoDataValida($fim)) ||
    ($inicio !== '' && $fim !== '' && $inicio > $fim)
) {
    $aviso = ['tipo' => 'danger', 'mensagem' => 'Confira o produto, o tipo e as datas do filtro. A data inicial deve ser anterior ou igual à final.'];
} else {
    if ($produtoId !== false) {
        $filtros[] = 'm.produto_id = ?';
        $parametros[] = $produtoId;
    }

    if ($tipo !== '') {
        $filtros[] = 'm.tipo = ?';
        $parametros[] = $tipo;
    }

    if ($inicio !== '') {
        $filtros[] = 'm.data_movimentacao >= ?';
        $parametros[] = $inicio . ' 00:00:00';
    }

    if ($fim !== '') {
        $filtros[] = 'm.data_movimentacao <= ?';
        $parametros[] = $fim . ' 23:59:59';
    }

    $where = $filtros ? ' WHERE ' . implode(' AND ', $filtros) : '';
    $stmt = $pdo->prepare('
        SELECT p.nome AS produto, u.nome AS usuario, m.tipo, m.quantidade, m.data_movimentacao, m.observacao
        FROM movimentacoes m
        INNER JOIN produtos p ON p.id = m.produto_id
        INNER JOIN usuarios u ON u.id = m.usuario_id
    ' . $where . ' ORDER BY m.data_movimentacao DESC, m.id DESC');
    $stmt->execute($parametros);
    $movimentacoes = $stmt->fetchAll();
}

stockwebLayoutAbrir('movimentacoes', 'Movimentações');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Movimentações', 'Consulte as entradas e saídas de produtos do estoque.'); ?>
    <a href="movimentacao_nova.php" class="btn btn-primary">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Nova movimentação
    </a>
</header>
<?php if ($aviso !== null): ?>
    <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
<?php endif; ?>

<form class="sw-panel sw-filter" method="GET">
    <div class="sw-form-section">
        <h2 class="sw-form-kicker"><i class="bi bi-sliders" aria-hidden="true"></i> Filtros</h2>
    </div>
    <div class="row g-3 align-items-end">
        <div class="col-md-6 col-xxl-4">
            <label for="produto_id" class="form-label">Produto</label>
            <select class="form-select" id="produto_id" name="produto_id">
                <option value="">Todos os produtos</option>
                <?php foreach ($produtos as $item): ?>
                    <option value="<?= (int) $item['id'] ?>" <?= (int) $item['id'] === $produtoId ? 'selected' : '' ?>>
                        <?= movimentacaoEscapar($item['nome']) ?> (#<?= (int) $item['id'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 col-xxl-2">
            <label for="tipo" class="form-label">Tipo</label>
            <select class="form-select" id="tipo" name="tipo">
                <option value="">Todos os tipos</option>
                <option value="ENTRADA" <?= $tipo === 'ENTRADA' ? 'selected' : '' ?>>Entrada</option>
                <option value="SAIDA" <?= $tipo === 'SAIDA' ? 'selected' : '' ?>>Saída</option>
            </select>
        </div>
        <div class="col-sm-6 col-xxl-2">
            <label for="inicio" class="form-label">Data inicial</label>
            <input type="date" class="form-control" id="inicio" name="inicio"
                   value="<?= movimentacaoDataValida($inicio) ? movimentacaoEscapar($inicio) : '' ?>">
        </div>
        <div class="col-sm-6 col-xxl-2">
            <label for="fim" class="form-label">Data final</label>
            <input type="date" class="form-control" id="fim" name="fim"
                   value="<?= movimentacaoDataValida($fim) ? movimentacaoEscapar($fim) : '' ?>">
        </div>
        <div class="col-xxl-2 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
            <a href="movimentacoes.php" class="btn btn-outline-secondary">Limpar</a>
        </div>
    </div>
</form>

<section class="sw-panel" aria-label="Histórico de movimentações">
    <?php stockwebTabelaCabecalho('Histórico de movimentações', count($movimentacoes)); ?>
    <div class="table-responsive sw-table-scroll" tabindex="0" role="region" aria-label="Tabela de movimentações">
        <table class="table table-hover align-middle sw-table">
            <thead>
                <tr>
                    <th scope="col">Data e horário</th>
                    <th scope="col">Produto</th>
                    <th scope="col">Tipo</th>
                    <th scope="col" class="text-end">Quantidade</th>
                    <th scope="col">Responsável</th>
                    <th scope="col">Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$movimentacoes): ?>
                    <tr><td colspan="6"><?php stockwebEstadoVazio('arrow-left-right', 'Nenhuma movimentação encontrada.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($movimentacoes as $item): ?>
                    <?php $entrada = $item['tipo'] === 'ENTRADA'; ?>
                    <tr>
                        <td class="text-nowrap"><?= (new DateTimeImmutable($item['data_movimentacao']))->format('d/m/Y H:i:s') ?></td>
                        <th scope="row"><?= movimentacaoEscapar($item['produto']) ?></th>
                        <td><?php stockwebStatus($entrada ? 'Entrada' : 'Saída', $entrada ? 'success' : 'warning'); ?></td>
                        <td class="text-end"><?= (int) $item['quantidade'] ?></td>
                        <td><?= movimentacaoEscapar($item['usuario']) ?></td>
                        <td class="text-break"><?= movimentacaoEscapar($item['observacao'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php stockwebLayoutFechar(); ?>
