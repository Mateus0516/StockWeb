<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '/var/www/stockweb/includes/layout.php';
require_once '/var/www/stockweb/includes/movimentacao_registro.php';

function movimentacaoEscapar($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function movimentacaoInteiro($valor) {
    return filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => ESTOQUE_MAXIMO],
    ]);
}

function movimentacaoToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function movimentacaoRedirecionar($destino, $tipo, $mensagem, $dados = []): void {
    $_SESSION['movimentacao_aviso'] = compact('destino', 'tipo', 'mensagem', 'dados');
    header('Location: ' . $destino, true, 303);
    exit;
}

function movimentacaoAviso($destino): ?array {
    $aviso = $_SESSION['movimentacao_aviso'] ?? null;
    if (($aviso['destino'] ?? null) === $destino) {
        unset($_SESSION['movimentacao_aviso']);
        return $aviso;
    }

    return null;
}

function movimentacaoCabecalho($titulo, $aviso = null): void {
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= movimentacaoEscapar($titulo) ?> | StockWeb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php stockwebLayoutEstilos(); ?>
</head>
<body style="background-color: #f4f6fa">
    <?php stockwebLayoutAbrir('movimentacoes'); ?>
    <main class="container py-5">
        <?php if ($aviso): ?>
            <div class="alert alert-<?= movimentacaoEscapar($aviso['tipo']) ?>" role="alert">
                <?= movimentacaoEscapar($aviso['mensagem']) ?>
            </div>
        <?php endif; ?>
    <?php
}

function movimentacaoRodape(): void {
    ?>
    </main>
    <?php stockwebLayoutFechar(); ?>
</body>
</html>
    <?php
}
