<?php
require_once __DIR__ . '/cliente_funcoes.php';
require_once '/var/www/stockweb/includes/layout.php';

$editando = isset($cliente);
$destino = $editando ? 'cliente_editar.php?id=' . $id : 'cliente_novo.php';
$aviso = clienteAviso($destino);
$dados = $aviso['dados'] ?? $cliente ?? [];
$acao = $editando ? 'cliente_atualizar.php' : 'cliente_salvar.php';
$titulo = $editando ? 'Editar cliente' : 'Novo cliente';

stockwebLayoutAbrir('clientes', $titulo);
?>
<header class="sw-page-header">
    <?php stockwebTitulo($titulo, $editando ? 'Atualize os dados de cadastro e contato.' : 'Informe os dados de cadastro e contato.'); ?>
</header>
<div class="sw-form-narrow">
    <?php if ($aviso !== null): ?>
        <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
    <?php endif; ?>
    <form class="sw-panel sw-form-panel" action="<?= clienteEscapar($acao) ?>" method="POST">
        <div class="sw-form-section">
            <h2 class="sw-form-kicker"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-person" aria-hidden="true"></i></span> Dados do cliente</h2>
        </div>
        <input type="hidden" name="csrf_token" value="<?= clienteEscapar(clienteToken()) ?>">
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-8">
                <label for="nome" class="form-label fw-semibold">Nome</label>
                <input type="text" id="nome" name="nome" class="form-control" required maxlength="150"
                       value="<?= clienteEscapar($dados['nome'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="cpf" class="form-label fw-semibold">CPF</label>
                <input type="text" id="cpf" name="cpf" class="form-control" required maxlength="14" inputmode="numeric"
                       aria-describedby="cpf-ajuda" value="<?= clienteEscapar($dados['cpf'] ?? '') ?>">
                <div id="cpf-ajuda" class="form-text">Informe os 11 dígitos, com ou sem pontuação.</div>
            </div>
            <div class="col-md-8">
                <label for="email" class="form-label fw-semibold">E-mail (opcional)</label>
                <input type="email" id="email" name="email" class="form-control" maxlength="150"
                       value="<?= clienteEscapar($dados['email'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="telefone" class="form-label fw-semibold">Telefone (opcional)</label>
                <input type="tel" id="telefone" name="telefone" class="form-control" maxlength="20"
                       value="<?= clienteEscapar($dados['telefone'] ?? '') ?>">
            </div>
        </div>

        <div class="sw-form-actions">
            <a href="clientes.php" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar cliente</button>
        </div>
    </form>
</div>
<?php stockwebLayoutFechar(); ?>
