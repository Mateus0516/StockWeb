<?php
require_once __DIR__ . '/movimentacao_funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: movimentacoes.php');
    exit;
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || !hash_equals(movimentacaoToken(), $token)) {
    http_response_code(403);
    require_once '/var/www/stockweb/includes/layout.php';
    stockwebLayoutAbrir('movimentacoes', 'Nova movimentação');
    ?>
    <header class="sw-page-header"><?php stockwebTitulo('Nova movimentação'); ?></header>
    <?php stockwebAviso('danger', 'Formulário inválido. Abra um novo formulário e tente novamente.'); ?>
    <a href="movimentacao_nova.php" class="btn btn-primary">Voltar ao formulário</a>
    <?php
    stockwebLayoutFechar();
    exit;
}

$produtoId = movimentacaoInteiro($_POST['produto_id'] ?? null);
$quantidade = movimentacaoInteiro($_POST['quantidade'] ?? null);
$tipo = $_POST['tipo'] ?? '';
$observacao = $_POST['observacao'] ?? '';
$dados = [
    'produto_id' => $produtoId,
    'tipo' => is_string($tipo) ? $tipo : '',
    'quantidade' => is_string($_POST['quantidade'] ?? null) ? $_POST['quantidade'] : '',
    'observacao' => is_string($observacao) && strlen($observacao) <= 65535 ? $observacao : '',
];

if ($produtoId === false || !in_array($tipo, ['ENTRADA', 'SAIDA'], true) || $quantidade === false) {
    movimentacaoRedirecionar('movimentacao_nova.php', 'danger', 'Selecione um produto e um tipo e informe uma quantidade inteira entre 1 e 2.147.483.647.', $dados);
}

if (!is_string($observacao) || preg_match('/\A.{0,1000}\z/us', $observacao) !== 1) {
    movimentacaoRedirecionar('movimentacao_nova.php', 'danger', 'Informe uma observação de até 1.000 caracteres.', $dados);
}

require_once '/var/www/stockweb/config/database.php';

try {
    $pdo->beginTransaction();
    $observacao = trim($observacao);
    movimentacaoAplicar($pdo, $produtoId, (int) $_SESSION['usuario_id'], $tipo, $quantidade, $observacao !== '' ? $observacao : null);
    $pdo->commit();
} catch (DomainException | PDOException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $mensagem = 'Não foi possível registrar a movimentação. Tente novamente.';
    if ($erro instanceof DomainException) {
        $mensagem = $erro->getMessage();
    } else {
        error_log($erro->getMessage());
    }

    movimentacaoRedirecionar('movimentacao_nova.php', 'danger', $mensagem, $dados);
}

movimentacaoRedirecionar('movimentacoes.php', 'success', 'Movimentação registrada e estoque atualizado.');
