<?php
/**
 * config/database.php
 * ---------------------------------------------------------------
 * Conexión a la base de datos mediante PDO.
 *
 * Usamos PDO (PHP Data Objects) porque permite:
 *   - Consultas preparadas -> evita la inyección SQL.
 *   - Cambiar de motor de BD con poco esfuerzo.
 *
 * Este fichero devuelve (return) un objeto $pdo ya conectado.
 * En cualquier página haremos:  $pdo = require 'config/database.php';
 * ---------------------------------------------------------------
 */

// --- Parámetros de conexión (ajústalos a tu entorno) ---
$host    = 'localhost';
$db      = 'catalogo';
$user    = 'root';
$pass    = '';          // pon aquí tu contraseña de MySQL si tienes una
$charset = 'utf8mb4';

// Cadena DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opciones de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // lanza excepciones ante errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // resultados como array asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // usa consultas preparadas reales
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // En producción NO se muestra el mensaje real; aquí ayuda a depurar.
    die('Error de conexión con la base de datos: ' . $e->getMessage());
}

return $pdo;
