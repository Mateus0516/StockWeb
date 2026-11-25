<?php
require_once __DIR__ . '/fornecedor_funcoes.php';
require_once '/var/www/stockweb/includes/layout.php';

$editando = isset($fornecedor);
$destino = $editando ? 'fornecedor_editar.php?id=' . $id : 'fornecedor_novo.php';
$aviso = fornecedorAviso($destino);
$dados = $aviso['dados'] ?? $fornecedor ?? [];
$acao = $editando ? 'fornecedor_atualizar.php' : 'fornecedor_salvar.php';
$titulo = $editando ? 'Editar fornecedor' : 'Novo fornecedor';

stockwebLayoutAbrir('fornecedores', $titulo);
?>
<header class="sw-page-header">
    <?php stockwebTitulo($titulo, $editando ? 'Atualize os dados de cadastro e contato.' : 'Informe os dados de cadastro e contato.'); ?>
</header>
<div class="sw-form-narrow">
    <?php if ($aviso !== null): ?>
        <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
    <?php endif; ?>
    <form class="sw-panel sw-form-panel" action="<?= fornecedorEscapar($acao) ?>" method="POST">
        <div class="sw-form-section">
            <h2 class="sw-form-kicker"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-truck" aria-hidden="true"></i></span> Dados do fornecedor</h2>
        </div>
        <input type="hidden" name="csrf_token" value="<?= fornecedorEscapar(fornecedorToken()) ?>">
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-8">
                <label for="nome" class="form-label fw-semibold">Nome</label>
                <input type="text" id="nome" name="nome" class="form-control" required maxlength="150"
                       value="<?= fornecedorEscapar($dados['nome'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="cnpj" class="form-label fw-semibold">CNPJ</label>
                <input type="text" id="cnpj" name="cnpj" class="form-control" required maxlength="18"
                       aria-describedby="cnpj-ajuda" value="<?= fornecedorEscapar($dados['cnpj'] ?? '') ?>">
                <div id="cnpj-ajuda" class="form-text">Informe os 14 caracteres, com ou sem pontuação.</div>
            </div>
            <div class="col-md-8">
                <label for="email" class="form-label fw-semibold">E-mail (opcional)</label>
                <input type="email" id="email" name="email" class="form-control" maxlength="150"
                       value="<?= fornecedorEscapar($dados['email'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="telefone" class="form-label fw-semibold">Telefone (opcional)</label>
                <input type="tel" id="telefone" name="telefone" class="form-control" maxlength="20"
                       value="<?= fornecedorEscapar($dados['telefone'] ?? '') ?>">
            </div>
        </div>

        <div class="sw-form-actions">
            <a href="fornecedores.php" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar fornecedor</button>
        </div>
    </form>
</div>
<?php stockwebLayoutFechar(); ?>
