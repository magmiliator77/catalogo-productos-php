<?php
/**
 * admin/productos.php  (PRIVADO - requiere login)
 * ---------------------------------------------------------------
 * Listado de productos para administración, con enlaces para
 * crear, editar y eliminar. La eliminación se hace por POST.
 * ---------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/auth.php';
requiereLogin();   // si no hay sesión, redirige al login

$pdo = require __DIR__ . '/../config/database.php';

$mensaje = '';

// ---------- Eliminar un producto (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id = (int) ($_POST['id'] ?? 0);

    // Antes de borrar el registro, recuperamos el nombre de la imagen
    // para borrar también el fichero del disco.
    $stmt = $pdo->prepare('SELECT imagen FROM producto WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $imagen = $stmt->fetchColumn();

    $stmt = $pdo->prepare('DELETE FROM producto WHERE id = :id');
    $stmt->execute([':id' => $id]);

    // Borramos el fichero de imagen asociado, si existía.
    if ($imagen && file_exists(__DIR__ . '/../uploads/' . $imagen)) {
        unlink(__DIR__ . '/../uploads/' . $imagen);
    }

    $mensaje = 'Producto eliminado correctamente.';
}

// ---------- Obtener todos los productos ----------
$productos = $pdo->query(
    "SELECT p.*, f.nombre AS fabricante
     FROM producto p
     LEFT JOIN fabricante f ON p.id_fabricante = f.id
     ORDER BY p.id DESC"
)->fetchAll();

$titulo = 'Gestionar productos';
$base   = '../';
require __DIR__ . '/../includes/header.php';
?>

<div class="admin-cabecera">
    <h1>Gestionar productos</h1>
    <a class="btn-nuevo" href="producto_form.php">+ Nuevo producto</a>
</div>

<?php if ($mensaje !== ''): ?>
    <p class="alerta-ok"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<table class="tabla">
    <thead>
        <tr>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Fabricante</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $p): ?>
            <tr>
                <td>
                    <?php if (!empty($p['imagen'])): ?>
                        <img class="mini" src="../uploads/<?= htmlspecialchars($p['imagen']) ?>" alt="">
                    <?php else: ?>
                        <span class="sin-imagen mini">—</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= number_format($p['precio'], 2, ',', '.') ?> &euro;</td>
                <td><?= htmlspecialchars($p['fabricante'] ?? 'Sin fabricante') ?></td>
                <td class="acciones">
                    <a class="btn-editar" href="producto_form.php?id=<?= (int) $p['id'] ?>">Editar</a>
                    <!-- Eliminar mediante POST + confirmación -->
                    <form method="post" action="productos.php" class="form-inline"
                          onsubmit="return confirm('¿Eliminar el producto «<?= htmlspecialchars(addslashes($p['nombre'])) ?>»?');">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($productos)): ?>
            <tr><td colspan="5">No hay productos todavía.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
