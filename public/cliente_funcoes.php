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
