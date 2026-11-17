<?php
require_once __DIR__ . '/fornecedor_funcoes.php';

$id = fornecedorId($_GET['id'] ?? null);
if ($id === false) {
    fornecedorRedirecionar('fornecedores.php', 'danger', 'Fornecedor inválido.');
}

require_once '/var/www/stockweb/config/database.php';

try {
    $stmt = $pdo->prepare('SELECT nome, cnpj, email, telefone FROM fornecedores WHERE id = ?');
    $stmt->execute([$id]);
    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    fornecedorRedirecionar('fornecedores.php', 'danger', 'Não foi possível carregar o fornecedor.');
}

if (!$fornecedor) {
    fornecedorRedirecionar('fornecedores.php', 'danger', 'Fornecedor não encontrado.');
}

require __DIR__ . '/fornecedor_novo.php';
