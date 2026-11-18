<?php
require_once __DIR__ . '/movimentacao_funcoes.php';
require_once '/var/www/stockweb/config/database.php';

$produtos = $pdo->query('SELECT id, nome, quantidade FROM produtos WHERE ativo = 1 ORDER BY nome, id')->fetchAll();
$aviso = movimentacaoAviso('movimentacao_nova.php');
$dados = $aviso['dados'] ?? [];
$saldoSelecionado = null;
foreach ($produtos as $produto) {
    if ((int) $produto['id'] === ($dados['produto_id'] ?? null)) {
        $saldoSelecionado = (int) $produto['quantidade'];
        break;
    }
}

movimentacaoCabecalho('Nova movimentação', $aviso);
?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="fw-bold mb-0">Nova movimentação</h1>
            <a href="movimentacoes.php" class="btn btn-outline-secondary">Voltar ao histórico</a>
        </div>

        <?php if (!$produtos): ?>
            <div class="alert alert-warning">Cadastre um produto ativo para movimentar o estoque.</div>
        <?php else: ?>
            <form class="card border-0 rounded-4 shadow-sm p-3 p-md-4" action="movimentacao_salvar.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= movimentacaoEscapar(movimentacaoToken()) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="produto_id" class="form-label fw-semibold">Produto</label>
                        <select class="form-select" id="produto_id" name="produto_id" aria-describedby="estoque_atual" required>
                            <option value="">Selecione</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= (int) $produto['id'] ?>" data-estoque="<?= (int) $produto['quantidade'] ?>"
                                    <?= (int) $produto['id'] === ($dados['produto_id'] ?? null) ? 'selected' : '' ?>>
                                    <?= movimentacaoEscapar($produto['nome']) ?> (#<?= (int) $produto['id'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="estoque_atual" role="status">
                            Estoque atual: <output id="saldo_produto" for="produto_id"><?= $saldoSelecionado ?? '—' ?></output>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="tipo" class="form-label fw-semibold">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="ENTRADA" <?= ($dados['tipo'] ?? '') === 'ENTRADA' ? 'selected' : '' ?>>Entrada</option>
                            <option value="SAIDA" <?= ($dados['tipo'] ?? '') === 'SAIDA' ? 'selected' : '' ?>>Saída</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="quantidade" class="form-label fw-semibold">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" required
                               min="1" max="<?= ESTOQUE_MAXIMO ?>" step="1" value="<?= movimentacaoEscapar($dados['quantidade'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label for="observacao" class="form-label fw-semibold">Observação (opcional)</label>
                        <textarea class="form-control" id="observacao" name="observacao" rows="3"
                                  maxlength="1000"><?= movimentacaoEscapar($dados['observacao'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-primary" type="submit">Registrar movimentação</button>
                </div>
            </form>
            <script>
                const produto = document.querySelector('#produto_id');
                const saldo = document.querySelector('#saldo_produto');

                produto.addEventListener('change', () => {
                    saldo.value = produto.selectedOptions[0]?.dataset.estoque ?? '—';
                });
            </script>
        <?php endif; ?>
<?php movimentacaoRodape();
