<?php
session_start();

header(isset($_SESSION['usuario_id']) ? 'Location: /dashboard.php' : 'Location: /login.php');
exit;
