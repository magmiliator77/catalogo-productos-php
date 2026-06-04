<?php
/**
 * login.php  (RAÍZ)
 * ---------------------------------------------------------------
 * Formulario de inicio de sesión.
 *   - GET  : muestra el formulario.
 *   - POST : valida las credenciales contra la tabla 'usuario'.
 *
 * La contraseña NO se guarda en claro en la BD: guardamos su hash
 * y lo comparamos con password_verify().
 * ---------------------------------------------------------------
 */

require_once __DIR__ . '/includes/auth.php';   // arranca la sesión
$pdo = require __DIR__ . '/config/database.php';

// Si ya estaba autenticado, no tiene sentido ver el login.
if (estaAutenticado()) {
    header('Location: index.php');
    exit;
}

$error = '';

// ---------- Procesar el envío del formulario ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Debes rellenar todos los campos.';
    } else {
        // Buscamos al usuario por su login (consulta preparada).
        $stmt = $pdo->prepare('SELECT * FROM usuario WHERE login = :login');
        $stmt->execute([':login' => $login]);
        $usuario = $stmt->fetch();

        // password_verify compara la contraseña escrita con el hash guardado.
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Credenciales correctas: regeneramos el id de sesión (buena práctica)
            // y guardamos los datos necesarios.
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}

$titulo = 'Iniciar sesión';
$base   = '';
require __DIR__ . '/includes/header.php';
?>

<div class="form-centrado">
    <h1>Iniciar sesión</h1>

    <?php if ($error !== ''): ?>
        <p class="alerta-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php" class="form">
        <label>
            Usuario
            <input type="text" name="login" required autofocus>
        </label>
        <label>
            Contraseña
            <input type="password" name="password" required>
        </label>
        <button type="submit">Entrar</button>
    </form>

    <p class="ayuda-login">Usuario de prueba: <code>admin</code> / <code>admin123</code></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
