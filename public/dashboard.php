<?php
session_start();

// Protege a página contra acesso sem login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Dados do usuário autenticado
$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil = $_SESSION['usuario_perfil'] ?? 'VENDEDOR';

// Conexão com o banco MySQL
require_once '/var/www/stockweb/config/database.php';

// Indicadores do dashboard
$totalProdutos = (int) $pdo
    ->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")
    ->fetchColumn();

$totalClientes = (int) $pdo
    ->query("SELECT COUNT(*) FROM clientes")
    ->fetchColumn();

$totalVendas = (int) $pdo
    ->query("SELECT COUNT(*) FROM vendas WHERE status = 'CONCLUIDA'")
    ->fetchColumn();

$estoqueBaixo = (int) $pdo
    ->query("SELECT COUNT(*) FROM produtos
             WHERE quantidade <= estoque_minimo AND ativo = 1")
    ->fetchColumn();

// Busca os produtos cadastrados
$produtos = $pdo->query("
    SELECT
        p.id,
        p.nome,
        p.preco,
        p.quantidade,
        p.estoque_minimo,
        c.nome AS categoria
    FROM produtos p
    INNER JOIN categorias c ON c.id = p.categoria_id
    WHERE p.ativo = 1
    ORDER BY p.id DESC
    LIMIT 10
")->fetchAll();

// Proteção para exibir textos na página
function e($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}
require_once '/var/www/stockweb/includes/layout.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | StockWeb</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f4f6fa;
        }

        .dashboard-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
        }

        .dashboard-number {
            font-size: 2rem;
            font-weight: 700;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .table-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
    </style>
    <?php stockwebLayoutEstilos(); ?>
</head>

<body>
    <?php stockwebLayoutAbrir('dashboard'); ?>
    <main class="container py-5">

        <div class="mb-4">
            <h1 class="fw-bold">Painel Administrativo</h1>

            <p class="text-muted">
                Bem-vindo, <?= e($nome) ?>!
                Gerencie seu estoque em um só lugar.
            </p>
        </div>

        <!-- Indicadores -->
        <div class="row g-4 mb-5">

            <!-- Produtos -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body p-4">

                        <p class="text-muted mb-2">
                            Produtos cadastrados
                        </p>

                        <div class="dashboard-number text-primary">
                            <?= $totalProdutos ?>
                        </div>

                        <small class="text-muted">
                            Produtos ativos
                        </small>

                    </div>
                </div>
            </div>

            <!-- Clientes -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body p-4">

                        <p class="text-muted mb-2">
                            Clientes cadastrados
                        </p>

                        <div class="dashboard-number text-success">
                            <?= $totalClientes ?>
                        </div>

                        <small class="text-muted">
                            Total de clientes
                        </small>

                    </div>
                </div>
            </div>

            <!-- Vendas -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body p-4">

                        <p class="text-muted mb-2">
                            Vendas realizadas
                        </p>

                        <div class="dashboard-number text-info">
                            <?= $totalVendas ?>
                        </div>

                        <small class="text-muted">
                            Vendas concluídas
                        </small>

                    </div>
                </div>
            </div>

            <!-- Estoque baixo -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body p-4">

                        <p class="text-muted mb-2">
                            Estoque baixo
                        </p>

                        <div class="dashboard-number text-danger">
                            <?= $estoqueBaixo ?>
                        </div>

                        <small class="text-muted">
                            Produtos que precisam de atenção
                        </small>

                    </div>
                </div>
            </div>

        </div>

        <!-- Tabela de produtos -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="section-title h4 mb-0">
                Produtos cadastrados
            </h2>
        </div>

        <div class="card table-card shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Preço</th>
                                <th>Estoque</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (empty($produtos)): ?>

                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        Nenhum produto cadastrado.
                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($produtos as $produto): ?>

                                    <?php
                                        $baixo = $produto['quantidade']
                                            <= $produto['estoque_minimo'];
                                    ?>

                                    <tr>

                                        <td class="ps-4">
                                            <?= (int) $produto['id'] ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= e($produto['nome']) ?>
                                        </td>

                                        <td>
                                            <?= e($produto['categoria']) ?>
                                        </td>

                                        <td>
                                            R$ <?= number_format(
                                                (float) $produto['preco'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= (int) $produto['quantidade'] ?>
                                        </td>

                                        <td>

                                            <?php if ($baixo): ?>

                                                <span class="badge bg-danger">
                                                    Estoque baixo
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-success">
                                                    Disponível
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <!-- Informações do usuário -->
        <div class="card border-0 shadow-sm rounded-4 mt-4">

            <div class="card-body p-4">

                <h2 class="h5 fw-bold mb-3">
                    Informações da conta
                </h2>

                <p>
                    <strong>Nome:</strong>
                    <?= e($nome) ?>
                </p>

                <p class="mb-0">
                    <strong>Perfil:</strong>
                    <?= e($perfil) ?>
                </p>

            </div>

        </div>

    </main>

    <footer class="text-center text-muted py-4">
        <small>
            StockWeb © 2026 — Sistema de Gerenciamento de Estoque
        </small>
    </footer>

    <?php stockwebLayoutFechar(); ?>
</body>
</html>
