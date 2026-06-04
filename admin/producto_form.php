<?php
/**
 * admin/producto_form.php  (PRIVADO - requiere login)
 * ---------------------------------------------------------------
 * Formulario único que sirve para CREAR y EDITAR productos.
 *   - producto_form.php          -> formulario vacío (crear).
 *   - producto_form.php?id=5     -> formulario relleno (editar).
 *
 * Gestiona también la SUBIDA DE IMÁGENES al directorio /uploads.
 * ---------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/auth.php';
requiereLogin();

$pdo = require __DIR__ . '/../config/database.php';

// Carpeta donde se guardan las imágenes y extensiones permitidas.
$dirUploads     = __DIR__ . '/../uploads/';
$extPermitidas  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$tamMaximo      = 2 * 1024 * 1024; // 2 MB

// ---------- Cargar fabricantes para el desplegable <select> ----------
$fabricantes = $pdo->query('SELECT id, nombre FROM fabricante ORDER BY nombre')->fetchAll();

// ---------- ¿Estamos editando? ----------
$id        = (int) ($_GET['id'] ?? 0);
$esEdicion = $id > 0;
$errores   = [];

// Valores por defecto del formulario.
$producto = [
    'nombre'        => '',
    'descripcion'   => '',
    'precio'        => '',
    'imagen'        => '',
    'id_fabricante' => '',
];

// Si editamos, cargamos los datos actuales del producto.
if ($esEdicion && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM producto WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $encontrado = $stmt->fetch();

    if (!$encontrado) {
        // No existe: volvemos al listado.
        header('Location: productos.php');
        exit;
    }
    $producto = $encontrado;
}

// ---------- Procesar el envío del formulario (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Recoger y limpiar los datos del formulario.
    $producto['nombre']        = trim($_POST['nombre'] ?? '');
    $producto['descripcion']   = trim($_POST['descripcion'] ?? '');
    $producto['precio']        = trim($_POST['precio'] ?? '');
    $producto['id_fabricante'] = $_POST['id_fabricante'] ?? '';

    // 2) Validar los datos.
    if ($producto['nombre'] === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if (!is_numeric($producto['precio']) || $producto['precio'] < 0) {
        $errores[] = 'El precio debe ser un número mayor o igual que 0.';
    }

    // 3) Gestionar la imagen subida (si la hay).
    // En edición recuperamos de la BD la imagen ACTUAL. Así, si el usuario no
    // sube una nueva, la conservamos (antes se borraba al editar otros campos).
    $imagenActual = '';
    if ($esEdicion) {
        $stmt = $pdo->prepare('SELECT imagen FROM producto WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $imagenActual = (string) $stmt->fetchColumn();
        $producto['imagen'] = $imagenActual; // para volver a mostrarla si hay error
    }
    $nombreImagen = $imagenActual;  // por defecto, la que ya tenía
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

        $tmp       = $_FILES['imagen']['tmp_name'];
        $nombreOrg = $_FILES['imagen']['name'];
        $tam       = $_FILES['imagen']['size'];
        $ext       = strtolower(pathinfo($nombreOrg, PATHINFO_EXTENSION));

        if (!in_array($ext, $extPermitidas, true)) {
            $errores[] = 'Formato de imagen no permitido (usa JPG, PNG, GIF o WEBP).';
        } elseif ($tam > $tamMaximo) {
            $errores[] = 'La imagen no puede superar los 2 MB.';
        } else {
            // Generamos un nombre único para evitar sobrescribir otras imágenes.
            $nuevoNombre = uniqid('prod_', true) . '.' . $ext;

            if (move_uploaded_file($tmp, $dirUploads . $nuevoNombre)) {
                // Si editábamos y había una imagen anterior, la borramos.
                if ($esEdicion && !empty($producto['imagen'])
                    && file_exists($dirUploads . $producto['imagen'])) {
                    unlink($dirUploads . $producto['imagen']);
                }
                $nombreImagen = $nuevoNombre;
            } else {
                $errores[] = 'No se pudo guardar la imagen en el servidor.';
            }
        }
    }

    // 4) Si no hay errores, guardamos en la base de datos.
    if (empty($errores)) {
        // El fabricante puede quedar vacío (NULL).
        $idFab = $producto['id_fabricante'] !== '' ? (int) $producto['id_fabricante'] : null;

        if ($esEdicion) {
            // UPDATE
            $sql = 'UPDATE producto
                    SET nombre = :nombre, descripcion = :descripcion, precio = :precio,
                        imagen = :imagen, id_fabricante = :id_fabricante
                    WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre'        => $producto['nombre'],
                ':descripcion'   => $producto['descripcion'],
                ':precio'        => $producto['precio'],
                ':imagen'        => $nombreImagen,
                ':id_fabricante' => $idFab,
                ':id'            => $id,
            ]);
        } else {
            // INSERT
            $sql = 'INSERT INTO producto (nombre, descripcion, precio, imagen, id_fabricante)
                    VALUES (:nombre, :descripcion, :precio, :imagen, :id_fabricante)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre'        => $producto['nombre'],
                ':descripcion'   => $producto['descripcion'],
                ':precio'        => $producto['precio'],
                ':imagen'        => $nombreImagen,
                ':id_fabricante' => $idFab,
            ]);
        }

        // Redirigimos al listado tras guardar (patrón Post/Redirect/Get).
        header('Location: productos.php');
        exit;
    }
}

$titulo = $esEdicion ? 'Editar producto' : 'Nuevo producto';
$base   = '../';
require __DIR__ . '/../includes/header.php';
?>

<h1><?= $esEdicion ? 'Editar producto' : 'Nuevo producto' ?></h1>

<?php if (!empty($errores)): ?>
    <div class="alerta-error">
        <ul>
            <?php foreach ($errores as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- enctype="multipart/form-data" es OBLIGATORIO para poder subir ficheros -->
<form method="post" enctype="multipart/form-data" class="form">

    <label>
        Nombre *
        <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
    </label>

    <label>
        Descripción
        <textarea name="descripcion" rows="4"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
    </label>

    <label>
        Precio (&euro;) *
        <input type="number" name="precio" step="0.01" min="0"
               value="<?= htmlspecialchars($producto['precio']) ?>" required>
    </label>

    <label>
        Fabricante
        <!-- Desplegable poblado desde la base de datos -->
        <select name="id_fabricante">
            <option value="">— Sin fabricante —</option>
            <?php foreach ($fabricantes as $f): ?>
                <option value="<?= (int) $f['id'] ?>"
                    <?= (string) $producto['id_fabricante'] === (string) $f['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        Imagen
        <input type="file" name="imagen" accept="image/*">
    </label>

    <?php if (!empty($producto['imagen'])): ?>
        <div class="imagen-actual">
            <p>Imagen actual:</p>
            <img class="mini" src="../uploads/<?= htmlspecialchars($producto['imagen']) ?>" alt="">
        </div>
    <?php endif; ?>

    <div class="form-botones">
        <button type="submit"><?= $esEdicion ? 'Guardar cambios' : 'Crear producto' ?></button>
        <a class="btn-reset" href="productos.php">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
