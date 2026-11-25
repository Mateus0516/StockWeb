<?php
session_start();

require_once '/var/www/stockweb/includes/layout.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Aceita somente requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: produtos.php');
    exit;
}

// Verifica o token CSRF
$tokenSessao = $_SESSION['csrf_token'] ?? null;
$tokenFormulario = $_POST['csrf_token'] ?? null;

if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    $tokenSessao === '' ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    http_response_code(403);
    stockwebErroProduto('Formulário inválido. Atualize a página e tente novamente.');
}

// Conexão com o banco de dados
require_once '/var/www/stockweb/config/database.php';

// Valida o ID do produto
$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id <= 0) {
    http_response_code(422);
    stockwebErroProduto('ID do produto inválido.');
}

try {
    // Verifica se o produto existe e está ativo
    $stmt = $pdo->prepare("
        SELECT id
        FROM produtos
        WHERE id = ? AND ativo = 1
    ");

    $stmt->execute([$id]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(404);
        stockwebErroProduto('Produto não encontrado ou já excluído.');
    }

    // Exclusão lógica: desativa o produto
    $stmt = $pdo->prepare("
        UPDATE produtos
        SET ativo = 0
        WHERE id = ? AND ativo = 1
    ");

    $stmt->execute([$id]);

    // Retorna à listagem
    header('Location: produtos.php?excluido=1');
    exit;

} catch (PDOException $e) {
    error_log(
        'Erro ao excluir produto: ' . $e->getMessage()
    );

    http_response_code(500);
    stockwebErroProduto('Não foi possível excluir o produto.');
}
