<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
requiereAdmin();

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];

    // Evitar que el admin se elimine a sí mismo
    if ($id === (int)$_SESSION['id_usuario']) {
        header("Location: /admin/usuarios.php?error=nopropio");
        exit();
    }

    $conn->query("DELETE FROM usuarios WHERE id_usuario = $id");
    header("Location: /admin/usuarios.php?ok=eliminado");
    exit();
}

// Búsqueda por nombre o email
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

if ($busqueda !== '') {
    $like = "%$busqueda%";
    $stmt = $conn->prepare("SELECT * FROM usuarios 
                            WHERE (nombre LIKE ? OR email LIKE ?) 
                            ORDER BY fecha_registro DESC");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $usuarios = $stmt->get_result();
} else {
    $usuarios = $conn->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC");
}

// Contadores
$total_clientes = $conn->query("SELECT COUNT(*) as c FROM usuarios WHERE rol='cliente'")->fetch_assoc()['c'];
$total_admins   = $conn->query("SELECT COUNT(*) as c FROM usuarios WHERE rol='admin'")->fetch_assoc()['c'];

require_once '../includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
    <div>
        <h1 style="margin-bottom:0.3rem;">👥 Gestión de Usuarios</h1>
        <p style="color:var(--color-secundario); font-size:0.9rem;">
            <?= $total_clientes ?> clientes · <?= $total_admins ?> administradores
        </p>
    </div>
    <a href="/admin/index.php" style="color:var(--color-secundario);
       text-decoration:none; font-size:0.9rem;">← Volver al panel</a>
</div>

<!-- Alertas -->
<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Usuario eliminado correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'nopropio'): ?>
    <div class="alert alert-error">No puedes eliminar tu propia cuenta de administrador.</div>
<?php endif; ?>

<!-- Buscador -->
<form method="GET" action="/admin/usuarios.php"
      style="display:flex; gap:0.7rem; margin-bottom:1.5rem;">
    <input type="text" name="buscar"
           placeholder="Buscar por nombre o email..."
           value="<?= htmlspecialchars($busqueda) ?>"
           style="flex:1; padding:0.65rem 1rem; border:1px solid var(--color-borde);
                  border-radius:8px; font-size:0.9rem;">
    <button type="submit" class="btn-primary">Buscar</button>
    <?php if ($busqueda): ?>
        <a href="/admin/usuarios.php" class="btn-secondary">Limpiar</a>
    <?php endif; ?>
</form>

<!-- Tabla de usuarios -->
<?php if ($usuarios->num_rows === 0): ?>
    <div class="alert alert-info">No se encontraron usuarios con ese criterio de búsqueda.</div>
<?php else: ?>
<div style="background:white; border:1px solid var(--color-borde);
            border-radius:12px; overflow:hidden;">
    <table class="tabla-datos" style="margin:0;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Fecha de registro</th>
                <th>Pedidos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = $usuarios->fetch_assoc()):
                // Contar pedidos de ese usuario
                $np = $conn->prepare("SELECT COUNT(*) as c FROM pedidos WHERE id_usuario = ?");
                $np->bind_param("i", $u['id_usuario']);
                $np->execute();
                $num_pedidos = $np->get_result()->fetch_assoc()['c'];

                $es_yo = ($u['id_usuario'] == $_SESSION['id_usuario']);
            ?>
            <tr>
                <td style="color:var(--color-secundario); font-size:0.85rem;">
                    #<?= $u['id_usuario'] ?>
                </td>
                <td>
                    <div style="font-weight:600;"><?= htmlspecialchars($u['nombre']) ?></div>
                    <?php if ($es_yo): ?>
                        <div style="font-size:0.75rem; color:var(--color-primario);">(tú)</div>
                    <?php endif; ?>
                </td>
                <td style="font-size:0.9rem;"><?= htmlspecialchars($u['email']) ?></td>
                <td>
                    <?php if ($u['rol'] === 'admin'): ?>
                        <span style="background:#dbeafe; color:#1e40af; padding:0.2rem 0.7rem;
                                     border-radius:20px; font-size:0.78rem; font-weight:600;">
                            Admin
                        </span>
                    <?php else: ?>
                        <span style="background:#f1f5f9; color:#475569; padding:0.2rem 0.7rem;
                                     border-radius:20px; font-size:0.78rem; font-weight:600;">
                            Cliente
                        </span>
                    <?php endif; ?>
                </td>
                <td style="font-size:0.88rem; color:var(--color-secundario);">
                    <?= date('d/m/Y', strtotime($u['fecha_registro'])) ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($num_pedidos > 0): ?>
                        <a href="/admin/pedidos.php?usuario=<?= $u['id_usuario'] ?>"
                           style="color:var(--color-primario); font-weight:600;">
                            <?= $num_pedidos ?>
                        </a>
                    <?php else: ?>
                        <span style="color:var(--color-secundario);">0</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!$es_yo): ?>
                        <a href="/admin/usuarios.php?eliminar=<?= $u['id_usuario'] ?>"
                           onclick="return confirm('¿Eliminar a <?= htmlspecialchars(addslashes($u['nombre'])) ?>? Esta acción no se puede deshacer.')"
                           class="btn-danger">
                            Eliminar
                        </a>
                    <?php else: ?>
                        <span style="font-size:0.8rem; color:var(--color-secundario);">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>