<?php
require_once __DIR__ . '/cliente_funcoes.php';

$id = clienteId($_GET['id'] ?? null);
if ($id === false) {
    clienteRedirecionar('clientes.php', 'danger', 'Cliente inválido.');
}

require_once '/var/www/stockweb/config/database.php';

try {
    $stmt = $pdo->prepare('SELECT nome, cpf, email, telefone FROM clientes WHERE id = ?');
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    clienteRedirecionar('clientes.php', 'danger', 'Não foi possível carregar o cliente.');
}

if (!$cliente) {
    clienteRedirecionar('clientes.php', 'danger', 'Cliente não encontrado.');
}

require __DIR__ . '/cliente_novo.php';
