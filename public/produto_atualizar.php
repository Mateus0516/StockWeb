<?php
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Permite apenas requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: produtos.php');
    exit;
}

// Proteção contra CSRF
$tokenSessao = $_SESSION['csrf_token'] ?? null;
$tokenFormulario = $_POST['csrf_token'] ?? null;

if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    http_response_code(403);
    exit('Formulário inválido. Atualize a página e tente novamente.');
}

// Conexão com o banco de dados
require_once '/var/www/stockweb/config/database.php';

// Recebe os dados enviados
$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

$nome = trim((string) ($_POST['nome'] ?? ''));
$descricao = trim((string) ($_POST['descricao'] ?? ''));
$preco = trim((string) ($_POST['preco'] ?? ''));

$quantidade = filter_var(
    $_POST['quantidade'] ?? null,
    FILTER_VALIDATE_INT
);

$estoqueMinimo = filter_var(
    $_POST['estoque_minimo'] ?? null,
    FILTER_VALIDATE_INT
);

$categoriaId = filter_var(
    $_POST['categoria_id'] ?? null,
    FILTER_VALIDATE_INT
);

$fornecedorId = filter_var(
    $_POST['fornecedor_id'] ?? null,
    FILTER_VALIDATE_INT
);

// Valida o preço em formato decimal
$precoValido = preg_match(
    '/^\d{1,8}(\.\d{1,2})?$/',
    $preco
) === 1;

// Valida todos os campos
if (
    $id === false || $id === null || $id <= 0 ||
    $nome === '' ||
    mb_strlen($nome, 'UTF-8') > 150 ||
    !$precoValido ||
    $quantidade === false || $quantidade === null ||
    $quantidade < 0 ||
    $estoqueMinimo === false || $estoqueMinimo === null ||
    $estoqueMinimo < 0 ||
    $categoriaId === false || $categoriaId === null ||
    $categoriaId <= 0 ||
    $fornecedorId === false || $fornecedorId === null ||
    $fornecedorId <= 0
) {
    http_response_code(422);
    exit('Dados inválidos. Volte ao formulário e confira os campos.');
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
        exit('Produto não encontrado ou inativo.');
    }

    // Verifica se a categoria existe
    $stmt = $pdo->prepare("
        SELECT id
        FROM categorias
        WHERE id = ?
    ");

    $stmt->execute([$categoriaId]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(422);
        exit('Categoria inválida.');
    }

    // Verifica se o fornecedor existe
    $stmt = $pdo->prepare("
        SELECT id
        FROM fornecedores
        WHERE id = ?
    ");

    $stmt->execute([$fornecedorId]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(422);
        exit('Fornecedor inválido.');
    }

    // Atualiza as informações do produto
    $sql = "
        UPDATE produtos
        SET
            nome = :nome,
            descricao = :descricao,
            preco = :preco,
            quantidade = :quantidade,
            estoque_minimo = :estoque_minimo,
            categoria_id = :categoria_id,
            fornecedor_id = :fornecedor_id
        WHERE id = :id AND ativo = 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nome' => $nome,
        ':descricao' => $descricao !== '' ? $descricao : null,
        ':preco' => $preco,
        ':quantidade' => $quantidade,
        ':estoque_minimo' => $estoqueMinimo,
        ':categoria_id' => $categoriaId,
        ':fornecedor_id' => $fornecedorId,
        ':id' => $id
    ]);

    // Redireciona após a atualização
    header('Location: produtos.php?atualizado=1');
    exit;

} catch (PDOException $e) {

    // Registra o erro sem expor informações do banco
    error_log(
        'Erro ao atualizar produto: ' . $e->getMessage()
    );

    http_response_code(500);

    exit('Erro ao atualizar o produto. Tente novamente.');
}
