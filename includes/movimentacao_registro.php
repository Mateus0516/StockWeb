<?php

const ESTOQUE_MAXIMO = 2_147_483_647;

// O chamador mantém a transação que alterou o estoque.
function movimentacaoRegistrar(PDO $pdo, int $produtoId, int $usuarioId, string $tipo, int $quantidade, ?string $observacao): void {
    $stmt = $pdo->prepare('
        INSERT INTO movimentacoes (produto_id, usuario_id, tipo, quantidade, observacao)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$produtoId, $usuarioId, $tipo, $quantidade, $observacao]);
}

// Aplica uma movimentação manual dentro da transação do chamador.
function movimentacaoAplicar(PDO $pdo, int $produtoId, int $usuarioId, string $tipo, int $quantidade, ?string $observacao): void {
    $stmt = $pdo->prepare('SELECT quantidade FROM produtos WHERE id = ? AND ativo = 1 FOR UPDATE');
    $stmt->execute([$produtoId]);
    $estoque = $stmt->fetchColumn();
    if ($estoque === false) {
        throw new DomainException('Produto não encontrado ou inativo.');
    }

    $saldo = $tipo === 'ENTRADA' ? (int) $estoque + $quantidade : (int) $estoque - $quantidade;
    if ($saldo < 0) {
        throw new DomainException('Estoque insuficiente. Saldo disponível: ' . $estoque . '.');
    }

    if ($saldo > ESTOQUE_MAXIMO) {
        throw new DomainException('A entrada excede o limite de ' . number_format(ESTOQUE_MAXIMO, 0, ',', '.') . ' unidades em estoque.');
    }

    $stmt = $pdo->prepare('UPDATE produtos SET quantidade = ? WHERE id = ?');
    $stmt->execute([$saldo, $produtoId]);
    movimentacaoRegistrar($pdo, $produtoId, $usuarioId, $tipo, $quantidade, $observacao);
}
