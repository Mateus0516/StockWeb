<?php
require_once __DIR__ . '/categoria_funcoes.php';

$id = categoriaId($_GET['id'] ?? null);
if ($id === false) {
    categoriaRedirecionar('categorias.php', 'danger', 'Categoria inválida.');
}

require_once '/var/www/stockweb/config/database.php';

try {
    $stmt = $pdo->prepare('SELECT nome, descricao FROM categorias WHERE id = ?');
    $stmt->execute([$id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    categoriaRedirecionar('categorias.php', 'danger', 'Não foi possível carregar a categoria.');
}

if (!$categoria) {
    categoriaRedirecionar('categorias.php', 'danger', 'Categoria não encontrada.');
}

require __DIR__ . '/categoria_nova.php';
