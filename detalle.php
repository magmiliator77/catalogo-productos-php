<?php
/**
 * detalle.php  (RAÍZ - acceso público)
 * ---------------------------------------------------------------
 * Ficha ampliada de un producto. Recibe el id por la URL:
 *   detalle.php?id=5
 * ---------------------------------------------------------------
 */

$pdo = require __DIR__ . '/config/database.php';

// Validamos el id recibido.
$id = (int) ($_GET['id'] ?? 0);

// Consulta preparada con JOIN para traer también el nombre del fabricante.
$stmt = $pdo->prepare(
    "SELECT p.*, f.nombre AS fabricante
     FROM producto p
     LEFT JOIN fabricante f ON p.id_fabricante = f.id
     WHERE p.id = :id"
);
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch();

$titulo = $producto ? $producto['nombre'] : 'Producto no encontrado';
$base   = '';
require __DIR__ . '/includes/header.php';
?>

<?php if (!$producto): ?>
    <p class="vacio">El producto solicitado no existe.</p>
    <p><a class="btn" href="index.php">&larr; Volver al catálogo</a></p>
<?php else: ?>
    <p><a class="volver" href="index.php">&larr; Volver al catálogo</a></p>

    <article class="detalle">
        <div class="detalle-imagen">
            <?php if (!empty($producto['imagen'])): ?>
                <img src="uploads/<?= htmlspecialchars($producto['imagen']) ?>"
                     alt="<?= htmlspecialchars($producto['nombre']) ?>">
            <?php else: ?>
                <div class="sin-imagen grande">Sin imagen</div>
            <?php endif; ?>
        </div>

        <div class="detalle-info">
            <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
            <p class="precio grande"><?= number_format($producto['precio'], 2, ',', '.') ?> &euro;</p>
            <p class="fabricante">
                Fabricante: <strong><?= htmlspecialchars($producto['fabricante'] ?? 'Sin fabricante') ?></strong>
            </p>
            <h2>Descripción</h2>
            <p><?= nl2br(htmlspecialchars($producto['descripcion'] ?? 'Sin descripción.')) ?></p>
        </div>
    </article>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
