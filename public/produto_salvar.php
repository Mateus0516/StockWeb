<?php
session_start();

// Protege o cadastro contra acesso sem login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Aceita somente requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: produto_novo.php');
    exit;
}

// Validação do token CSRF
if (
    empty($_SESSION['csrf_token']) ||
    empty($_POST['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        (string) $_POST['csrf_token']
    )
) {
    http_response_code(403);
    exit('Formulário inválido. Atualize a página e tente novamente.');
}

require_once '/var/www/stockweb/config/database.php';

// Recebe os dados do formulário
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

// Valida nome e preço
$precoValido = preg_match(
    '/^\d{1,8}(\.\d{1,2})?$/',
    $preco
);

if (
    $nome === '' ||
    mb_strlen($nome) > 150 ||
    !$precoValido ||
    $quantidade === false ||
    $quantidade === null ||
    $quantidade < 0 ||
    $estoqueMinimo === false ||
    $estoqueMinimo === null ||
    $estoqueMinimo < 0 ||
    $categoriaId === false ||
    $categoriaId === null ||
    $categoriaId <= 0 ||
    $fornecedorId === false ||
    $fornecedorId === null ||
    $fornecedorId <= 0
) {
    http_response_code(422);
    exit('Dados inválidos. Volte ao formulário e confira os campos.');
}

try {
    // Verifica se a categoria existe
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM categorias WHERE id = ?'
    );
    $stmt->execute([$categoriaId]);

    if (!$stmt->fetchColumn()) {
        http_response_code(422);
        exit('Categoria inválida.');
    }

    // Verifica se o fornecedor existe
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM fornecedores WHERE id = ?'
    );
    $stmt->execute([$fornecedorId]);

    if (!$stmt->fetchColumn()) {
        http_response_code(422);
        exit('Fornecedor inválido.');
    }

    // Salva o produto usando parâmetros seguros
    $stmt = $pdo->prepare("
        INSERT INTO produtos (
            nome,
            descricao,
            preco,
            quantidade,
            estoque_minimo,
            categoria_id,
            fornecedor_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $nome,
        $descricao !== '' ? $descricao : null,
        $preco,
        $quantidade,
        $estoqueMinimo,
        $categoriaId,
        $fornecedorId
    ]);

    // Redireciona após cadastrar
    header('Location: produtos.php?sucesso=1');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    exit('Não foi possível cadastrar o produto. Tente novamente.');
}

