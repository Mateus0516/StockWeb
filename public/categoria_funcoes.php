<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function categoriaToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function categoriaRedirecionar($destino, $tipo, $mensagem, $dados = null) {
    $_SESSION['categoria_aviso'] = compact('tipo', 'mensagem', 'destino', 'dados');
    header('Location: ' . $destino, true, 303);
    exit;
}

function categoriaAviso($destino) {
    $aviso = $_SESSION['categoria_aviso'] ?? null;
    if (($aviso['destino'] ?? null) !== $destino) {
        return null;
    }

    unset($_SESSION['categoria_aviso']);
    return $aviso;
}

function categoriaExigirPost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: categorias.php');
        exit;
    }

    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals(categoriaToken(), $token)) {
        categoriaRedirecionar('categorias.php', 'danger', 'Formulário inválido. Atualize a página e tente novamente.');
    }
}

function categoriaId($valor) {
    return filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}
