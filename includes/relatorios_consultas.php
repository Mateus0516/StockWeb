<?php

const RELATORIO_POR_PAGINA = 25;
const RELATORIO_LIMITE_IMPRESSAO = 500;

function relatorioTitulos(): array {
    return [
        'resumo' => 'Resumo geral',
        'estoque' => 'Produtos e estoque',
        'vendas' => 'Vendas',
        'movimentacoes' => 'Movimentações registradas',
    ];
}

function relatorioCampos(string $tipo): array {
    $periodo = ['inicio' => ['Data inicial', null], 'fim' => ['Data final', null]];
    $produto = ['produto_id' => ['Produto', 'produtos']];

    return match ($tipo) {
        'estoque' => [
            'categoria_id' => ['Categoria', 'categorias'],
            'fornecedor_id' => ['Fornecedor', 'fornecedores'],
            'ativo' => ['Status do produto', ['1' => 'Ativo', '0' => 'Inativo']],
            'saldo' => ['Situação do estoque', [
                'zerado' => 'Estoque zerado', 'abaixo' => 'Abaixo do mínimo',
                'igual' => 'Igual ao mínimo', 'acima' => 'Acima do mínimo',
            ]],
        ],
        'vendas' => $periodo + [
            'cliente_id' => ['Cliente', 'clientes'],
        ] + $produto + [
            'status' => ['Status da venda', [
                'CONCLUIDA' => 'Concluída', 'PENDENTE' => 'Pendente', 'CANCELADA' => 'Cancelada',
            ]],
        ],
        'movimentacoes' => $periodo + $produto + [
            'movimento' => ['Tipo', ['ENTRADA' => 'Entrada', 'SAIDA' => 'Saída']],
            'usuario_id' => ['Responsável', 'usuarios'],
        ],
        'resumo' => $periodo,
    };
}

function relatorioInteiro($valor): int|false {
    return filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 2_147_483_647],
    ]);
}

function relatorioDataValida(string $valor): bool {
    return preg_match('/\A([1-9][0-9]{3})-([0-9]{2})-([0-9]{2})\z/', $valor, $partes) === 1 &&
        checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1]);
}

function relatorioFiltros(array $entrada, array $campos): array {
    $filtros = [];
    foreach ($campos as $nome => [$rotulo, $opcoes]) {
        $valor = $entrada[$nome] ?? '';
        if (!is_string($valor)) {
            throw new InvalidArgumentException('Filtro inválido: ' . $rotulo . '.');
        }

        if ($valor === '') {
            continue;
        }

        $valido = match (true) {
            $opcoes === null => relatorioDataValida($valor),
            is_array($opcoes) => array_key_exists($valor, $opcoes),
            default => relatorioInteiro($valor) !== false,
        };
        if (!$valido) {
            throw new InvalidArgumentException('Filtro inválido: ' . $rotulo . '.');
        }

        $filtros[$nome] = $opcoes !== null && is_string($opcoes) ? relatorioInteiro($valor) : $valor;
    }

    if (isset($filtros['inicio'], $filtros['fim']) && $filtros['inicio'] > $filtros['fim']) {
        throw new InvalidArgumentException('A data inicial deve ser anterior ou igual à final.');
    }

    return $filtros;
}

