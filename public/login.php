<?php
session_start();

require_once '/var/www/stockweb/includes/layout.php';

$erroLogin = $_SESSION['erro_login'] ?? null;
unset($_SESSION['erro_login']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php stockwebHead('Login'); ?>
<body>
    <main class="sw-login">
        <div class="sw-login-brand-panel">
            <div class="sw-brand"><?php stockwebMarca(); ?></div>
            <div class="sw-login-message">
                <p class="sw-login-tagline">Controle seu estoque <span>em um só lugar.</span></p>
                <p class="sw-login-description">Acompanhe produtos, vendas e movimentações com o StockWeb.</p>
            </div>
            <div class="sw-login-art" aria-hidden="true">
                <div class="sw-login-cube sw-login-cube-back"><span></span><span></span><span></span></div>
                <div class="sw-login-cube sw-login-cube-front"><span></span><span></span><span></span></div>
                <div class="sw-login-cube sw-login-cube-side"><span></span><span></span><span></span></div>
            </div>
        </div>
        <div class="sw-login-form-panel">
            <div class="sw-login-form">
                <div class="sw-brand sw-login-mobile-brand"><?php stockwebMarca(); ?></div>
                <hgroup>
                    <h1>Entrar no StockWeb</h1>
                    <p>Use seu e-mail e senha para acessar o sistema.</p>
                </hgroup>

                <?php if (is_string($erroLogin) && $erroLogin !== ''): ?>
                    <?php stockwebAviso('danger', $erroLogin); ?>
                <?php endif; ?>

                <form action="autenticar.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail</label>
                        <div class="sw-login-field">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input class="form-control" type="email" id="email" name="email" placeholder="Digite seu e-mail"
                                   autocomplete="username" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="senha">Senha</label>
                        <div class="sw-login-field">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input class="form-control" type="password" id="senha" name="senha" placeholder="Digite sua senha"
                                   autocomplete="current-password" required>
                            <button class="sw-icon-button sw-password-toggle" type="button" data-password-toggle
                                    aria-controls="senha" aria-label="Mostrar senha" aria-pressed="false" title="Mostrar senha" hidden>
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Entrar</button>
                </form>
            </div>
        </div>
    </main>
    <script src="assets/js/interface.js" defer></script>
</body>
</html>
