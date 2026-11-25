<?php
$editando = isset($produto);
$titulo = $editando ? 'Editar produto' : 'Novo produto';
$acao = $editando ? 'produto_atualizar.php' : 'produto_salvar.php';

stockwebLayoutAbrir('produtos', $titulo);
?>
<header class="sw-page-header">
    <?php stockwebTitulo($titulo, $editando ? 'Atualize as informações do item no estoque.' : 'Preencha os dados para adicionar um item ao estoque.'); ?>
</header>
<form class="sw-panel sw-form-panel sw-form-narrow" action="<?= $acao ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= stockwebEscapar($_SESSION['csrf_token']) ?>">
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= (int) $produto['id'] ?>">
        <input type="hidden" name="quantidade_original" value="<?= (int) $produto['quantidade'] ?>">
    <?php endif; ?>

    <div class="sw-form-section">
        <h2 class="sw-form-kicker"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-box-seam" aria-hidden="true"></i></span> Dados do produto</h2>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label for="nome" class="form-label">Nome do produto</label>
            <input type="text" id="nome" name="nome" class="form-control" maxlength="150" required
                   value="<?= stockwebEscapar($produto['nome'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="categoria_id" class="form-label">Categoria</label>
            <select id="categoria_id" name="categoria_id" class="form-select" required>
                <?php if (!$editando): ?>
                    <option value="">Selecione uma categoria</option>
                <?php endif; ?>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int) $categoria['id'] ?>"
                        <?= (int) $categoria['id'] === (int) ($produto['categoria_id'] ?? 0) ? 'selected' : '' ?>>
                        <?= stockwebEscapar($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="fornecedor_id" class="form-label">Fornecedor</label>
            <select id="fornecedor_id" name="fornecedor_id" class="form-select" required>
                <?php if (!$editando): ?>
                    <option value="">Selecione um fornecedor</option>
                <?php endif; ?>
                <?php foreach ($fornecedores as $fornecedor): ?>
                    <option value="<?= (int) $fornecedor['id'] ?>"
                        <?= (int) $fornecedor['id'] === (int) ($produto['fornecedor_id'] ?? 0) ? 'selected' : '' ?>>
                        <?= stockwebEscapar($fornecedor['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label for="descricao" class="form-label">Descrição <span class="fw-normal">(opcional)</span></label>
            <textarea id="descricao" name="descricao" class="form-control" rows="3"><?= stockwebEscapar($produto['descricao'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="sw-form-section sw-form-section-divider">
        <h2 class="sw-form-kicker"><span class="sw-icon-tile sw-tone-violet"><i class="bi bi-boxes" aria-hidden="true"></i></span> Preço e estoque</h2>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label for="preco" class="form-label">Preço (R$)</label>
            <input type="number" id="preco" name="preco" class="form-control" min="0" max="99999999.99" step="0.01" required
                   value="<?= stockwebEscapar($produto['preco'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label for="quantidade" class="form-label"><?= $editando ? 'Quantidade' : 'Quantidade inicial' ?></label>
            <input type="number" id="quantidade" name="quantidade" class="form-control" min="0" step="1" required
                   value="<?= (int) ($produto['quantidade'] ?? 0) ?>">
        </div>
        <div class="col-md-4">
            <label for="estoque_minimo" class="form-label">Estoque mínimo</label>
            <input type="number" id="estoque_minimo" name="estoque_minimo" class="form-control" min="0" step="1" required
                   value="<?= (int) ($produto['estoque_minimo'] ?? 5) ?>">
        </div>
    </div>

    <div class="sw-form-actions">
        <a href="produtos.php" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Salvar alterações' : 'Salvar produto' ?></button>
    </div>
</form>
<?php stockwebLayoutFechar(); ?>
