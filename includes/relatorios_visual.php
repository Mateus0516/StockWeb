<?php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
    header('Allow: GET, HEAD');
    http_response_code(405);
    exit;
}

require_once __DIR__ . '/relatorios_consultas.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../config/database.php';

function relatorioEscapar($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function relatorioUrl(string $arquivo, string $tipo, array $filtros = [], int $pagina = 1): string {
    $parametros = ['relatorio' => $tipo] + $filtros;
    if ($pagina !== 1) {
        $parametros['pagina'] = $pagina;
    }

    return $arquivo . '?' . http_build_query($parametros, '', '&', PHP_QUERY_RFC3986);
}

function relatorioPreparar(PDO $pdo, array $entrada, bool $impressao = false): array {
    $titulos = relatorioTitulos();
    $tipo = $entrada['relatorio'] ?? 'resumo';
    $erro = null;
    if (!is_string($tipo) || !isset($titulos[$tipo])) {
        $tipo = 'resumo';
        $erro = 'Selecione um relatório válido.';
    }

    $campos = relatorioCampos($tipo);
    $filtros = [];
    $resultado = null;
    if ($erro === null) {
        try {
            $filtros = relatorioFiltros($entrada, $campos);
            $pagina = $impressao ? 1 : relatorioInteiro($entrada['pagina'] ?? '1');
            if ($pagina === false) {
                throw new InvalidArgumentException('Página inválida.');
            }

            $resultado = relatorioCarregar($pdo, $tipo, $filtros, $pagina, $impressao);
        } catch (InvalidArgumentException $excecao) {
            http_response_code(422);
            $erro = $excecao->getMessage();
        } catch (PDOException $excecao) {
            error_log($excecao->getMessage());
            http_response_code(500);
            $erro = 'Não foi possível carregar o relatório. Tente novamente.';
        }
    } else {
        http_response_code(422);
    }

    return compact('tipo', 'titulos', 'campos', 'filtros', 'resultado', 'erro');
}

function relatorioNumero($valor): string {
    return number_format((int) $valor, 0, ',', '.');
}

function relatorioMoeda($valor): string {
    // Formata o DECIMAL como texto para preservar os centavos.
    [$inteiro, $decimal] = array_pad(explode('.', (string) $valor, 2), 2, '00');
    $inteiro = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $inteiro);
    return 'R$ ' . $inteiro . ',' . str_pad($decimal, 2, '0');
}

function relatorioData(string $valor, string $formato = 'd/m/Y'): string {
    return (new DateTimeImmutable($valor))->format($formato);
}

function relatorioRotuloStatus(string $status): string {
    return match ($status) {
        'CONCLUIDA' => 'Concluída',
        'PENDENTE' => 'Pendente',
        'CANCELADA' => 'Cancelada',
        'ENTRADA' => 'Entrada',
        'SAIDA' => 'Saída',
        default => $status,
    };
}

function relatorioPeriodo(array $filtros): string {
    if (isset($filtros['inicio'], $filtros['fim'])) {
        return relatorioData($filtros['inicio']) . ' a ' . relatorioData($filtros['fim']);
    }

    if (isset($filtros['inicio'])) {
        return 'A partir de ' . relatorioData($filtros['inicio']);
    }

    return isset($filtros['fim']) ? 'Até ' . relatorioData($filtros['fim']) : 'Todo o período';
}

function relatorioDescricaoFiltros(string $tipo, array $campos, array $filtros, array $opcoes): array {
    $descricoes = ['Período' => $tipo === 'estoque' ? 'Posição atual do estoque' : relatorioPeriodo($filtros)];
    foreach ($campos as $nome => [$rotulo, $origem]) {
        if ($origem !== null && isset($filtros[$nome])) {
            $valor = $filtros[$nome];
            $descricoes[$rotulo] = $opcoes[$nome][$valor] ?? '#' . $valor;
        }
    }

    if (count($descricoes) === 1) {
        $descricoes['Outros filtros'] = 'Nenhum';
    }

    return $descricoes;
}

function relatorioCards(array $indicadores): void {
    ?>
    <dl class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 g-3 mb-4">
        <?php foreach ($indicadores as $rotulo => $valor): ?>
            <div class="col">
                <div class="sw-panel sw-report-metric report-card">
                    <dt><?= relatorioEscapar($rotulo) ?></dt>
                    <dd><?= relatorioEscapar($valor) ?></dd>
                </div>
            </div>
        <?php endforeach; ?>
    </dl>
    <?php
}

