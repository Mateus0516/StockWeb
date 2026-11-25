<?php
require_once __DIR__ . '/sidebar.php';

function stockwebEscapar($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function stockwebHead(string $titulo, bool $impressao = false): void {
    ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title><?= stockwebEscapar($titulo) ?> | StockWeb</title>
    <link rel="icon" href="assets/stockweb.svg" type="image/svg+xml">
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/stockweb.css" rel="stylesheet">
    <?php if ($impressao): ?>
        <link href="assets/css/relatorios-impressao.css" rel="stylesheet">
    <?php endif; ?>
</head>
    <?php
}

function stockwebLayoutAbrir(string $modulo, string $titulo): void {
    [$rotulo, , $url] = stockwebModulos()[$modulo];
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php stockwebHead($titulo); ?>
<body class="sw-app">
    <a class="sw-skip-link" href="#conteudo">Ir para o conteúdo</a>
    <?php stockwebSidebar($modulo); ?>
    <div class="sw-content">
        <header class="sw-topbar">
            <button type="button" class="sw-icon-button d-lg-none" data-bs-toggle="offcanvas"
                    data-bs-target="#stockweb-sidebar" aria-controls="stockweb-sidebar" aria-label="Abrir menu">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <button type="button" class="sw-icon-button sw-collapse-button d-none d-lg-inline-flex"
                    data-sidebar-toggle aria-controls="stockweb-sidebar" aria-expanded="true"
                    aria-label="Recolher menu" title="Recolher menu" hidden>
                <i class="bi bi-layout-sidebar" aria-hidden="true"></i>
            </button>
            <span class="sw-topbar-divider" aria-hidden="true"></span>
            <nav class="sw-breadcrumb" aria-label="Localização">
                <?php if ($modulo === 'dashboard'): ?>
                    <span class="sw-crumb-root">Visão geral</span>
                <?php else: ?>
                    <a class="sw-crumb-root" href="dashboard.php">Visão geral</a>
                <?php endif; ?>
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                <?php if ($rotulo === $titulo): ?>
                    <span aria-current="page"><?= stockwebEscapar($rotulo) ?></span>
                <?php else: ?>
                    <a href="<?= stockwebEscapar($url) ?>"><?= stockwebEscapar($rotulo) ?></a>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    <span aria-current="page"><?= stockwebEscapar($titulo) ?></span>
                <?php endif; ?>
            </nav>
            <a class="sw-mobile-mark d-lg-none" href="dashboard.php" aria-label="StockWeb · Dashboard">
                <span class="sw-brand-icon"><i class="bi bi-box-seam-fill" aria-hidden="true"></i></span>
            </a>
        </header>
        <main class="sw-main" id="conteudo">
    <?php
}

function stockwebLayoutFechar(): void {
    ?>
        </main>
    </div>
    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script src="assets/js/interface.js" defer></script>
</body>
</html>
    <?php
}

function stockwebTitulo(string $titulo, string $descricao = ''): void {
    ?>
    <hgroup>
        <h1><?= stockwebEscapar($titulo) ?></h1>
        <?php if ($descricao !== ''): ?>
            <p><?= stockwebEscapar($descricao) ?></p>
        <?php endif; ?>
    </hgroup>
    <?php
}

function stockwebAviso(string $tipo, string $mensagem, bool $dispensavel = false): void {
    $icone = match ($tipo) {
        'danger' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        'success' => 'check-circle',
        default => 'info-circle',
    };
    ?>
    <div class="alert alert-<?= stockwebEscapar($tipo) ?><?= $dispensavel ? ' alert-dismissible fade show' : '' ?> sw-alert" role="alert">
        <i class="bi bi-<?= $icone ?>" aria-hidden="true"></i>
        <span><?= stockwebEscapar($mensagem) ?></span>
        <?php if ($dispensavel): ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar mensagem"></button>
        <?php endif; ?>
    </div>
    <?php
}

function stockwebEstadoVazio(string $icone, string $mensagem, string $descricao = ''): void {
    ?>
    <div class="sw-empty">
        <span class="sw-icon-tile sw-tone-neutral"><i class="bi bi-<?= stockwebEscapar($icone) ?>" aria-hidden="true"></i></span>
        <p><?= stockwebEscapar($mensagem) ?></p>
        <?php if ($descricao !== ''): ?>
            <small><?= stockwebEscapar($descricao) ?></small>
        <?php endif; ?>
    </div>
    <?php
}

function stockwebStatus(string $rotulo, string $tom): void {
    ?>
    <span class="sw-status sw-tone-<?= stockwebEscapar($tom) ?>"><?= stockwebEscapar($rotulo) ?></span>
    <?php
}

function stockwebStatusVenda(string $status): void {
    [$rotulo, $tom] = match ($status) {
        'CONCLUIDA' => ['Concluída', 'success'],
        'PENDENTE' => ['Pendente', 'warning'],
        'CANCELADA' => ['Cancelada', 'danger'],
        default => [$status, 'neutral'],
    };
    stockwebStatus($rotulo, $tom);
}

function stockwebTabelaCabecalho(string $titulo, int $total): void {
    ?>
    <div class="sw-panel-header sw-table-header">
        <h2><?= stockwebEscapar($titulo) ?></h2>
        <span class="sw-count"><?= number_format($total, 0, ',', '.') ?> <?= $total === 1 ? 'registro' : 'registros' ?></span>
    </div>
    <?php
}

function stockwebConfirmacaoExclusao(): void {
    ?>
    <dialog class="sw-dialog" id="sw-confirmacao" aria-labelledby="sw-confirmacao-titulo" aria-describedby="sw-confirmacao-mensagem">
        <form method="dialog">
            <span class="sw-icon-tile sw-tone-danger"><i class="bi bi-trash3" aria-hidden="true"></i></span>
            <h2 id="sw-confirmacao-titulo">Confirmar exclusão</h2>
            <p id="sw-confirmacao-mensagem"></p>
            <div class="sw-dialog-actions">
                <button class="btn btn-outline-secondary" value="cancelar" autofocus>Cancelar</button>
                <button class="btn btn-danger" value="excluir">Excluir</button>
            </div>
        </form>
    </dialog>
    <?php
}

function stockwebErroProduto(string $mensagem): never {
    stockwebLayoutAbrir('produtos', 'Erro no produto');
    ?>
    <header class="sw-page-header">
        <?php stockwebTitulo('Não foi possível concluir a operação'); ?>
    </header>
    <?php stockwebAviso('danger', $mensagem); ?>
    <a href="produtos.php" class="btn btn-outline-secondary">Voltar aos produtos</a>
    <?php
    stockwebLayoutFechar();
    exit;
}
