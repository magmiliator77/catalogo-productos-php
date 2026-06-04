<?php
/**
 * includes/auth.php
 * ---------------------------------------------------------------
 * Funciones de ayuda para la autenticación basada en SESIONES.
 *
 * La sesión se arranca una sola vez aquí. Cualquier página que
 * incluya este fichero ya tendrá la sesión disponible.
 * ---------------------------------------------------------------
 */

// Arranca la sesión solo si no estaba ya iniciada.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ¿Hay un usuario con la sesión iniciada?
 * Comprobamos si existe la variable de sesión 'usuario_id'.
 */
function estaAutenticado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Protege una página privada.
 * Si el usuario NO está autenticado, lo redirige al login y corta la ejecución.
 *
 * Se llama al principio de todas las páginas de /admin.
 */
function requiereLogin(): void
{
    if (!estaAutenticado()) {
        // Calculamos la ruta al login según la profundidad de la carpeta.
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Devuelve el nombre del usuario logueado (para mostrarlo en la cabecera).
 */
function nombreUsuario(): string
{
    return $_SESSION['usuario_nombre'] ?? '';
}
