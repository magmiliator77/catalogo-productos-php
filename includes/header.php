<?php
/**
 * includes/header.php
 * ---------------------------------------------------------------
 * Cabecera HTML común a todas las páginas (apertura de <html>,
 * <head> y barra de navegación).
 *
 * Antes de incluir este fichero, cada página define dos variables:
 *   $titulo : texto que aparece en la pestaña del navegador.
 *   $base   : prefijo de ruta hacia la raíz del proyecto.
 *             '' si la página está en la raíz, '../' si está en /admin.
 * ---------------------------------------------------------------
 */
require_once __DIR__ . '/auth.php';   // garantiza que la sesión está iniciada

$titulo = $titulo ?? 'Catálogo de productos';
$base   = $base   ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>
<body>
<header class="topbar">
    <a class="logo" href="<?= $base ?>index.php">🛍️ Catálogo</a>

    <nav class="nav">
        <a href="<?= $base ?>index.php">Inicio</a>

        <?php if (estaAutenticado()): ?>
            <!-- Enlaces visibles solo para usuarios autenticados -->
            <a href="<?= $base ?>admin/productos.php">Gestionar productos</a>
            <a href="<?= $base ?>admin/fabricantes.php">Gestionar fabricantes</a>
            <span class="user">👤 <?= htmlspecialchars(nombreUsuario()) ?></span>
            <a class="btn-logout" href="<?= $base ?>logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a class="btn-login" href="<?= $base ?>login.php">Iniciar sesión</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
