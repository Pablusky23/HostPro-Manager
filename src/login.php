<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Si ya está logueado, redirigir
if (estaLogueado()) {
    header("Location: /index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Introduce tu email y contraseña.';
    } else {
        $stmt = $conn->prepare("SELECT id_usuario, nombre, email, password, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        // Verificar contraseña
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Guardar datos en la sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigir según el rol
            if ($usuario['rol'] === 'admin') {
                header("Location: /admin/index.php");
            } else {
                header("Location: /index.php");
            }
            exit();
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <h1>Iniciar sesión</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/login.php">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn-primary btn-block">Entrar</button>
    </form>

    <p class="auth-link">¿No tienes cuenta? <a href="/register.php">Regístrate gratis</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>