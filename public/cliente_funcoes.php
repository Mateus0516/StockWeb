<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

function clienteEscapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function clienteId($valor) {
    return filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

function clienteToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function clienteRedirecionar($destino, $tipo, $mensagem, $dados = null) {
    $_SESSION['cliente_aviso'] = compact('tipo', 'mensagem', 'dados', 'destino');
    header('Location: ' . $destino, true, 303);
    exit;
}

function clienteExigirPost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: clientes.php');
        exit;
    }

    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals(clienteToken(), $token)) {
        clienteRedirecionar('clientes.php', 'danger', 'Formulário inválido. Atualize a página e tente novamente.');
    }
}

function clienteAviso($destino) {
    $aviso = $_SESSION['cliente_aviso'] ?? null;
    if (($aviso['destino'] ?? null) !== $destino) {
        return null;
    }

    unset($_SESSION['cliente_aviso']);
    return $aviso;
}

function clienteCabecalho($titulo, $aviso) {
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= clienteEscapar($titulo) ?> | StockWeb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #f4f6fa; }</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand fw-bold">StockWeb</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white d-none d-sm-inline">
                    <?= clienteEscapar($_SESSION['usuario_nome'] ?? 'Usuário') ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
            </div>
        </div>
    </nav>
    <main class="container py-5">
        <?php if ($aviso !== null): ?>
            <div class="alert alert-<?= clienteEscapar($aviso['tipo']) ?>" role="alert">
                <?= clienteEscapar($aviso['mensagem']) ?>
            </div>
        <?php endif; ?>
    <?php
}

function clienteRodape() {
    ?>
    </main>
    <footer class="text-center text-muted py-4">
        <small>StockWeb © 2026 — Gerenciamento de Estoque</small>
    </footer>
</body>
</html>
    <?php
}
