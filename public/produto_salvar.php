<?php
session_start();

require_once '/var/www/stockweb/includes/layout.php';

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
$tokenSessao = $_SESSION['csrf_token'] ?? null;
$tokenFormulario = $_POST['csrf_token'] ?? null;
if (!is_string($tokenSessao) || $tokenSessao === '' || !is_string($tokenFormulario) ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    http_response_code(403);
    stockwebErroProduto('Formulário inválido. Atualize a página e tente novamente.');
}

require_once '/var/www/stockweb/config/database.php';
require_once '/var/www/stockweb/includes/movimentacao_registro.php';

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
    $quantidade > ESTOQUE_MAXIMO ||
    $estoqueMinimo === false ||
    $estoqueMinimo === null ||
    $estoqueMinimo < 0 ||
    $estoqueMinimo > ESTOQUE_MAXIMO ||
    $categoriaId === false ||
    $categoriaId === null ||
    $categoriaId <= 0 ||
    $fornecedorId === false ||
    $fornecedorId === null ||
    $fornecedorId <= 0
) {
    http_response_code(422);
    stockwebErroProduto('Dados inválidos. Volte ao formulário e confira os campos.');
}

try {
    // Verifica se a categoria existe
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM categorias WHERE id = ?'
    );
    $stmt->execute([$categoriaId]);

    if (!$stmt->fetchColumn()) {
        http_response_code(422);
        stockwebErroProduto('Categoria inválida.');
    }

    // Verifica se o fornecedor existe
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM fornecedores WHERE id = ?'
    );
    $stmt->execute([$fornecedorId]);

    if (!$stmt->fetchColumn()) {
        http_response_code(422);
        stockwebErroProduto('Fornecedor inválido.');
    }

    $pdo->beginTransaction();

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

    if ($quantidade > 0) {
        movimentacaoRegistrar($pdo, (int) $pdo->lastInsertId(), (int) $_SESSION['usuario_id'], 'ENTRADA', $quantidade, 'Estoque inicial no cadastro do produto');
    }
    $pdo->commit();

    // Redireciona após cadastrar
    header('Location: produtos.php?sucesso=1');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);
    stockwebErroProduto('Não foi possível cadastrar o produto. Tente novamente.');
}

