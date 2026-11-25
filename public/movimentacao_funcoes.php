<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

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
