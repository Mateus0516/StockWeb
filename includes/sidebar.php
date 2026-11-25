<?php

function stockwebModulos(): array {
    return [
        'dashboard' => ['Dashboard', 'grid-1x2', 'dashboard.php'],
        'produtos' => ['Produtos', 'box-seam', 'produtos.php'],
        'categorias' => ['Categorias', 'tags', 'categorias.php'],
        'fornecedores' => ['Fornecedores', 'truck', 'fornecedores.php'],
        'clientes' => ['Clientes', 'people', 'clientes.php'],
        'vendas' => ['Vendas', 'cart3', 'vendas.php'],
        'movimentacoes' => ['Movimentações', 'arrow-left-right', 'movimentacoes.php'],
        'relatorios' => ['Relatórios', 'bar-chart-line', 'relatorios.php'],
    ];
}

function stockwebMarca(): void {
    ?>
    <span class="sw-brand-icon"><i class="bi bi-box-seam-fill" aria-hidden="true"></i></span>
    <span class="sw-brand-name">StockWeb</span>
    <?php
}

function stockwebSidebar(string $moduloAtivo): void {
    $modulos = stockwebModulos();
    $grupos = [
        'Visão geral' => ['dashboard'],
        'Cadastros' => ['produtos', 'categorias', 'fornecedores', 'clientes'],
        'Operações' => ['vendas', 'movimentacoes'],
        'Análises' => ['relatorios'],
    ];
    $nome = (string) ($_SESSION['usuario_nome'] ?? 'Usuário');
    $perfil = match ($_SESSION['usuario_perfil'] ?? '') {
        'ADMIN' => 'Administrador',
        'VENDEDOR' => 'Vendedor',
        default => (string) ($_SESSION['usuario_perfil'] ?? 'Usuário'),
    };
    ?>
    <aside class="offcanvas-lg offcanvas-start sw-sidebar" tabindex="-1" id="stockweb-sidebar"
           aria-labelledby="stockweb-sidebar-title">
        <div class="sw-sidebar-inner">
            <div class="sw-sidebar-header">
                <a class="sw-brand" id="stockweb-sidebar-title" href="dashboard.php" aria-label="StockWeb">
                    <?php stockwebMarca(); ?>
                </a>
                <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas"
                        data-bs-target="#stockweb-sidebar" aria-label="Fechar menu"></button>
            </div>
            <nav class="sw-sidebar-nav" aria-label="Navegação principal">
                <?php foreach ($grupos as $grupo => $itens): ?>
                    <div class="sw-nav-group">
                        <span class="sw-nav-heading"><?= $grupo ?></span>
                        <?php foreach ($itens as $modulo): ?>
                            <?php [$rotulo, $icone, $url] = $modulos[$modulo]; ?>
                            <a class="sw-nav-item<?= $modulo === $moduloAtivo ? ' active' : '' ?>"
                               href="<?= $url ?>" aria-label="<?= $rotulo ?>" title="<?= $rotulo ?>"
                               <?= $modulo === $moduloAtivo ? 'aria-current="page"' : '' ?>>
                                <i class="bi bi-<?= $icone ?>" aria-hidden="true"></i>
                                <span class="sw-nav-label"><?= $rotulo ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
            <div class="sw-sidebar-footer">
                <div class="sw-sidebar-user" title="<?= stockwebEscapar($nome . ' · ' . $perfil) ?>">
                    <span class="sw-sidebar-avatar"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
                    <span class="sw-user-label"><strong><?= stockwebEscapar($nome) ?></strong><small><?= stockwebEscapar($perfil) ?></small></span>
                </div>
                <a class="sw-nav-item sw-logout" href="logout.php" aria-label="Sair" title="Sair">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i><span class="sw-nav-label">Sair</span>
                </a>
            </div>
        </div>
    </aside>
    <?php
}
