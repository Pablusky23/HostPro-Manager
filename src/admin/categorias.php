<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
requiereAdmin();

$error = '';

// ELIMINAR categoría
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];

    // Comprobar si tiene productos asociados antes de eliminar
    $check = $conn->prepare("SELECT COUNT(*) as c FROM productos WHERE id_categoria = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $tiene_productos = $check->get_result()->fetch_assoc()['c'];

    if ($tiene_productos > 0) {
        header("Location: /admin/categorias.php?error=tiene_productos&n=$tiene_productos");
    } else {
        $conn->query("DELETE FROM categorias WHERE id_categoria = $id");
        header("Location: /admin/categorias.php?ok=eliminada");
    }
    exit();
}

// CREAR o EDITAR categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);

    if (empty($nombre)) {
        $error = 'El nombre de la categoría no puede estar vacío.';
    } else {
        if (!empty($_POST['id_categoria'])) {
            // Editar
            $id = (int)$_POST['id_categoria'];
            $stmt = $conn->prepare("UPDATE categorias SET nombre = ? WHERE id_categoria = ?");
            $stmt->bind_param("si", $nombre, $id);
        } else {
            // Crear
            $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
        }
        $stmt->execute();
        header("Location: /admin/categorias.php?ok=guardada");
        exit();
    }
}

// Obtener categoría a editar
$editar = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $editar = $conn->query("SELECT * FROM categorias WHERE id_categoria = $id")->fetch_assoc();
}

// Obtener todas las categorías con el número de productos de cada una
$categorias = $conn->query(
    "SELECT c.*, COUNT(p.id_producto) AS num_productos
     FROM categorias c
     LEFT JOIN productos p ON c.id_categoria = p.id_categoria
     GROUP BY c.id_categoria
     ORDER BY c.nombre ASC"
);

require_once '../includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
    <div>
        <h1 style="margin-bottom:0.3rem;">🗂️ Gestión de Categorías</h1>
        <p style="color:var(--color-secundario); font-size:0.9rem;">
            Organiza los productos del catálogo en categorías.
        </p>
    </div>
    <a href="/admin/index.php" style="color:var(--color-secundario);
       text-decoration:none; font-size:0.9rem;">← Volver al panel</a>
</div>

<!-- Alertas -->
<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">
        <?= $_GET['ok'] === 'eliminada' ? 'Categoría eliminada correctamente.' : 'Categoría guardada correctamente.' ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'tiene_productos'): ?>
    <div class="alert alert-error">
        No se puede eliminar esta categoría porque tiene <strong><?= (int)$_GET['n'] ?> producto(s)</strong> asociados.
        Primero reasigna o elimina esos productos desde
        <a href="/admin/productos.php">Gestión de Productos</a>.
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 1.6fr; gap:1.5rem; align-items:start;">

    <!-- Formulario crear / editar -->
    <div style="background:white; border:1px solid var(--color-borde);
                border-radius:12px; padding:1.5rem;">
        <h2 style="font-size:1rem; margin-bottom:1.2rem;">
            <?= $editar ? '✏️ Editar categoría' : '➕ Nueva categoría' ?>
        </h2>

        <form method="POST" action="/admin/categorias.php">
            <?php if ($editar): ?>
                <input type="hidden" name="id_categoria" value="<?= $editar['id_categoria'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Nombre de la categoría</label>
                <input type="text" name="nombre" required
                       placeholder="Ej: Hosting Compartido"
                       value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
            </div>

            <div style="display:flex; gap:0.7rem;">
                <button type="submit" class="btn-primary">
                    <?= $editar ? 'Guardar cambios' : 'Crear categoría' ?>
                </button>
                <?php if ($editar): ?>
                    <a href="/admin/categorias.php" class="btn-secondary">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabla de categorías -->
    <div style="background:white; border:1px solid var(--color-borde);
                border-radius:12px; overflow:hidden;">
        <div style="padding:1rem 1.5rem; border-bottom:1px solid var(--color-borde);
                    background:#f8fafc;">
            <h2 style="font-size:1rem; margin:0;">Categorías existentes</h2>
        </div>

        <?php if ($categorias->num_rows === 0): ?>
            <div style="padding:1.5rem;">
                <div class="alert alert-info" style="margin:0;">
                    No hay categorías creadas todavía.
                </div>
            </div>
        <?php else: ?>
        <table class="tabla-datos" style="margin:0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th style="text-align:center;">Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--color-secundario); font-size:0.85rem;">
                        #<?= $cat['id_categoria'] ?>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($cat['nombre']) ?></td>
                    <td style="text-align:center;">
                        <?php if ($cat['num_productos'] > 0): ?>
                            <a href="/admin/productos.php?categoria=<?= $cat['id_categoria'] ?>"
                               style="color:var(--color-primario); font-weight:600;">
                                <?= $cat['num_productos'] ?>
                            </a>
                        <?php else: ?>
                            <span style="color:var(--color-secundario);">0</span>
                        <?php endif; ?>
                    </td>
                    <td style="display:flex; gap:0.5rem;">
                        <a href="/admin/categorias.php?editar=<?= $cat['id_categoria'] ?>"
                           class="btn-secondary"
                           style="padding:0.35rem 0.8rem; font-size:0.82rem;">
                            Editar
                        </a>
                        <?php if ($cat['num_productos'] == 0): ?>
                            <a href="/admin/categorias.php?eliminar=<?= $cat['id_categoria'] ?>"
                               onclick="return confirm('¿Eliminar la categoría <?= htmlspecialchars(addslashes($cat['nombre'])) ?>?')"
                               class="btn-danger"
                               style="padding:0.35rem 0.8rem; font-size:0.82rem;">
                                Eliminar
                            </a>
                        <?php else: ?>
                            <span style="font-size:0.8rem; color:var(--color-secundario);
                                         padding:0.35rem 0; align-self:center;">
                                Con productos
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>