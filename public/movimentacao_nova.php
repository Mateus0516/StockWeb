<?php
require_once __DIR__ . '/movimentacao_funcoes.php';
require_once '/var/www/stockweb/includes/layout.php';
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

stockwebLayoutAbrir('movimentacoes', 'Nova movimentação');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Nova movimentação', 'Registre uma entrada ou saída manual de produtos.'); ?>
    <a href="movimentacoes.php" class="btn btn-outline-secondary">Voltar ao histórico</a>
</header>
<div class="sw-form-narrow">
    <?php if ($aviso !== null): ?>
        <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
    <?php endif; ?>
    <?php if (!$produtos): ?>
        <?php stockwebAviso('warning', 'Cadastre um produto ativo para movimentar o estoque.'); ?>
    <?php else: ?>
        <form class="sw-panel sw-form-panel" action="movimentacao_salvar.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= movimentacaoEscapar(movimentacaoToken()) ?>">
            <div class="sw-form-section">
                <h2 class="sw-form-kicker"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span> Dados da movimentação</h2>
            </div>
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
            <div class="sw-form-actions">
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
</div>
<?php stockwebLayoutFechar(); ?>
