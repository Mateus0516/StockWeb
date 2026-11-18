<?php

function vendaRegistrar(PDO $pdo, int $clienteId, int $usuarioId, array $quantidades): int {
    // Trava cada produto em ordem de ID para serializar vendas concorrentes.
    ksort($quantidades, SORT_NUMERIC);
    $consulta = $pdo->prepare('SELECT nome, preco, quantidade, ativo FROM produtos WHERE id = ? FOR UPDATE');
    $itens = [];
    $total = 0;

    foreach ($quantidades as $produtoId => $quantidade) {
        $consulta->execute([$produtoId]);
        $produto = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$produto || (int) $produto['ativo'] !== 1) {
            throw new DomainException('Um dos produtos selecionados está indisponível. Revise os itens.');
        }

        if ($quantidade > (int) $produto['quantidade']) {
            throw new DomainException('Estoque insuficiente para ' . $produto['nome'] . '.');
        }

        $preco = vendaCentavos($produto['preco']);
        if ($preco > 0 && $quantidade > intdiv(VENDA_MAX_CENTAVOS - $total, $preco)) {
            throw new DomainException('O total ultrapassa o limite de R$ 99.999.999,99 por venda.');
        }

        $total += $preco * $quantidade;
        $itens[$produtoId] = [$quantidade, $produto['preco']];
    }

    $venda = $pdo->prepare("
        INSERT INTO vendas (cliente_id, usuario_id, valor_total, status)
        VALUES (?, ?, ?, 'CONCLUIDA')
    ");
    $venda->execute([$clienteId, $usuarioId, vendaDecimal($total)]);
    $vendaId = (int) $pdo->lastInsertId();

    $inserir = $pdo->prepare('
        INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario)
        VALUES (?, ?, ?, ?)
    ');
    $baixar = $pdo->prepare('UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?');

    foreach ($itens as $produtoId => [$quantidade, $preco]) {
        $inserir->execute([$vendaId, $produtoId, $quantidade, $preco]);
        $baixar->execute([$quantidade, $produtoId]);
    }

    return $vendaId;
}
