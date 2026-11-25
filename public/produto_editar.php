<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '/var/www/stockweb/config/database.php';
require_once '/var/www/stockweb/includes/layout.php';

// Valida o ID recebido pela URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: produtos.php');
    exit;
}

// Busca o produto
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    http_response_code(404);
    stockwebErroProduto('Produto não encontrado.');
}

// Busca categorias e fornecedores
$categorias = $pdo->query(
    "SELECT id, nome FROM categorias ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$fornecedores = $pdo->query(
    "SELECT id, nome FROM fornecedores ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

// Token de segurança
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require '/var/www/stockweb/includes/produto_formulario.php';
