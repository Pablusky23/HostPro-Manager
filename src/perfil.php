<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requiereLogin();

$id_usuario = $_SESSION['id_usuario'];
$exito = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);

    if (empty($nombre) || empty($email)) {
        $error = 'Nombre y email son obligatorios.';
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $nombre, $email, $id_usuario);
        if ($stmt->execute()) {
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;
            $exito = 'Perfil actualizado correctamente.';
        } else {
            $error = 'Error al actualizar el perfil.';
        }
    }
}

// Obtener datos actuales
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

require_once 'includes/header.php';
?>

<div class="perfil-container">
    <h1>👤 Mi Perfil</h1>

    <?php if ($exito): ?>
        <div class="alert alert-success"><?= $exito ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/perfil.php">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
        </div>
        <div class="form-group">
            <label>Rol</label>
            <input type="text" value="<?= ucfirst($usuario['rol']) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Fecha de registro</label>
            <input type="text" value="<?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?>" disabled>
        </div>
        <button type="submit" class="btn-primary">Guardar cambios</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>