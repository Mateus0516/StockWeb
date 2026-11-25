<?php
require_once __DIR__ . '/categoria_funcoes.php';

// Conexão com o banco
require_once '/var/www/stockweb/config/database.php';

// Mantém a contagem de ativos e verifica todos os vínculos.
$stmt = $pdo->query("
    SELECT
        c.id,
        c.nome,
        c.descricao,
        COUNT(CASE WHEN p.ativo = 1 THEN p.id END) AS total_produtos,
        COUNT(p.id) AS total_vinculos
    FROM categorias c
    LEFT JOIN produtos p
        ON p.categoria_id = c.id
    GROUP BY c.id, c.nome, c.descricao
    ORDER BY c.nome ASC
");

$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aviso = categoriaAviso('categorias.php');
$token = categoriaToken();
require_once '/var/www/stockweb/includes/layout.php';

stockwebLayoutAbrir('categorias', 'Categorias');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Categorias', 'Organize os produtos do estoque por categoria.'); ?>
    <a href="categoria_nova.php" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nova categoria</a>
</header>
<?php if ($aviso !== null): ?>
    <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
<?php endif; ?>

<section class="sw-panel" aria-label="Cadastro de categorias">
    <?php stockwebTabelaCabecalho('Categorias cadastradas', count($categorias)); ?>
    <div class="table-responsive sw-table-scroll" tabindex="0" role="region" aria-label="Tabela de categorias">
        <table class="table table-hover align-middle sw-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Categoria</th>
                    <th scope="col">Descrição</th>
                    <th scope="col" class="text-end">Produtos ativos</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$categorias): ?>
                    <tr><td colspan="5"><?php stockwebEstadoVazio('tags', 'Nenhuma categoria cadastrada.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($categorias as $categoria): ?>
                    <?php $id = (int) $categoria['id']; ?>
                    <tr>
                        <td>#<?= $id ?></td>
                        <th scope="row">
                            <span class="sw-row-name">
                                <span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-tag" aria-hidden="true"></i></span>
                                <?= stockwebEscapar($categoria['nome']) ?>
                            </span>
                        </th>
                        <td><?= stockwebEscapar($categoria['descricao'] ?? '—') ?></td>
                        <td class="text-end"><?= (int) $categoria['total_produtos'] ?></td>
                        <td>
                            <div class="sw-row-actions">
                                <a href="categoria_editar.php?id=<?= $id ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil" aria-hidden="true"></i> Editar
                                </a>
                                <?php if ((int) $categoria['total_vinculos'] > 0): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            disabled aria-describedby="vinculos-<?= $id ?>">Excluir</button>
                                <?php else: ?>
                                    <form method="POST" action="categoria_excluir.php"
                                          onsubmit="return confirm(this.dataset.confirmacao);"
                                          data-confirmacao="Excluir a categoria «<?= stockwebEscapar($categoria['nome']) ?>»?">
                                        <input type="hidden" name="csrf_token" value="<?= stockwebEscapar($token) ?>">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <?php if ((int) $categoria['total_vinculos'] > 0): ?>
                                <small id="vinculos-<?= $id ?>" class="sw-row-hint">Há produtos vinculados.</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php stockwebConfirmacaoExclusao(); ?>
<?php stockwebLayoutFechar(); ?>
