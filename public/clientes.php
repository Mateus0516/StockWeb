<?php
require_once __DIR__ . '/cliente_funcoes.php';
require_once '/var/www/stockweb/config/database.php';

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

clienteCabecalho('Clientes', $aviso);
?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="fw-bold mb-0">Gerenciamento de Clientes</h1>
            <a href="cliente_novo.php" class="btn btn-primary">+ Novo cliente</a>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary mb-4">Voltar ao painel</a>

        <?php if (isset($clientes)): ?>
            <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">Nome</th>
                                <th scope="col">CPF</th>
                                <th scope="col">E-mail</th>
                                <th scope="col">Telefone</th>
                                <th scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$clientes): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">Nenhum cliente cadastrado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <?php $id = (int) $cliente['id']; ?>
                                <tr>
                                    <th scope="row" class="ps-4"><?= clienteEscapar($cliente['nome']) ?></th>
                                    <td><?= clienteEscapar($cliente['cpf']) ?></td>
                                    <td><?= clienteEscapar($cliente['email'] ?? '—') ?></td>
                                    <td><?= clienteEscapar($cliente['telefone'] ?? '—') ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="cliente_editar.php?id=<?= $id ?>"
                                               class="btn btn-outline-primary btn-sm">Editar</a>
                                            <?php if ($cliente['possui_vendas']): ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                        aria-describedby="vinculos-<?= $id ?>" disabled>Excluir</button>
                                                <small id="vinculos-<?= $id ?>" class="text-muted w-100">
                                                    Há vendas vinculadas.
                                                </small>
                                            <?php else: ?>
                                                <form action="cliente_excluir.php" method="POST"
                                                      onsubmit="return confirm('Excluir este cliente?');">
                                                    <input type="hidden" name="id" value="<?= $id ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= clienteEscapar($token) ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
<?php clienteRodape();
