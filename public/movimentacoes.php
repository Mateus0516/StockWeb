<?php
require_once __DIR__ . '/movimentacao_funcoes.php';
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

movimentacaoCabecalho('Movimentações de estoque', $aviso);
?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="fw-bold mb-0">Movimentações de estoque</h1>
            <a href="movimentacao_nova.php" class="btn btn-primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Nova movimentação
            </a>
        </div>

        <form class="card border-0 rounded-4 shadow-sm p-3 mb-4" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-6 col-xl-4">
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
                <div class="col-md-6 col-xl-2">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos os tipos</option>
                        <option value="ENTRADA" <?= $tipo === 'ENTRADA' ? 'selected' : '' ?>>Entrada</option>
                        <option value="SAIDA" <?= $tipo === 'SAIDA' ? 'selected' : '' ?>>Saída</option>
                    </select>
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label for="inicio" class="form-label">Data inicial</label>
                    <input type="date" class="form-control" id="inicio" name="inicio"
                           value="<?= movimentacaoDataValida($inicio) ? movimentacaoEscapar($inicio) : '' ?>">
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label for="fim" class="form-label">Data final</label>
                    <input type="date" class="form-control" id="fim" name="fim"
                           value="<?= movimentacaoDataValida($fim) ? movimentacaoEscapar($fim) : '' ?>">
                </div>
                <div class="col-xl-2 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="movimentacoes.php" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </div>
        </form>

        <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Data e horário</th>
                            <th scope="col">Produto</th>
                            <th scope="col">Tipo</th>
                            <th scope="col" class="text-end">Quantidade</th>
                            <th scope="col">Responsável</th>
                            <th scope="col" class="pe-4">Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$movimentacoes): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma movimentação encontrada.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($movimentacoes as $item): ?>
                            <?php $entrada = $item['tipo'] === 'ENTRADA'; ?>
                            <tr>
                                <td class="ps-4 text-nowrap"><?= (new DateTimeImmutable($item['data_movimentacao']))->format('d/m/Y H:i:s') ?></td>
                                <th scope="row"><?= movimentacaoEscapar($item['produto']) ?></th>
                                <td><span class="badge <?= $entrada ? 'text-bg-success' : 'text-bg-warning' ?>"><?= $entrada ? 'Entrada' : 'Saída' ?></span></td>
                                <td class="text-end"><?= (int) $item['quantidade'] ?></td>
                                <td><?= movimentacaoEscapar($item['usuario']) ?></td>
                                <td class="pe-4 text-break"><?= movimentacaoEscapar($item['observacao'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php movimentacaoRodape();
