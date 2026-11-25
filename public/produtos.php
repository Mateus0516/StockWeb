<?php
session_start();

// Protege a página contra acesso sem login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '/var/www/stockweb/config/database.php';

// Gera token CSRF para operações de exclusão
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Consulta produtos ativos, categorias e fornecedores
$stmt = $pdo->query("
    SELECT
        p.id,
        p.nome,
        p.preco,
        p.quantidade,
        p.estoque_minimo,
        c.nome AS categoria,
        f.nome AS fornecedor
    FROM produtos p
    INNER JOIN categorias c
        ON p.categoria_id = c.id
    INNER JOIN fornecedores f
        ON p.fornecedor_id = f.id
    WHERE p.ativo = 1
    ORDER BY p.id DESC
");

$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '/var/www/stockweb/includes/layout.php';

stockwebLayoutAbrir('produtos', 'Produtos');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Produtos', 'Consulte e gerencie os itens do seu estoque.'); ?>
    <a href="produto_novo.php" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo produto</a>
</header>

<?php if (($_GET['sucesso'] ?? null) === '1'): ?>
    <?php stockwebAviso('success', 'Produto cadastrado com sucesso.', true); ?>
<?php endif; ?>
<?php if (($_GET['atualizado'] ?? null) === '1'): ?>
    <?php stockwebAviso('success', 'Produto atualizado com sucesso!', true); ?>
<?php endif; ?>
<?php if (($_GET['excluido'] ?? null) === '1'): ?>
    <?php stockwebAviso('success', 'Produto excluído com sucesso!', true); ?>
<?php endif; ?>

<section class="sw-panel" aria-label="Catálogo de produtos">
    <?php stockwebTabelaCabecalho('Produtos ativos', count($produtos)); ?>
    <div class="table-responsive sw-table-scroll" tabindex="0" role="region" aria-label="Tabela de produtos ativos">
        <table class="table table-hover align-middle sw-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Produto</th>
                    <th scope="col">Categoria</th>
                    <th scope="col">Fornecedor</th>
                    <th scope="col" class="text-end">Preço</th>
                    <th scope="col" class="text-end">Quantidade</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$produtos): ?>
                    <tr><td colspan="8"><?php stockwebEstadoVazio('box-seam', 'Nenhum produto ativo cadastrado.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($produtos as $produto): ?>
                    <?php $baixo = (int) $produto['quantidade'] <= (int) $produto['estoque_minimo']; ?>
                    <tr>
                        <td>#<?= (int) $produto['id'] ?></td>
                        <th scope="row">
                            <span class="sw-row-name">
                                <span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                                <?= stockwebEscapar($produto['nome']) ?>
                            </span>
                        </th>
                        <td><?= stockwebEscapar($produto['categoria']) ?></td>
                        <td><?= stockwebEscapar($produto['fornecedor']) ?></td>
                        <td class="text-end text-nowrap">R$ <?= number_format((float) $produto['preco'], 2, ',', '.') ?></td>
                        <td class="text-end"><?= (int) $produto['quantidade'] ?></td>
                        <td><?php stockwebStatus($baixo ? 'Estoque baixo' : 'Disponível', $baixo ? 'danger' : 'success'); ?></td>
                        <td>
                            <div class="sw-row-actions">
                                <a href="produto_editar.php?id=<?= (int) $produto['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil" aria-hidden="true"></i> Editar
                                </a>
                                <form action="produto_excluir.php" method="POST"
                                      onsubmit="return confirm(this.dataset.confirmacao);"
                                      data-confirmacao="Excluir «<?= stockwebEscapar($produto['nome']) ?>» da lista de produtos ativos?">
                                    <input type="hidden" name="id" value="<?= (int) $produto['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= stockwebEscapar($_SESSION['csrf_token']) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php stockwebConfirmacaoExclusao(); ?>
<?php stockwebLayoutFechar(); ?>
