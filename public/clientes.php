<?php
require_once __DIR__ . '/cliente_funcoes.php';
require_once '/var/www/stockweb/config/database.php';
require_once '/var/www/stockweb/includes/layout.php';

$aviso = clienteAviso('clientes.php');
$token = clienteToken();

try {
    $stmt = $pdo->query('
        SELECT c.id, c.nome, c.cpf, c.email, c.telefone,
               EXISTS (SELECT 1 FROM vendas v WHERE v.cliente_id = c.id) AS possui_vendas
        FROM clientes c
        ORDER BY c.nome, c.id
    ');
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    http_response_code(500);
    $aviso = ['tipo' => 'danger', 'mensagem' => 'Não foi possível carregar os clientes.'];
}

stockwebLayoutAbrir('clientes', 'Clientes');
?>
<header class="sw-page-header">
    <?php stockwebTitulo('Clientes', 'Gerencie os dados dos clientes e seus contatos.'); ?>
    <a href="cliente_novo.php" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo cliente</a>
</header>
<?php if ($aviso !== null): ?>
    <?php stockwebAviso($aviso['tipo'], $aviso['mensagem']); ?>
<?php endif; ?>

<?php if (isset($clientes)): ?>
    <section class="sw-panel" aria-label="Pessoas cadastradas">
        <?php stockwebTabelaCabecalho('Pessoas cadastradas', count($clientes)); ?>
        <div class="table-responsive sw-table-scroll" tabindex="0" role="region" aria-label="Tabela de clientes">
            <table class="table table-hover align-middle sw-table">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">CPF</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Telefone</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$clientes): ?>
                        <tr>
                            <td colspan="5"><?php stockwebEstadoVazio('person', 'Nenhum cliente cadastrado.'); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <?php $id = (int) $cliente['id']; ?>
                        <tr>
                            <th scope="row">
                                <span class="sw-row-name"><span class="sw-icon-tile sw-tone-indigo"><i class="bi bi-person" aria-hidden="true"></i></span><?= clienteEscapar($cliente['nome']) ?></span>
                            </th>
                            <td><?= clienteEscapar($cliente['cpf']) ?></td>
                            <td><?= clienteEscapar($cliente['email'] ?? '—') ?></td>
                            <td><?= clienteEscapar($cliente['telefone'] ?? '—') ?></td>
                            <td>
                                <div class="sw-row-actions">
                                    <a href="cliente_editar.php?id=<?= $id ?>"
                                       class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</a>
                                    <?php if ($cliente['possui_vendas']): ?>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                aria-describedby="vinculos-<?= $id ?>" disabled>Excluir</button>
                                    <?php else: ?>
                                        <form action="cliente_excluir.php" method="POST"
                                              onsubmit="return confirm(this.dataset.confirmacao);"
                                              data-confirmacao="Excluir «<?= clienteEscapar($cliente['nome']) ?>»?">
                                            <input type="hidden" name="id" value="<?= $id ?>">
                                            <input type="hidden" name="csrf_token" value="<?= clienteEscapar($token) ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <?php if ($cliente['possui_vendas']): ?>
                                    <small id="vinculos-<?= $id ?>" class="sw-row-hint">
                                        Há vendas vinculadas.
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
