<?php
session_start();

// Remove os dados da sessão
$_SESSION = [];

// Exclui o cookie da sessão, quando utilizado
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Encerra a sessão
session_destroy();

// Retorna para o login
header('Location: login.php');
exit;
