<?php
require_once __DIR__ . '/fornecedor_funcoes.php';
require_once '/var/www/stockweb/config/database.php';
require_once '/var/www/stockweb/includes/layout.php';

$aviso = fornecedorAviso('fornecedores.php');
$token = fornecedorToken();

try {
    $stmt = $pdo->query('
        SELECT f.id, f.nome, f.cnpj, f.email, f.telefone,
               EXISTS (SELECT 1 FROM produtos p WHERE p.fornecedor_id = f.id) AS possui_produtos
        FROM fornecedores f
        ORDER BY f.nome, f.id
    ');
    $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    http_response_code(500);
    $aviso = ['tipo' => 'danger', 'mensagem' => 'Não foi possível carregar os fornecedores.'];
}

stockwebLayoutAbrir('fornecedores', 'Fornecedores');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Fornecedores', 'Organize os parceiros que abastecem seu estoque.'); ?>
    <a href="fornecedor_novo.php" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo fornecedor</a>
</header>
<?php if ($aviso !== null): ?>
    <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
<?php endif; ?>

<?php if (isset($fornecedores)): ?>
    <section class="sw-panel" aria-label="Fornecedores cadastrados">
        <?php stockwebTabelaCabecalho('Fornecedores cadastrados', count($fornecedores)); ?>
        <div class="table-responsive sw-table-scroll" tabindex="0" role="region" aria-label="Tabela de fornecedores">
            <table class="table table-hover align-middle sw-table">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">CNPJ</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Telefone</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$fornecedores): ?>
                        <tr>
                            <td colspan="5"><?php stockwebEstadoVazio('truck', 'Nenhum fornecedor cadastrado.'); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($fornecedores as $fornecedor): ?>
                        <?php $id = (int) $fornecedor['id']; ?>
                        <tr>
                            <th scope="row">
                                <span class="sw-row-name"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-truck" aria-hidden="true"></i></span><?= fornecedorEscapar($fornecedor['nome']) ?></span>
                            </th>
                            <td><?= fornecedorEscapar($fornecedor['cnpj']) ?></td>
                            <td><?= fornecedorEscapar($fornecedor['email'] ?? '—') ?></td>
                            <td><?= fornecedorEscapar($fornecedor['telefone'] ?? '—') ?></td>
                            <td>
                                <div class="sw-row-actions">
                                    <a href="fornecedor_editar.php?id=<?= $id ?>"
                                       class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</a>
                                    <?php if ($fornecedor['possui_produtos']): ?>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                aria-describedby="vinculos-<?= $id ?>" disabled>Excluir</button>
                                    <?php else: ?>
                                        <form action="fornecedor_excluir.php" method="POST"
                                              onsubmit="return confirm(this.dataset.confirmacao);"
                                              data-confirmacao="Excluir «<?= fornecedorEscapar($fornecedor['nome']) ?>»?">
                                            <input type="hidden" name="id" value="<?= $id ?>">
                                            <input type="hidden" name="csrf_token" value="<?= fornecedorEscapar($token) ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <?php if ($fornecedor['possui_produtos']): ?>
                                    <small id="vinculos-<?= $id ?>" class="sw-row-hint">
                                        Há produtos vinculados.
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php stockwebConfirmacaoExclusao(); ?>
<?php endif; ?>
<?php stockwebLayoutFechar(); ?>