function relatorioConsultar(PDO $pdo, string $sql, array $parametros = []): PDOStatement {
    $stmt = $pdo->prepare($sql);
    foreach (array_values($parametros) as $indice => $valor) {
        $stmt->bindValue($indice + 1, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt;
}

function relatorioOpcoes(PDO $pdo, array $campos): array {
    $opcoes = [];
    foreach ($campos as $nome => $campo) {
        $origem = $campo[1] ?? null;
        $opcoes[$nome] = is_string($origem)
            ? relatorioConsultar($pdo, 'SELECT id, nome FROM ' . $origem . ' ORDER BY nome, id')->fetchAll(PDO::FETCH_KEY_PAIR)
            : $origem;
    }

    return $opcoes;
}

function relatorioCondicoes(array $filtros, array $colunas, ?string $colunaData = null): array {
    $condicoes = [];
    $parametros = [];
    foreach ($colunas as $campo => $coluna) {
        if (isset($filtros[$campo])) {
            $condicoes[] = $coluna . ' = ?';
            $parametros[] = $filtros[$campo];
        }
    }

    if ($colunaData !== null) {
        if (isset($filtros['inicio'])) {
            $condicoes[] = $colunaData . ' >= ?';
            $parametros[] = $filtros['inicio'] . ' 00:00:00';
        }
        if (isset($filtros['fim'])) {
            $condicoes[] = $colunaData . ' <= ?';
            $parametros[] = $filtros['fim'] . ' 23:59:59';
        }
    }

    return [$condicoes, $parametros];
}

function relatorioWhere(array $condicoes): string {
    return $condicoes ? ' WHERE ' . implode(' AND ', $condicoes) : '';
}

function relatorioEstoque(PDO $pdo, array $filtros): array {
    [$condicoes, $parametros] = relatorioCondicoes($filtros, [
        'categoria_id' => 'p.categoria_id', 'fornecedor_id' => 'p.fornecedor_id', 'ativo' => 'p.ativo',
    ]);
    if (isset($filtros['saldo'])) {
        $condicoes[] = match ($filtros['saldo']) {
            'zerado' => 'p.quantidade = 0',
            'abaixo' => 'p.quantidade < p.estoque_minimo',
            'igual' => 'p.quantidade = p.estoque_minimo',
            'acima' => 'p.quantidade > p.estoque_minimo',
        };
    }

    $where = relatorioWhere($condicoes);
    $totais = relatorioConsultar($pdo, '
        SELECT COUNT(*) AS registros, COALESCE(SUM(p.quantidade), 0) AS unidades,
               COUNT(CASE WHEN p.ativo = 1 AND p.quantidade <= p.estoque_minimo THEN 1 END) AS estoque_baixo
        FROM produtos p' . $where, $parametros)->fetch();

    return [
        $totais,
        "SELECT p.nome AS produto, c.nome AS categoria, f.nome AS fornecedor,
                p.quantidade, p.estoque_minimo, p.ativo,
                CASE WHEN p.quantidade = 0 THEN 'Zerado'
                     WHEN p.quantidade < p.estoque_minimo THEN 'Abaixo do mínimo'
                     WHEN p.quantidade = p.estoque_minimo THEN 'No mínimo'
                     ELSE 'Acima do mínimo' END AS situacao
         FROM produtos p
         INNER JOIN categorias c ON c.id = p.categoria_id
         INNER JOIN fornecedores f ON f.id = p.fornecedor_id $where
         ORDER BY p.nome, p.id",
        $parametros,
    ];
}

function relatorioVendas(PDO $pdo, array $filtros): array {
    [$condicoes, $parametros] = relatorioCondicoes($filtros, [
        'cliente_id' => 'v.cliente_id', 'status' => 'v.status',
    ], 'v.data_venda');
    $valor = 'v.valor_total';
    $unidades = '(SELECT COALESCE(SUM(i.quantidade), 0) FROM itens_venda i WHERE i.venda_id = v.id)';
    $join = '';

    if (isset($filtros['produto_id'])) {
        $join = '
            INNER JOIN (
                SELECT venda_id, SUM(quantidade) AS unidades, SUM(subtotal) AS valor
                FROM itens_venda WHERE produto_id = ? GROUP BY venda_id
            ) itens ON itens.venda_id = v.id';
        array_unshift($parametros, $filtros['produto_id']);
        $valor = 'itens.valor';
        $unidades = 'itens.unidades';
    }

    $where = relatorioWhere($condicoes);
    $totais = relatorioConsultar($pdo, "
        SELECT COUNT(*) AS registros,
               COUNT(CASE WHEN v.status = 'CONCLUIDA' THEN 1 END) AS concluidas,
               COALESCE(SUM(CASE WHEN v.status = 'CONCLUIDA' THEN $unidades ELSE 0 END), 0) AS unidades,
               COALESCE(SUM(CASE WHEN v.status = 'CONCLUIDA' THEN $valor ELSE 0 END), 0) AS faturamento,
               COALESCE(ROUND(AVG(CASE WHEN v.status = 'CONCLUIDA' THEN $valor END), 2), 0) AS ticket
        FROM vendas v $join $where", $parametros)->fetch();

    return [
        $totais,
        "SELECT v.id, v.data_venda, c.nome AS cliente, v.status, $unidades AS unidades, $valor AS valor
         FROM vendas v INNER JOIN clientes c ON c.id = v.cliente_id $join $where
         ORDER BY v.data_venda DESC, v.id DESC",
        $parametros,
    ];
}

function relatorioMovimentacoes(PDO $pdo, array $filtros): array {
    [$condicoes, $parametros] = relatorioCondicoes($filtros, [
        'produto_id' => 'm.produto_id', 'movimento' => 'm.tipo', 'usuario_id' => 'm.usuario_id',
    ], 'm.data_movimentacao');
    $where = relatorioWhere($condicoes);
    $totais = relatorioConsultar($pdo, "
        SELECT COUNT(*) AS registros,
               COALESCE(SUM(CASE WHEN m.tipo = 'ENTRADA' THEN m.quantidade ELSE 0 END), 0) AS entradas,
               COALESCE(SUM(CASE WHEN m.tipo = 'SAIDA' THEN m.quantidade ELSE 0 END), 0) AS saidas
        FROM movimentacoes m $where", $parametros)->fetch();

    return [
        $totais,
        "SELECT m.data_movimentacao, p.nome AS produto, m.tipo, m.quantidade,
                u.nome AS usuario, m.observacao
         FROM movimentacoes m
         INNER JOIN produtos p ON p.id = m.produto_id
         INNER JOIN usuarios u ON u.id = m.usuario_id $where
         ORDER BY m.data_movimentacao DESC, m.id DESC",
        $parametros,
    ];
}

function relatorioResumo(PDO $pdo, array $filtros): array {
    $atuais = relatorioConsultar($pdo, '
        SELECT COUNT(*) AS ativos, COUNT(CASE WHEN quantidade <= estoque_minimo THEN 1 END) AS estoque_baixo,
               (SELECT COUNT(*) FROM clientes) AS clientes
        FROM produtos WHERE ativo = 1')->fetch();
    [$vendas] = relatorioVendas($pdo, $filtros + ['status' => 'CONCLUIDA']);
    [$movimentos] = relatorioMovimentacoes($pdo, $filtros);
    unset($vendas['registros'], $movimentos['registros']);

    return $atuais + $vendas + $movimentos;
}

function relatorioCarregar(PDO $pdo, string $tipo, array $filtros, int $pagina, bool $impressao = false): array {
    $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
    $pdo->beginTransaction();
    try {
        $opcoes = relatorioOpcoes($pdo, relatorioCampos($tipo));
        $linhas = [];
        $paginas = 1;
        $excedeu = false;
        if ($tipo === 'resumo') {
            $totais = relatorioResumo($pdo, $filtros);
        } else {
            [$totais, $sql, $parametros] = match ($tipo) {
                'estoque' => relatorioEstoque($pdo, $filtros),
                'vendas' => relatorioVendas($pdo, $filtros),
                'movimentacoes' => relatorioMovimentacoes($pdo, $filtros),
            };

            $paginas = max(1, (int) ceil($totais['registros'] / RELATORIO_POR_PAGINA));
            $pagina = min($pagina, $paginas);
            $excedeu = $impressao && $totais['registros'] > RELATORIO_LIMITE_IMPRESSAO;
            if (!$excedeu && $totais['registros'] > 0) {
                $limite = $impressao ? RELATORIO_LIMITE_IMPRESSAO : RELATORIO_POR_PAGINA;
                $inicio = $impressao ? 0 : ($pagina - 1) * $limite;
                $linhas = relatorioConsultar($pdo, $sql . ' LIMIT ? OFFSET ?', [...$parametros, $limite, $inicio])->fetchAll();
            }
        }

        $pdo->commit();
        return compact('opcoes', 'totais', 'linhas', 'pagina', 'paginas', 'excedeu');
    } catch (Throwable $erro) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $erro;
    }
}
