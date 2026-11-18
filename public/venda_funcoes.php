<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '/var/www/stockweb/includes/layout.php';

const VENDA_MAX_ITENS = 100;
const VENDA_MAX_CENTAVOS = 9_999_999_999;

function vendaEscapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function vendaInteiro($valor) {
    return filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 2_147_483_647],
    ]);
}

function vendaCentavos($decimal) {
    [$inteiros, $centavos] = explode('.', $decimal);
    return (int) $inteiros * 100 + (int) $centavos;
}

function vendaDecimal($centavos) {
    return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
}

function vendaMoeda($decimal) {
    return 'R$ ' . number_format((float) $decimal, 2, ',', '.');
}

function vendaData($data) {
    return (new DateTimeImmutable($data))->format('d/m/Y H:i');
}

function vendaToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function vendaRedirecionar($destino, $tipo, $mensagem, $dados = []) {
    $_SESSION['venda_aviso'] = compact('destino', 'tipo', 'mensagem', 'dados');
    header('Location: ' . $destino, true, 303);
    exit;
}

function vendaAviso($destino) {
    $aviso = $_SESSION['venda_aviso'] ?? null;
    if (($aviso['destino'] ?? null) === $destino) {
        unset($_SESSION['venda_aviso']);
        return $aviso;
    }

    return null;
}

function vendaCabecalho($titulo, $aviso = null) {
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= vendaEscapar($titulo) ?> | StockWeb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php stockwebLayoutEstilos(); ?>
</head>
<body style="background-color: #f4f6fa">
    <?php stockwebLayoutAbrir('vendas'); ?>
    <main class="container py-5">
        <?php if ($aviso): ?>
            <div class="alert alert-<?= vendaEscapar($aviso['tipo']) ?>" role="alert">
                <?= vendaEscapar($aviso['mensagem']) ?>
            </div>
        <?php endif; ?>
    <?php
}

function vendaRodape() {
    ?>
    </main>
    <?php stockwebLayoutFechar(); ?>
</body>
</html>
    <?php
}
