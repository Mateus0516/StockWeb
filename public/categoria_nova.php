<?php
require_once __DIR__ . '/categoria_funcoes.php';

$editando = isset($categoria);
$destino = $editando ? 'categoria_editar.php?id=' . $id : 'categoria_nova.php';
$aviso = categoriaAviso($destino);
$dados = $aviso['dados'] ?? $categoria ?? ['nome' => '', 'descricao' => ''];
$titulo = $editando ? 'Editar categoria' : 'Nova categoria';
$acao = $editando ? 'categoria_atualizar.php' : 'categoria_salvar.php';
$token = categoriaToken();
require_once '/var/www/stockweb/includes/layout.php';

stockwebLayoutAbrir('categorias', $titulo);
?>
<header class="sw-page-header">
    <?php stockwebTitulo($titulo, $editando ? 'Atualize os dados da categoria.' : 'Cadastre uma categoria para organizar os produtos.'); ?>
</header>
<div class="sw-form-narrow">
    <?php if ($aviso !== null): ?>
        <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
    <?php endif; ?>

    <form class="sw-panel sw-form-panel" action="<?= e($acao) ?>" method="POST">
        <div class="sw-form-section">
            <h2 class="sw-form-kicker"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-tag" aria-hidden="true"></i></span> Dados da categoria</h2>
        </div>
        <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="nome" class="form-label">Nome da categoria</label>
            <input type="text" id="nome" name="nome" class="form-control" value="<?= e($dados['nome']) ?>"
                   maxlength="100" placeholder="Ex.: Equipamentos de Rede" required>
        </div>
        <div>
            <label for="descricao" class="form-label">Descrição <span class="fw-normal">(opcional)</span></label>
            <textarea id="descricao" name="descricao" class="form-control" rows="4"
                      placeholder="Descreva os produtos desta categoria"><?= e($dados['descricao']) ?></textarea>
        </div>
        <div class="sw-form-actions">
            <a href="categorias.php" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?= $editando ? 'Salvar alterações' : 'Salvar categoria' ?></button>
        </div>
    </form>
</div>
<?php stockwebLayoutFechar(); ?>
