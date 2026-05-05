<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$exito = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim($_POST['nombre']);
    $email   = trim($_POST['email']);
    $mensaje = trim($_POST['mensaje']);

    if (empty($nombre) || empty($email) || empty($mensaje)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email introducido no es válido.';
    } else {
        $stmt = $conn->prepare("INSERT INTO mensajes (nombre, email, mensaje) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $email, $mensaje);

        if ($stmt->execute()) {
            $exito = '¡Mensaje enviado correctamente! Te responderemos lo antes posible.';
        } else {
            $error = 'Error al enviar el mensaje. Inténtalo de nuevo.';
        }
    }
}

require_once 'includes/header.php';
?>

<div style="max-width: 700px; margin: 0 auto;">

    <div style="margin-bottom: 2rem;">
        <h1>Contacto</h1>
        <p style="color: var(--color-secundario); margin-top: 0.5rem;">
            ¿Tienes alguna duda o necesitas ayuda? Escríbenos y te responderemos en menos de 24 horas.
        </p>
    </div>

    <?php if ($exito): ?>
        <div class="alert alert-success"><?= $exito ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <!-- Tarjetas de información de contacto -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: white; border: 1px solid var(--color-borde); border-radius: 12px;
                    padding: 1.2rem; text-align: center;">
            <div style="font-size: 1.8rem; margin-bottom: 0.5rem;">📧</div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-secundario); margin-bottom: 0.3rem;">EMAIL</div>
            <div style="font-size: 0.9rem;">soporte@hostpro.com</div>
        </div>
        <div style="background: white; border: 1px solid var(--color-borde); border-radius: 12px;
                    padding: 1.2rem; text-align: center;">
            <div style="font-size: 1.8rem; margin-bottom: 0.5rem;">📞</div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-secundario); margin-bottom: 0.3rem;">TELÉFONO</div>
            <div style="font-size: 0.9rem;">+34 900 123 456</div>
        </div>
        <div style="background: white; border: 1px solid var(--color-borde); border-radius: 12px;
                    padding: 1.2rem; text-align: center;">
            <div style="font-size: 1.8rem; margin-bottom: 0.5rem;">🕐</div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-secundario); margin-bottom: 0.3rem;">HORARIO</div>
            <div style="font-size: 0.9rem;">Lun–Vie 9:00–18:00</div>
        </div>
    </div>

    <!-- Formulario -->
    <div style="background: white; border: 1px solid var(--color-borde); border-radius: 12px; padding: 2rem;">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.2rem;">Envíanos un mensaje</h2>

        <form method="POST" action="/contacto.php">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" required
                           placeholder="Tu nombre"
                           value="<?= htmlspecialchars($_POST['nombre'] ?? (estaLogueado() ? $_SESSION['nombre'] : '')) ?>">
                </div>
                <div class="form-group">
                    <label>Email de contacto</label>
                    <input type="email" name="email" required
                           placeholder="tu@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? (estaLogueado() ? $_SESSION['email'] : '')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Mensaje</label>
                <textarea name="mensaje" rows="6" required
                          placeholder="Describe tu consulta o incidencia con el mayor detalle posible..."
                          style="resize: vertical;"><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-primary" style="padding: 0.8rem 2rem;">
                ✉️ Enviar mensaje
            </button>

        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>