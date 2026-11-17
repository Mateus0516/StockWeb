<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '/var/www/stockweb/config/database.php';

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Valida o ID recebido pela URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: produtos.php');
    exit;
}

// Busca o produto
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    http_response_code(404);
    exit('Produto não encontrado.');
}

// Busca categorias e fornecedores
$categorias = $pdo->query(
    "SELECT id, nome FROM categorias ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$fornecedores = $pdo->query(
    "SELECT id, nome FROM fornecedores ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

// Token de segurança
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto | StockWeb</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            StockWeb
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white">
                <?= e($nomeUsuario) ?>
            </span>

            <a href="logout.php" class="btn btn-outline-light btn-sm">
                Sair
            </a>
        </div>
    </div>
</nav>

<main class="container py-5">

    <div class="mb-4">
        <h1 class="fw-bold">Editar Produto</h1>
        <p class="text-muted">
            Atualize as informações do produto cadastrado.
        </p>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            <form action="produto_atualizar.php" method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $produto['id'] ?>"
                >

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
                        value="<?= e($produto['nome']) ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">
                        Descrição
                    </label>
                    <textarea
                        id="descricao"
                        name="descricao"
                        class="form-control"
                        rows="3"
                    ><?= e($produto['descricao'] ?? '') ?></textarea>
                </div>

                <div class="row">
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
                            value="<?= e($produto['preco']) ?>"
                            required
                        >
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="quantidade" class="form-label">
                            Quantidade
                        </label>
                        <input
                            type="number"
                            id="quantidade"
                            name="quantidade"
                            class="form-control"
                            min="0"
                            step="1"
                            value="<?= (int) $produto['quantidade'] ?>"
                            required
                        >
                    </div>

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
                            value="<?= (int) $produto['estoque_minimo'] ?>"
                            required
                        >
                    </div>
                </div>

                <div class="row">
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
                            <?php foreach ($categorias as $categoria): ?>
                                <option
                                    value="<?= (int) $categoria['id'] ?>"
                                    <?= (int) $categoria['id'] === (int) $produto['categoria_id'] ? 'selected' : '' ?>
                                >
                                    <?= e($categoria['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

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
                            <?php foreach ($fornecedores as $fornecedor): ?>
                                <option
                                    value="<?= (int) $fornecedor['id'] ?>"
                                    <?= (int) $fornecedor['id'] === (int) $produto['fornecedor_id'] ? 'selected' : '' ?>
                                >
                                    <?= e($fornecedor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="produtos.php" class="btn btn-outline-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Salvar alterações
                    </button>
                </div>

            </form>

        </div>
    </div>

</main>

</body>
</html>
