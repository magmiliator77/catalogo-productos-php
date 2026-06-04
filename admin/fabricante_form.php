<?php
/**
 * admin/fabricante_form.php  (PRIVADO - requiere login)
 * ---------------------------------------------------------------
 * Formulario para CREAR y EDITAR fabricantes.
 *   - fabricante_form.php        -> crear.
 *   - fabricante_form.php?id=3   -> editar.
 * ---------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/auth.php';
requiereLogin();

$pdo = require __DIR__ . '/../config/database.php';

$id        = (int) ($_GET['id'] ?? 0);
$esEdicion = $id > 0;
$errores   = [];
$fabricante = ['nombre' => ''];

// Cargar datos si estamos editando.
if ($esEdicion && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM fabricante WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $encontrado = $stmt->fetch();

    if (!$encontrado) {
        header('Location: fabricantes.php');
        exit;
    }
    $fabricante = $encontrado;
}

// Procesar el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fabricante['nombre'] = trim($_POST['nombre'] ?? '');

    if ($fabricante['nombre'] === '') {
        $errores[] = 'El nombre del fabricante es obligatorio.';
    }

    if (empty($errores)) {
        if ($esEdicion) {
            $stmt = $pdo->prepare('UPDATE fabricante SET nombre = :nombre WHERE id = :id');
            $stmt->execute([':nombre' => $fabricante['nombre'], ':id' => $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO fabricante (nombre) VALUES (:nombre)');
            $stmt->execute([':nombre' => $fabricante['nombre']]);
        }
        header('Location: fabricantes.php');
        exit;
    }
}

$titulo = $esEdicion ? 'Editar fabricante' : 'Nuevo fabricante';
$base   = '../';
require __DIR__ . '/../includes/header.php';
?>

<h1><?= $esEdicion ? 'Editar fabricante' : 'Nuevo fabricante' ?></h1>

<?php if (!empty($errores)): ?>
    <div class="alerta-error">
        <ul>
            <?php foreach ($errores as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="form">
    <label>
        Nombre *
        <input type="text" name="nombre" value="<?= htmlspecialchars($fabricante['nombre']) ?>" required autofocus>
    </label>

    <div class="form-botones">
        <button type="submit"><?= $esEdicion ? 'Guardar cambios' : 'Crear fabricante' ?></button>
        <a class="btn-reset" href="fabricantes.php">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
