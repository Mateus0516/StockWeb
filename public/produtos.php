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

// Proteção contra HTML malicioso
function e($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Mensagens de sucesso
$produtoAtualizado = isset($_GET['atualizado'])
    && $_GET['atualizado'] === '1';

$produtoExcluido = isset($_GET['excluido'])
    && $_GET['excluido'] === '1';
require_once '/var/www/stockweb/includes/layout.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Produtos | StockWeb</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f4f6fa;
        }

        .table-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .acoes {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .acoes form {
            margin: 0;
        }

        .acoes .btn {
            white-space: nowrap;
        }
    </style>
    <?php stockwebLayoutEstilos(); ?>
</head>

<body>
    <?php stockwebLayoutAbrir('produtos'); ?>
    <main class="container py-5">

        <!-- Cabeçalho -->
        <div class="d-flex flex-wrap justify-content-between
                    align-items-center gap-3 mb-4">

            <div>
                <h1 class="fw-bold mb-1">
                    Gerenciamento de Produtos
                </h1>

                <p class="text-muted mb-0">
                    Consulte, cadastre, edite e exclua produtos do estoque.
                </p>
            </div>

            <a href="produto_novo.php"
               class="btn btn-primary">
                + Novo produto
            </a>

        </div>

        <!-- Voltar ao dashboard -->
        <div class="mb-4">
            <a href="dashboard.php"
               class="btn btn-outline-secondary">
                Voltar ao painel
            </a>
        </div>

        <!-- Mensagem de atualização -->
        <?php if ($produtoAtualizado): ?>

            <div class="alert alert-success alert-dismissible fade show"
                 role="alert">

                Produto atualizado com sucesso!

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fechar">
                </button>

            </div>

        <?php endif; ?>

        <!-- Mensagem de exclusão -->
        <?php if ($produtoExcluido): ?>

            <div class="alert alert-success alert-dismissible fade show"
                 role="alert">

                Produto excluído com sucesso!

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fechar">
                </button>

            </div>

        <?php endif; ?>

        <!-- Total de produtos -->
        <div class="alert alert-primary">

            Total de produtos ativos:

            <strong>
                <?= count($produtos) ?>
            </strong>

        </div>

        <!-- Tabela de produtos -->
        <div class="card table-card shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Fornecedor</th>
                                <th>Preço</th>
                                <th>Quantidade</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (empty($produtos)): ?>

                                <tr>
                                    <td colspan="8"
                                        class="text-center py-4">

                                        Nenhum produto cadastrado.

                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($produtos as $produto): ?>

                                    <?php
                                    $estoqueBaixo =
                                        (int) $produto['quantidade']
                                        <= (int) $produto['estoque_minimo'];
                                    ?>

                                    <tr>

                                        <!-- ID -->
                                        <td class="ps-4">
                                            <?= (int) $produto['id'] ?>
                                        </td>

                                        <!-- Nome -->
                                        <td class="fw-semibold">
                                            <?= e($produto['nome']) ?>
                                        </td>

                                        <!-- Categoria -->
                                        <td>
                                            <?= e($produto['categoria']) ?>
                                        </td>

                                        <!-- Fornecedor -->
                                        <td>
                                            <?= e($produto['fornecedor']) ?>
                                        </td>

                                        <!-- Preço -->
                                        <td>
                                            R$
                                            <?= number_format(
                                                (float) $produto['preco'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>
                                        </td>

                                        <!-- Quantidade -->
                                        <td>
                                            <?= (int) $produto['quantidade'] ?>
                                        </td>

                                        <!-- Status -->
                                        <td>

                                            <?php if ($estoqueBaixo): ?>

                                                <span class="badge bg-danger">
                                                    Estoque baixo
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-success">
                                                    Disponível
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <!-- Ações -->
                                        <td>

                                            <div class="acoes">

                                                <!-- Editar -->
                                                <a
                                                    href="produto_editar.php?id=<?= (int) $produto['id'] ?>"
                                                    class="btn btn-outline-primary btn-sm"
                                                >
                                                    Editar
                                                </a>

                                                <!-- Excluir -->
                                                <form
                                                    action="produto_excluir.php"
                                                    method="POST"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este produto?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int) $produto['id'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= e($_SESSION['csrf_token']) ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-outline-danger btn-sm"
                                                    >
                                                        Excluir
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </main>

    <!-- Rodapé -->
    <footer class="text-center text-muted py-4">

        <small>
            StockWeb © 2026 — Gerenciamento de Estoque
        </small>

    </footer>

    <?php stockwebLayoutFechar(); ?>
</body>
</html>
