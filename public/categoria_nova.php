
<?php
session_start();

// Protege a página contra acesso sem login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Gera o token de proteção CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nova Categoria | StockWeb</title>

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

        .form-card {
            border: none;
            border-radius: 15px;
        }
    </style>
</head>

<body>

    <!-- Barra de navegação -->
    <nav class="navbar navbar-dark">
        <div class="container">

            <a
                class="navbar-brand fw-bold"
                href="dashboard.php"
            >
                StockWeb
            </a>

            <div class="d-flex align-items-center gap-3">

                <span class="text-white d-none d-sm-inline">
                    <?= e($nomeUsuario) ?>
                </span>

                <a
                    href="logout.php"
                    class="btn btn-outline-light btn-sm"
                >
                    Sair
                </a>

            </div>
        </div>
    </nav>

    <main class="container py-5">

        <!-- Cabeçalho -->
        <div class="mb-4">

            <h1 class="fw-bold">
                Nova Categoria
            </h1>

            <p class="text-muted">
                Cadastre uma nova categoria para organizar os produtos.
            </p>

        </div>

        <!-- Formulário -->
        <div class="card form-card shadow-sm">

            <div class="card-body p-4">

                <form
                    action="categoria_salvar.php"
                    method="POST"
                >

                    <!-- Proteção CSRF -->
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['csrf_token']) ?>"
                    >

                    <!-- Nome -->
                    <div class="mb-3">

                        <label
                            for="nome"
                            class="form-label fw-semibold"
                        >
                            Nome da categoria
                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            class="form-control"
                            maxlength="100"
                            placeholder="Ex.: Equipamentos de Rede"
                            required
                        >

                    </div>

                    <!-- Descrição -->
                    <div class="mb-4">

                        <label
                            for="descricao"
                            class="form-label fw-semibold"
                        >
                            Descrição
                        </label>

                        <textarea
                            id="descricao"
                            name="descricao"
                            class="form-control"
                            rows="4"
                            placeholder="Descreva os produtos desta categoria"
                        ></textarea>

                        <div class="form-text">
                            Campo opcional.
                        </div>

                    </div>

                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">

                        <a
                            href="categorias.php"
                            class="btn btn-outline-secondary"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Salvar categoria
                        </button>

                    </div>

                </form>

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
