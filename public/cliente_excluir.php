<?php
require_once __DIR__ . '/cliente_funcoes.php';

clienteExigirPost();

$id = clienteId($_POST['id'] ?? null);
if ($id === false) {
    clienteRedirecionar('clientes.php', 'danger', 'Cliente inválido.');
}

require_once '/var/www/stockweb/config/database.php';

$mensagemVinculo = 'O cliente possui vendas vinculadas e não pode ser excluído.';

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT id FROM clientes WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() === false) {
        $pdo->rollBack();
        clienteRedirecionar('clientes.php', 'danger', 'Cliente não encontrado.');
    }

    $stmt = $pdo->prepare('
        DELETE FROM clientes
        WHERE id = ? AND NOT EXISTS (SELECT 1 FROM vendas WHERE cliente_id = ?)
    ');
    $stmt->execute([$id, $id]);
    $excluido = $stmt->rowCount() === 1;
    $pdo->commit();
} catch (PDOException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) ($erro->errorInfo[1] ?? 0) === 1451) {
        clienteRedirecionar('clientes.php', 'danger', $mensagemVinculo);
    }

    error_log($erro->getMessage());
    clienteRedirecionar('clientes.php', 'danger', 'Não foi possível excluir o cliente. Tente novamente.');
}

if (!$excluido) {
    clienteRedirecionar('clientes.php', 'danger', $mensagemVinculo);
}

clienteRedirecionar('clientes.php', 'success', 'Cliente excluído com sucesso.');
