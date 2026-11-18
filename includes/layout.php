<?php
require_once __DIR__ . '/sidebar.php';

function stockwebLayoutEstilos() {
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <?php
}

function stockwebLayoutAbrir($moduloAtivo) {
    ?>
    <header class="stockweb-mobile-header d-lg-none">
        <a class="stockweb-brand" href="dashboard.php">
            <span class="stockweb-brand-icon"><i class="bi bi-boxes" aria-hidden="true"></i></span>
            StockWeb
        </a>
        <button type="button" class="stockweb-menu-button" data-bs-toggle="offcanvas"
                data-bs-target="#stockweb-sidebar" aria-controls="stockweb-sidebar" aria-label="Abrir menu">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
    </header>
    <?php
    stockwebSidebar($moduloAtivo);
    ?>
    <div class="stockweb-content">
    <?php
}

function stockwebLayoutFechar() {
    ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php
}
