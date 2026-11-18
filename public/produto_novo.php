<?php
session_start();

// Impede acesso sem autenticação
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '/var/www/stockweb/config/database.php';

// Gera o token de proteção CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Busca categorias e fornecedores no banco
$categorias = $pdo->query(
    "SELECT id, nome FROM categorias ORDER BY nome"
)->fetchAll();

$fornecedores = $pdo->query(
    "SELECT id, nome FROM fornecedores ORDER BY nome"
)->fetchAll();

// Protege os textos exibidos na página
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

    <title>Novo Produto | StockWeb</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f4f6fa;
        }

        .form-card {
            border: none;
            border-radius: 16px;
        }
    </style>
    <?php stockwebLayoutEstilos(); ?>
</head>

<body>
    <?php stockwebLayoutAbrir('produtos'); ?>
    <main class="container py-5">

        <div class="mb-4">
            <h1 class="fw-bold">Cadastrar Produto</h1>

            <p class="text-muted">
                Preencha os dados para adicionar um novo produto ao estoque.
            </p>
        </div>

        <div class="card form-card shadow-sm">
            <div class="card-body p-4">

                <form action="produto_salvar.php" method="POST">

                    <!-- Token de segurança CSRF -->
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['csrf_token']) ?>"
                    >

                    <!-- Nome -->
                    <div class="mb-3">
                        <label for="nome" class="form-label">
                            Nome do produto
                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            class="form-control"
                            maxlength="150"
                            required
                        >
                    </div>

                    <!-- Descrição -->
                    <div class="mb-3">
                        <label for="descricao" class="form-label">
                            Descrição
                        </label>

                        <textarea
                            id="descricao"
                            name="descricao"
                            class="form-control"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="row">

                        <!-- Preço -->
                        <div class="col-md-4 mb-3">
                            <label for="preco" class="form-label">
                                Preço (R$)
                            </label>

                            <input
                                type="number"
                                id="preco"
                                name="preco"
                                class="form-control"
                                min="0"
                                max="99999999.99"
                                step="0.01"
                                required
                            >
                        </div>

                        <!-- Quantidade -->
                        <div class="col-md-4 mb-3">
                            <label for="quantidade" class="form-label">
                                Quantidade inicial
                            </label>

                            <input
                                type="number"
                                id="quantidade"
                                name="quantidade"
                                class="form-control"
                                min="0"
                                step="1"
                                value="0"
                                required
                            >
                        </div>

                        <!-- Estoque mínimo -->
                        <div class="col-md-4 mb-3">
                            <label for="estoque_minimo" class="form-label">
                                Estoque mínimo
                            </label>

                            <input
                                type="number"
                                id="estoque_minimo"
                                name="estoque_minimo"
                                class="form-control"
                                min="0"
                                step="1"
                                value="5"
                                required
                            >
                        </div>

                    </div>

                    <div class="row">

                        <!-- Categoria -->
                        <div class="col-md-6 mb-3">
                            <label for="categoria_id" class="form-label">
                                Categoria
                            </label>

                            <select
                                id="categoria_id"
                                name="categoria_id"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Selecione uma categoria
                                </option>

                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int) $categoria['id'] ?>">
                                        <?= e($categoria['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Fornecedor -->
                        <div class="col-md-6 mb-3">
                            <label for="fornecedor_id" class="form-label">
                                Fornecedor
                            </label>

                            <select
                                id="fornecedor_id"
                                name="fornecedor_id"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Selecione um fornecedor
                                </option>

                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?= (int) $fornecedor['id'] ?>">
                                        <?= e($fornecedor['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2 mt-4">

                        <a
                            href="produtos.php"
                            class="btn btn-outline-secondary"
                        >
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Salvar produto
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </main>

    <?php stockwebLayoutFechar(); ?>
</body>
</html>
