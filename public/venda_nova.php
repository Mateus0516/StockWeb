<?php
require_once __DIR__ . '/venda_funcoes.php';
require_once '/var/www/stockweb/config/database.php';

$clientes = $pdo->query('SELECT id, nome, cpf FROM clientes ORDER BY nome, id')->fetchAll();
$produtos = $pdo->query('
    SELECT id, nome, preco, quantidade
    FROM produtos
    WHERE ativo = 1 AND quantidade > 0
    ORDER BY nome, id
')->fetchAll();

$aviso = vendaAviso('venda_nova.php');
$dados = $aviso['dados'] ?? [];
$linhas = ($dados['itens'] ?? []) ?: [['produto_id' => false, 'quantidade' => 1]];
$podeVender = $clientes && $produtos;

function vendaLinha($indice, $item, $produtos) {
    $indice = vendaEscapar($indice);
    ?>
    <div class="row g-3 align-items-end py-3 border-bottom" data-item>
        <div class="col-12 col-xl-4">
            <label class="form-label" for="produto-<?= $indice ?>">Produto</label>
            <select class="form-select" id="produto-<?= $indice ?>" name="itens[<?= $indice ?>][produto_id]" data-produto required>
                <option value="">Selecione</option>
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?= (int) $produto['id'] ?>" data-centavos="<?= vendaCentavos($produto['preco']) ?>"
                            data-estoque="<?= (int) $produto['quantidade'] ?>"
                            <?= (int) $produto['id'] === $item['produto_id'] ? 'selected' : '' ?>>
                        <?= vendaEscapar($produto['nome']) ?> (<?= (int) $produto['quantidade'] ?> em estoque)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-5 col-sm-3 col-xl-2">
            <label class="form-label" for="quantidade-<?= $indice ?>">Quantidade</label>
            <input type="number" class="form-control" id="quantidade-<?= $indice ?>" name="itens[<?= $indice ?>][quantidade]"
                   min="1" max="2147483647" step="1" value="<?= (int) $item['quantidade'] ?: '' ?>" data-quantidade required>
        </div>
        <div class="col-7 col-sm-3 col-xl-2">
            <span class="d-block mb-2">Preço unitário</span>
            <output class="d-block pb-2 text-nowrap" data-preco>—</output>
        </div>
        <div class="col-7 col-sm-3 col-xl-2">
            <span class="d-block mb-2">Subtotal</span>
            <output class="d-block pb-2 fw-semibold text-nowrap" data-subtotal>—</output>
        </div>
        <div class="col-5 col-sm-3 col-xl-2 text-end">
            <button class="btn btn-outline-danger btn-sm" type="button" data-remover>Remover item</button>
        </div>
    </div>
    <?php
}

vendaCabecalho('Nova venda', $aviso);
?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="fw-bold mb-0">Nova venda</h1>
            <a href="vendas.php" class="btn btn-outline-secondary">Voltar às vendas</a>
        </div>
        <?php if (!$podeVender): ?>
            <div class="alert alert-warning" role="status">
                <?= !$clientes ? 'Cadastre um cliente antes de registrar uma venda.' : 'Nenhum produto ativo com estoque disponível.' ?>
            </div>
        <?php else: ?>
            <form class="card border-0 rounded-4 shadow-sm p-3 p-md-4" action="venda_salvar.php" method="POST"
                  id="venda-form" data-max-itens="<?= VENDA_MAX_ITENS ?>" data-max-centavos="<?= VENDA_MAX_CENTAVOS ?>">
                <input type="hidden" name="csrf_token" value="<?= vendaEscapar(vendaToken()) ?>">
                <div class="mb-4">
                    <label for="cliente_id" class="form-label fw-semibold">Cliente</label>
                    <select class="form-select" id="cliente_id" name="cliente_id" required>
                        <option value="">Selecione um cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= (int) $cliente['id'] ?>"
                                    <?= (int) $cliente['id'] === ($dados['cliente_id'] ?? null) ? 'selected' : '' ?>>
                                <?= vendaEscapar($cliente['nome']) ?> (<?= vendaEscapar($cliente['cpf']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h2 class="h5 mb-0">Itens da venda</h2>
                <div id="venda-itens">
                    <?php foreach (array_values($linhas) as $indice => $item): ?>
                        <?php vendaLinha($indice, $item, $produtos); ?>
                    <?php endforeach; ?>
                </div>
                <template id="venda-item-template">
                    <?php vendaLinha('novo', ['produto_id' => false, 'quantidade' => 1], $produtos); ?>
                </template>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                    <button type="button" class="btn btn-outline-primary" id="venda-adicionar" disabled>
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> Adicionar produto
                    </button>
                    <div class="fs-5 fw-bold">Total <output id="venda-total">R$ 0,00</output></div>
                </div>
                <p id="venda-erro" class="text-danger mt-3 mb-0" role="status" hidden></p>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary" id="venda-concluir">Concluir venda</button>
                </div>
            </form>
            <script src="assets/js/venda.js" defer></script>
        <?php endif; ?>
<?php vendaRodape();
