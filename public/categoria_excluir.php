<?php
require_once __DIR__ . '/categoria_funcoes.php';

categoriaExigirPost();

$id = categoriaId($_POST['id'] ?? null);
if ($id === false) {
    categoriaRedirecionar('categorias.php', 'danger', 'Categoria inválida.');
}

require_once '/var/www/stockweb/config/database.php';

$mensagemVinculo = 'A categoria possui produtos vinculados, inclusive inativos, e não pode ser excluída.';

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT id FROM categorias WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() === false) {
        $pdo->rollBack();
        categoriaRedirecionar('categorias.php', 'danger', 'Categoria não encontrada.');
    }

    $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = ? AND NOT EXISTS (SELECT 1 FROM produtos WHERE categoria_id = ?)');
    $stmt->execute([$id, $id]);
    $excluida = $stmt->rowCount() === 1;
    $pdo->commit();
} catch (PDOException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) ($erro->errorInfo[1] ?? 0) === 1451) {
        categoriaRedirecionar('categorias.php', 'danger', 'A categoria possui registros vinculados e não pode ser excluída.');
    }

    error_log($erro->getMessage());
    categoriaRedirecionar('categorias.php', 'danger', 'Não foi possível excluir a categoria. Tente novamente.');
}

if (!$excluida) {
    categoriaRedirecionar('categorias.php', 'danger', $mensagemVinculo);
}

categoriaRedirecionar('categorias.php', 'success', 'Categoria excluída com sucesso.');
