<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}


require_once '/var/www/stockweb/config/database.php';

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    $_SESSION['erro_login'] = 'Preencha todos os campos.';
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, nome, email, senha_hash, perfil
     FROM usuarios
     WHERE email = ?
     LIMIT 1'
);

$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
    $_SESSION['erro_login'] = 'E-mail ou senha incorretos.';
    header('Location: login.php');
    exit;
}

// Protege contra fixação de sessão
session_regenerate_id(true);

// Armazena somente os dados necessários
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_perfil'] = $usuario['perfil'];

// Redireciona para o painel administrativo
header('Location: dashboard.php');
exit;
