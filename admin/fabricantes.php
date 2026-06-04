<?php
/**
 * admin/fabricantes.php  (PRIVADO - requiere login)
 * ---------------------------------------------------------------
 * Listado de fabricantes con opciones para crear, editar y eliminar.
 * ---------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/auth.php';
requiereLogin();

$pdo = require __DIR__ . '/../config/database.php';

$mensaje = '';

// ---------- Eliminar un fabricante (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id = (int) ($_POST['id'] ?? 0);

    // Gracias a "ON DELETE SET NULL" en la clave foránea, los productos
    // de este fabricante NO se borran: simplemente se quedan sin fabricante.
    $stmt = $pdo->prepare('DELETE FROM fabricante WHERE id = :id');
    $stmt->execute([':id' => $id]);

    $mensaje = 'Fabricante eliminado correctamente.';
}

// ---------- Obtener todos los fabricantes (con nº de productos) ----------
$fabricantes = $pdo->query(
    "SELECT f.*, COUNT(p.id) AS num_productos
     FROM fabricante f
     LEFT JOIN producto p ON p.id_fabricante = f.id
     GROUP BY f.id
     ORDER BY f.nombre"
)->fetchAll();

$titulo = 'Gestionar fabricantes';
$base   = '../';
require __DIR__ . '/../includes/header.php';
?>

<div class="admin-cabecera">
    <h1>Gestionar fabricantes</h1>
    <a class="btn-nuevo" href="fabricante_form.php">+ Nuevo fabricante</a>
</div>

<?php if ($mensaje !== ''): ?>
    <p class="alerta-ok"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<table class="tabla">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Nº de productos</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($fabricantes as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['nombre']) ?></td>
                <td><?= (int) $f['num_productos'] ?></td>
                <td class="acciones">
                    <a class="btn-editar" href="fabricante_form.php?id=<?= (int) $f['id'] ?>">Editar</a>
                    <form method="post" action="fabricantes.php" class="form-inline"
                          onsubmit="return confirm('¿Eliminar el fabricante «<?= htmlspecialchars(addslashes($f['nombre'])) ?>»?');">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($fabricantes)): ?>
            <tr><td colspan="3">No hay fabricantes todavía.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
