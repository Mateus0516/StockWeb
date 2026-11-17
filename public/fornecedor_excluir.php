<?php
require_once __DIR__ . '/fornecedor_funcoes.php';

fornecedorExigirPost();

$id = fornecedorId($_POST['id'] ?? null);
if ($id === false) {
    fornecedorRedirecionar('fornecedores.php', 'danger', 'Fornecedor inválido.');
}

require_once '/var/www/stockweb/config/database.php';

$mensagemVinculo = 'O fornecedor possui produtos vinculados e não pode ser excluído.';

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT id FROM fornecedores WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() === false) {
        $pdo->rollBack();
        fornecedorRedirecionar('fornecedores.php', 'danger', 'Fornecedor não encontrado.');
    }

    $stmt = $pdo->prepare('
        DELETE FROM fornecedores
        WHERE id = ? AND NOT EXISTS (SELECT 1 FROM produtos WHERE fornecedor_id = ?)
    ');
    $stmt->execute([$id, $id]);
    $excluido = $stmt->rowCount() === 1;
    $pdo->commit();
} catch (PDOException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) ($erro->errorInfo[1] ?? 0) === 1451) {
        fornecedorRedirecionar('fornecedores.php', 'danger', $mensagemVinculo);
    }

    error_log($erro->getMessage());
    fornecedorRedirecionar('fornecedores.php', 'danger', 'Não foi possível excluir o fornecedor. Tente novamente.');
}

if (!$excluido) {
    fornecedorRedirecionar('fornecedores.php', 'danger', $mensagemVinculo);
}

fornecedorRedirecionar('fornecedores.php', 'success', 'Fornecedor excluído com sucesso.');
