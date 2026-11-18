<?php

function stockwebSidebar($moduloAtivo) {
    $itens = [
        ['dashboard', 'Dashboard', 'speedometer2', 'dashboard.php'],
        ['produtos', 'Produtos', 'box-seam', 'produtos.php'],
        ['categorias', 'Categorias', 'tags', 'categorias.php'],
        ['fornecedores', 'Fornecedores', 'truck', 'fornecedores.php'],
        ['clientes', 'Clientes', 'people', 'clientes.php'],
        ['vendas', 'Vendas', 'cart3', 'vendas.php'],
        ['movimentacoes', 'Movimentações', 'arrow-left-right', 'movimentacoes.php'],
        ['relatorios', 'Relatórios', 'bar-chart-line', 'relatorios.php'],
    ];

    $nomeUsuario = htmlspecialchars(
        (string) ($_SESSION['usuario_nome'] ?? 'Usuário'),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
    ?>
    <aside class="offcanvas-lg offcanvas-start stockweb-sidebar" tabindex="-1"
           id="stockweb-sidebar" aria-labelledby="stockweb-sidebar-title">
        <div class="stockweb-sidebar-inner">
            <div class="stockweb-sidebar-header">
                <a class="stockweb-brand" id="stockweb-sidebar-title" href="dashboard.php">
                    <span class="stockweb-brand-icon"><i class="bi bi-boxes" aria-hidden="true"></i></span>
                    StockWeb
                </a>
                <button type="button" class="btn-close btn-close-white d-lg-none"
                        data-bs-dismiss="offcanvas" data-bs-target="#stockweb-sidebar"
                        aria-label="Fechar menu"></button>
            </div>

            <nav class="stockweb-sidebar-nav" aria-label="Navegação principal">
                <?php foreach ($itens as [$modulo, $rotulo, $icone, $pagina]): ?>
                    <?php if (is_file(dirname(__DIR__) . '/public/' . $pagina)): ?>
                        <a class="stockweb-nav-item<?= $modulo === $moduloAtivo ? ' active' : '' ?>"
                           href="<?= $pagina ?>"<?= $modulo === $moduloAtivo ? ' aria-current="page"' : '' ?>>
                            <i class="bi bi-<?= $icone ?>" aria-hidden="true"></i>
                            <?= $rotulo ?>
                        </a>
                    <?php else: ?>
                        <span class="stockweb-nav-item stockweb-nav-disabled" aria-disabled="true">
                            <i class="bi bi-<?= $icone ?>" aria-hidden="true"></i>
                            <?= $rotulo ?>
                            <i class="bi bi-lock stockweb-nav-lock ms-auto" aria-hidden="true"></i>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <div class="stockweb-sidebar-footer">
                <div class="stockweb-sidebar-user">
                    <i class="bi bi-person-circle" aria-hidden="true"></i>
                    <span><?= $nomeUsuario ?></span>
                </div>
                <a class="stockweb-nav-item stockweb-logout" href="logout.php">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    Sair
                </a>
            </div>
        </div>
    </aside>
    <?php
}
