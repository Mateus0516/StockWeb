<?php
session_start();

// Impede acesso sem autenticação
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '/var/www/stockweb/config/database.php';
require_once '/var/www/stockweb/includes/layout.php';

// Gera o token de proteção CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Busca categorias e fornecedores no banco
$categorias = $pdo->query(
    "SELECT id, nome FROM categorias ORDER BY nome"
)->fetchAll();

$fornecedores = $pdo->query(
    "SELECT id, nome FROM fornecedores ORDER BY nome"
)->fetchAll();

require '/var/www/stockweb/includes/produto_formulario.php';
