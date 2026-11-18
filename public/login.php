<?php
session_start();

$erroLogin = $_SESSION['erro_login'] ?? null;
unset($_SESSION['erro_login']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | StockWeb</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light d-flex align-items-center min-vh-100">

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">

                <div class="text-center mb-4">
                    <h1 class="fw-bold text-primary">StockWeb</h1>
                    <p class="text-muted">
                        Sistema de Gerenciamento de Estoque
                    </p>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">

                        <h2 class="h4 fw-bold mb-4">
                            Entrar no sistema
                        </h2>

                        <?php if (is_string($erroLogin) && $erroLogin !== ''): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($erroLogin, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <form action="autenticar.php" method="POST">

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    E-mail
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Digite seu e-mail"
                                    autocomplete="username"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label for="senha" class="form-label">
                                    Senha
                                </label>

                                <input
                                    type="password"
                                    id="senha"
                                    name="senha"
                                    class="form-control"
                                    placeholder="Digite sua senha"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100 py-2"
                            >
                                Entrar
                            </button>

                        </form>

                    </div>
                </div>

                <p class="text-center text-muted small mt-4">
                    StockWeb © 2026
                </p>

            </div>
        </div>
    </main>

</body>
</html>
