<?php
require_once __DIR__ . '/venda_funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vendas.php');
    exit;
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || !hash_equals(vendaToken(), $token)) {
    vendaRedirecionar('venda_nova.php', 'danger', 'Formulário inválido. Atualize a página e tente novamente.');
}

$clienteId = vendaInteiro($_POST['cliente_id'] ?? null);
$itens = $_POST['itens'] ?? [];
if (!is_array($itens) || count($itens) > VENDA_MAX_ITENS) {
    vendaRedirecionar('venda_nova.php', 'danger', 'Informe até ' . VENDA_MAX_ITENS . ' itens por venda.');
}

$dados = [
    'cliente_id' => $clienteId,
    'itens' => array_map(static function ($item) {
        return [
            'produto_id' => vendaInteiro($item['produto_id'] ?? null),
            'quantidade' => vendaInteiro($item['quantidade'] ?? null),
        ];
    }, $itens),
];

if ($clienteId === false || !$itens) {
    vendaRedirecionar('venda_nova.php', 'danger', 'Selecione um cliente e adicione pelo menos um produto.', $dados);
}

$quantidades = [];
foreach ($dados['itens'] as ['produto_id' => $produtoId, 'quantidade' => $quantidade]) {
    if ($produtoId === false || $quantidade === false) {
        vendaRedirecionar('venda_nova.php', 'danger', 'Selecione um produto e informe uma quantidade inteira positiva em cada item.', $dados);
    }

    $quantidades[$produtoId] = ($quantidades[$produtoId] ?? 0) + $quantidade;
}

require_once '/var/www/stockweb/config/database.php';
require_once '/var/www/stockweb/includes/venda_registro.php';

try {
    $pdo->beginTransaction();
    $cliente = $pdo->prepare('SELECT id FROM clientes WHERE id = ? FOR SHARE');
    $cliente->execute([$clienteId]);
    if ($cliente->fetchColumn() === false) {
        throw new DomainException('Cliente não encontrado. Selecione outro cliente.');
    }

    $vendaId = vendaRegistrar($pdo, $clienteId, (int) $_SESSION['usuario_id'], $quantidades);
    $pdo->commit();
} catch (DomainException | PDOException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $mensagem = 'Não foi possível concluir a venda. Revise os dados e tente novamente.';
    if ($erro instanceof DomainException) {
        $mensagem = $erro->getMessage();
    } else {
        error_log($erro->getMessage());
    }

    vendaRedirecionar('venda_nova.php', 'danger', $mensagem, $dados);
}

vendaRedirecionar('venda_detalhes.php?id=' . $vendaId, 'success', 'Venda concluída e estoque atualizado.');
