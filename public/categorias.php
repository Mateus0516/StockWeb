<?php
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Conexão com o banco
require_once '/var/www/stockweb/config/database.php';

// Busca categorias e quantidade de produtos ativos
$stmt = $pdo->query("
    SELECT
        c.id,
        c.nome,
        c.descricao,
        COUNT(p.id) AS total_produtos
    FROM categorias c
    LEFT JOIN produtos p
        ON p.categoria_id = c.id
        AND p.ativo = 1
    GROUP BY c.id, c.nome, c.descricao
    ORDER BY c.nome ASC
");

$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proteção contra HTML malicioso
function e($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Categorias | StockWeb</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f4f6fa;
        }

        .navbar {
            background-color: #0d6efd;
        }

        .table-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
    </style>
</head>

<body>

    <!-- Navegação -->
    <nav class="navbar navbar-dark">
        <div class="container">

            <a class="navbar-brand fw-bold"
               href="dashboard.php">
                StockWeb
            </a>

            <div class="d-flex align-items-center gap-3">

                <span class="text-white d-none d-sm-inline">
                    <?= e($nomeUsuario) ?>
                </span>

                <a href="logout.php"
                   class="btn btn-outline-light btn-sm">
                    Sair
                </a>

            </div>

        </div>
    </nav>

    <main class="container py-5">

        <!-- Cabeçalho -->
        <div class="d-flex flex-wrap justify-content-between
                    align-items-center gap-3 mb-4">

            <div>
                <h1 class="fw-bold mb-1">
                    Gerenciamento de Categorias
                </h1>

                <p class="text-muted mb-0">
                    Organize as categorias dos produtos do estoque.
                </p>
            </div>

            <button
                class="btn btn-primary"
                type="button"
                disabled
                title="Cadastro será implementado na próxima etapa"
            >
                + Nova categoria
            </button>

        </div>

        <!-- Voltar ao dashboard -->
        <div class="mb-4">
            <a href="dashboard.php"
               class="btn btn-outline-secondary">
                Voltar ao painel
            </a>
        </div>

        <!-- Total de categorias -->
        <div class="alert alert-primary">
            Total de categorias cadastradas:
            <strong><?= count($categorias) ?></strong>
        </div>

        <!-- Tabela -->
        <div class="card table-card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Categoria</th>
                                <th>Descrição</th>
                                <th>Produtos ativos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (empty($categorias)): ?>

                                <tr>
                                    <td colspan="5"
                                        class="text-center py-4">
                                        Nenhuma categoria cadastrada.
                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($categorias as $categoria): ?>

                                    <tr>

                                        <td class="ps-4">
                                            <?= (int) $categoria['id'] ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= e($categoria['nome']) ?>
                                        </td>

                                        <td>
                                            <?= e($categoria['descricao'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-primary">
                                                <?= (int) $categoria['total_produtos'] ?>
                                            </span>
                                        </td>

                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                disabled
                                            >
                                                Editar
                                            </button>
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

    <footer class="text-center text-muted py-4">
        <small>
            StockWeb © 2026 — Gerenciamento de Estoque
        </small>
    </footer>

</body>
</html>
