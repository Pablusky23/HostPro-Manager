<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$error = '';
$exito = '';

// Si el formulario se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        // Comprobar si el email ya existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Este email ya está registrado.';
        } else {
            // Cifrar la contraseña (NUNCA guardes contraseñas en texto plano)
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Insertar en la base de datos
            $stmt2 = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'cliente')");
            $stmt2->bind_param("sss", $nombre, $email, $hash);

            if ($stmt2->execute()) {
                $exito = 'Registro exitoso. <a href="/login.php">Inicia sesión aquí</a>';
            } else {
                $error = 'Error al registrar. Inténtalo de nuevo.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <h1>Crear cuenta</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="alert alert-success"><?= $exito ?></div>
    <?php endif; ?>

    <form method="POST" action="/register.php">
        <div class="form-group">
            <label>Nombre completo</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Repetir contraseña</label>
            <input type="password" name="password2" required>
        </div>
        <button type="submit" class="btn-primary btn-block">Registrarse</button>
    </form>

    <p class="auth-link">¿Ya tienes cuenta? <a href="/login.php">Inicia sesión</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>