<?php
/**
 * index.php  (RAÍZ - acceso público)
 * ---------------------------------------------------------------
 * Página principal del catálogo. Cualquier visitante puede:
 *   - Ver la lista de productos con nombre, precio, descripción e imagen.
 *   - Buscar por nombre o descripción.
 *   - Ordenar por precio (ascendente / descendente).
 *   - Navegar entre páginas (paginación).
 * ---------------------------------------------------------------
 */

$pdo  = require __DIR__ . '/config/database.php';

// ---------- 1) Recoger parámetros de la URL (GET) ----------

// Texto de búsqueda. trim() quita espacios sobrantes.
$busqueda = trim($_GET['q'] ?? '');

// Orden por precio. Solo admitimos dos valores: lo demás se ignora (lista blanca).
$orden    = ($_GET['orden'] ?? '') === 'desc' ? 'DESC' : 'ASC';

// Página actual. (int) la convierte en número; mínimo 1.
$pagina   = max(1, (int) ($_GET['pagina'] ?? 1));

// Productos por página y desplazamiento (OFFSET) para la consulta.
$porPagina = 4;
$offset    = ($pagina - 1) * $porPagina;

// ---------- 2) Construir la condición de búsqueda ----------

// Si hay texto de búsqueda, filtramos por nombre O descripción con LIKE.
// Usamos marcadores (:busq1 y :busq2) para evitar inyección SQL.
// Importante: con consultas preparadas reales no se puede repetir el mismo
// marcador, por eso usamos dos distintos con el mismo valor.
$where  = '';
$params = [];
if ($busqueda !== '') {
    $where = 'WHERE p.nombre LIKE :busq1 OR p.descripcion LIKE :busq2';
    $params[':busq1'] = '%' . $busqueda . '%';
    $params[':busq2'] = '%' . $busqueda . '%';
}

// ---------- 3) Contar el total de resultados (para la paginación) ----------

$sqlTotal = "SELECT COUNT(*) FROM producto p $where";
$stmt = $pdo->prepare($sqlTotal);
$stmt->execute($params);
$totalProductos = (int) $stmt->fetchColumn();
$totalPaginas   = (int) ceil($totalProductos / $porPagina);

// ---------- 4) Obtener los productos de la página actual ----------

// Hacemos JOIN con fabricante para mostrar su nombre.
$sql = "SELECT p.*, f.nombre AS fabricante
        FROM producto p
        LEFT JOIN fabricante f ON p.id_fabricante = f.id
        $where
        ORDER BY p.precio $orden
        LIMIT :limite OFFSET :offset";

$stmt = $pdo->prepare($sql);
// Re-ligamos los parámetros de búsqueda (si los hay).
foreach ($params as $clave => $valor) {
    $stmt->bindValue($clave, $valor);
}
// LIMIT y OFFSET deben ligarse como ENTEROS.
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$productos = $stmt->fetchAll();

// ---------- 5) Pintar la página ----------
$titulo = 'Catálogo de productos';
$base   = '';
require __DIR__ . '/includes/header.php';
?>

<h1>Catálogo de productos</h1>

<!-- Formulario de búsqueda y ordenación (método GET para que quede en la URL) -->
<form class="filtros" method="get" action="index.php">
    <input type="text" name="q" placeholder="Buscar por nombre o descripción..."
           value="<?= htmlspecialchars($busqueda) ?>">

    <select name="orden">
        <option value="asc"  <?= $orden === 'ASC'  ? 'selected' : '' ?>>Precio: menor a mayor</option>
        <option value="desc" <?= $orden === 'DESC' ? 'selected' : '' ?>>Precio: mayor a menor</option>
    </select>

    <button type="submit">Filtrar</button>
    <a class="btn-reset" href="index.php">Limpiar</a>
</form>

<p class="resultado-info">
    <?= $totalProductos ?> producto(s) encontrado(s).
</p>

<?php if (empty($productos)): ?>
    <p class="vacio">No se han encontrado productos.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($productos as $producto): ?>
            <article class="card">
                <a href="detalle.php?id=<?= (int) $producto['id'] ?>">
                    <?php if (!empty($producto['imagen'])): ?>
                        <img src="uploads/<?= htmlspecialchars($producto['imagen']) ?>"
                             alt="<?= htmlspecialchars($producto['nombre']) ?>">
                    <?php else: ?>
                        <div class="sin-imagen">Sin imagen</div>
                    <?php endif; ?>
                </a>

                <div class="card-body">
                    <h3><a href="detalle.php?id=<?= (int) $producto['id'] ?>"><?= htmlspecialchars($producto['nombre']) ?></a></h3>
                    <p class="precio"><?= number_format($producto['precio'], 2, ',', '.') ?> &euro;</p>
                    <?php
                        // Recortamos la descripción para la vista previa.
                        // Usamos mb_substr (multibyte, respeta acentos) si está disponible.
                        $resumen = $producto['descripcion'] ?? '';
                        $resumen = function_exists('mb_substr')
                            ? mb_substr($resumen, 0, 80)
                            : substr($resumen, 0, 80);
                    ?>
                    <p class="desc"><?= htmlspecialchars($resumen) ?>&hellip;</p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
        <nav class="paginacion">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <?php
                    // Mantenemos la búsqueda y el orden al cambiar de página.
                    $query = http_build_query([
                        'q'      => $busqueda,
                        'orden'  => strtolower($orden),
                        'pagina' => $i,
                    ]);
                ?>
                <a class="<?= $i === $pagina ? 'activa' : '' ?>" href="index.php?<?= $query ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
