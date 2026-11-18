<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

function fornecedorEscapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fornecedorId($valor) {
    return filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

function fornecedorToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function fornecedorRedirecionar($destino, $tipo, $mensagem, $dados = null) {
    $_SESSION['fornecedor_aviso'] = compact('tipo', 'mensagem', 'dados', 'destino');
    header('Location: ' . $destino, true, 303);
    exit;
}

function fornecedorExigirPost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: fornecedores.php');
        exit;
    }

    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals(fornecedorToken(), $token)) {
        fornecedorRedirecionar('fornecedores.php', 'danger', 'Formulário inválido. Atualize a página e tente novamente.');
    }
}

function fornecedorAviso($destino) {
    $aviso = $_SESSION['fornecedor_aviso'] ?? null;
    if (($aviso['destino'] ?? null) !== $destino) {
        return null;
    }

    unset($_SESSION['fornecedor_aviso']);
    return $aviso;
}

require_once '/var/www/stockweb/includes/layout.php';

function fornecedorCabecalho($titulo, $aviso) {
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= fornecedorEscapar($titulo) ?> | StockWeb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #f4f6fa; }</style>
    <?php stockwebLayoutEstilos(); ?>
</head>
<body>
    <?php stockwebLayoutAbrir('fornecedores'); ?>
    <main class="container py-5">
        <?php if ($aviso !== null): ?>
            <div class="alert alert-<?= fornecedorEscapar($aviso['tipo']) ?>" role="alert">
                <?= fornecedorEscapar($aviso['mensagem']) ?>
            </div>
        <?php endif; ?>
    <?php
}

function fornecedorRodape() {
    ?>
    </main>
    <footer class="text-center text-muted py-4">
        <small>StockWeb © 2026 — Gerenciamento de Estoque</small>
    </footer>
    <?php stockwebLayoutFechar(); ?>
</body>
</html>
    <?php
}
