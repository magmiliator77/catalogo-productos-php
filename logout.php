<?php
/**
 * logout.php  (RAÍZ)
 * ---------------------------------------------------------------
 * Cierra la sesión de forma segura y vuelve a la página principal.
 * ---------------------------------------------------------------
 */

require_once __DIR__ . '/includes/auth.php';   // arranca la sesión

// 1) Vaciamos todas las variables de sesión.
$_SESSION = [];

// 2) Borramos la cookie de sesión del navegador (si existe).
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// 3) Destruimos la sesión en el servidor.
session_destroy();

// 4) Volvemos al inicio.
header('Location: index.php');
exit;
