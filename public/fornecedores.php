<?php
require_once __DIR__ . '/fornecedor_funcoes.php';
require_once '/var/www/stockweb/config/database.php';

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

fornecedorCabecalho('Fornecedores', $aviso);
?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="fw-bold mb-0">Gerenciamento de Fornecedores</h1>
            <a href="fornecedor_novo.php" class="btn btn-primary">+ Novo fornecedor</a>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary mb-4">Voltar ao painel</a>

        <?php if (isset($fornecedores)): ?>
            <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">Nome</th>
                                <th scope="col">CNPJ</th>
                                <th scope="col">E-mail</th>
                                <th scope="col">Telefone</th>
                                <th scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$fornecedores): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">Nenhum fornecedor cadastrado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($fornecedores as $fornecedor): ?>
                                <?php $id = (int) $fornecedor['id']; ?>
                                <tr>
                                    <th scope="row" class="ps-4"><?= fornecedorEscapar($fornecedor['nome']) ?></th>
                                    <td><?= fornecedorEscapar($fornecedor['cnpj']) ?></td>
                                    <td><?= fornecedorEscapar($fornecedor['email'] ?? '—') ?></td>
                                    <td><?= fornecedorEscapar($fornecedor['telefone'] ?? '—') ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="fornecedor_editar.php?id=<?= $id ?>"
                                               class="btn btn-outline-primary btn-sm">Editar</a>
                                            <?php if ($fornecedor['possui_produtos']): ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                        aria-describedby="vinculos-<?= $id ?>" disabled>Excluir</button>
                                                <small id="vinculos-<?= $id ?>" class="text-muted w-100">
                                                    Há produtos vinculados.
                                                </small>
                                            <?php else: ?>
                                                <form action="fornecedor_excluir.php" method="POST"
                                                      onsubmit="return confirm('Excluir este fornecedor?');">
                                                    <input type="hidden" name="id" value="<?= $id ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= fornecedorEscapar($token) ?>">
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
<?php fornecedorRodape();