function relatorioIndicadores(string $tipo, array $totais): void {
    $vendas = static fn (array $dados) => [
        'Unidades vendidas' => relatorioNumero($dados['unidades']),
        'Faturamento' => relatorioMoeda($dados['faturamento']),
        'Ticket médio' => relatorioMoeda($dados['ticket']),
    ];
    $movimentos = static fn (array $dados) => [
        'Unidades que entraram' => relatorioNumero($dados['entradas']),
        'Unidades que saíram' => relatorioNumero($dados['saidas']),
    ];

    if ($tipo === 'resumo') {
        ?>
        <h2 class="sw-section-title mb-3">Situação atual</h2>
        <?php relatorioCards([
            'Produtos ativos' => relatorioNumero($totais['ativos']),
            'Produtos ativos no mínimo ou abaixo' => relatorioNumero($totais['estoque_baixo']),
            'Clientes cadastrados' => relatorioNumero($totais['clientes']),
        ]); ?>
        <h2 class="sw-section-title mb-3">Vendas concluídas no período</h2>
        <?php relatorioCards(['Vendas concluídas' => relatorioNumero($totais['concluidas'])] + $vendas($totais)); ?>
        <h2 class="sw-section-title mb-3">Movimentações registradas no período</h2>
        <?php relatorioCards($movimentos($totais));
        return;
    }

    $indicadores = match ($tipo) {
        'estoque' => [
            'Produtos no resultado' => relatorioNumero($totais['registros']),
            'Unidades em estoque' => relatorioNumero($totais['unidades']),
            'Produtos ativos no mínimo ou abaixo' => relatorioNumero($totais['estoque_baixo']),
        ],
        'vendas' => ['Total de vendas no resultado' => relatorioNumero($totais['registros'])] + $vendas($totais),
        'movimentacoes' => ['Movimentações registradas' => relatorioNumero($totais['registros'])] + $movimentos($totais),
    };
    relatorioCards($indicadores);
}

function relatorioTabela(string $tipo, array $linhas, bool $produtoFiltrado, bool $impressao = false): void {
    $colunas = match ($tipo) {
        'estoque' => ['Produto', 'Categoria', 'Fornecedor', 'Saldo', 'Mínimo', 'Situação do estoque', 'Status'],
        'vendas' => ['Venda', 'Data e horário', 'Cliente', 'Status', 'Unidades', $produtoFiltrado ? 'Valor do produto' : 'Valor total'],
        'movimentacoes' => ['Data e horário', 'Produto', 'Tipo', 'Quantidade', 'Responsável', 'Observação'],
    };
    ?>
    <div class="table-responsive sw-table-scroll report-table"
        <?= $impressao ? '' : 'tabindex="0" role="region" aria-label="Tabela de resultados do relatório"' ?>>
        <table class="table table-hover align-middle sw-table">
            <thead>
                <tr>
                    <?php foreach ($colunas as $coluna): ?>
                        <th scope="col"><?= relatorioEscapar($coluna) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$linhas): ?>
                    <tr><td colspan="<?= count($colunas) ?>"><?php stockwebEstadoVazio('file-earmark-bar-graph', 'Nenhum resultado para os filtros aplicados.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($linhas as $linha): ?>
                    <?php
                    $baixo = $tipo === 'estoque' && $linha['quantidade'] <= $linha['estoque_minimo'];
                    $valores = match ($tipo) {
                        'estoque' => [$linha['produto'], $linha['categoria'], $linha['fornecedor'],
                            relatorioNumero($linha['quantidade']), relatorioNumero($linha['estoque_minimo']),
                            $linha['situacao'], (int) $linha['ativo'] === 1 ? 'Ativo' : 'Inativo'],
                        'vendas' => ['#' . $linha['id'], relatorioData($linha['data_venda'], 'd/m/Y H:i'), $linha['cliente'],
                            relatorioRotuloStatus($linha['status']), relatorioNumero($linha['unidades']), relatorioMoeda($linha['valor'])],
                        'movimentacoes' => [relatorioData($linha['data_movimentacao'], 'd/m/Y H:i:s'), $linha['produto'],
                            relatorioRotuloStatus($linha['tipo']), relatorioNumero($linha['quantidade']),
                            $linha['usuario'], $linha['observacao'] ?? '—'],
                    };
                    ?>
                    <tr<?= $baixo ? ' class="table-warning"' : '' ?>>
                        <?php foreach ($valores as $indice => $valor): ?>
                            <td>
                                <?php if ($impressao): ?>
                                    <?= relatorioEscapar($valor) ?>
                                <?php elseif ($tipo === 'estoque' && $indice === 5): ?>
                                    <?php stockwebStatus($valor, $baixo ? 'warning' : 'success'); ?>
                                <?php elseif ($tipo === 'estoque' && $indice === 6): ?>
                                    <?php stockwebStatus($valor, (int) $linha['ativo'] === 1 ? 'success' : 'neutral'); ?>
                                <?php elseif ($tipo === 'vendas' && $indice === 3): ?>
                                    <?php stockwebStatusVenda($linha['status']); ?>
                                <?php elseif ($tipo === 'movimentacoes' && $indice === 2): ?>
                                    <?php stockwebStatus($valor, $linha['tipo'] === 'ENTRADA' ? 'success' : 'warning'); ?>
                                <?php else: ?>
                                    <?= relatorioEscapar($valor) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
