<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}


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
