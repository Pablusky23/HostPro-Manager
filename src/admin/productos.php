<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
requiereAdmin();

$mensaje = '';

// ELIMINAR producto
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conn->query("DELETE FROM productos WHERE id_producto = $id");
    header("Location: /admin/productos.php?ok=eliminado");
    exit();
}

// CREAR o EDITAR producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $stock = (int)$_POST['stock'];
    $id_categoria = (int)$_POST['id_categoria'];

    if (isset($_POST['id_producto']) && !empty($_POST['id_producto'])) {
        // Editar
        $id = (int)$_POST['id_producto'];
        $stmt = $conn->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, id_categoria=? WHERE id_producto=?");
        $stmt->bind_param("ssdiii", $nombre, $descripcion, $precio, $stock, $id_categoria, $id);
    } else {
        // Crear
        $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssdii", $nombre, $descripcion, $precio, $stock, $id_categoria);
    }
    $stmt->execute();
    header("Location: /admin/productos.php?ok=guardado");
    exit();
}

// Obtener producto a editar
$editar = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $editar = $conn->query("SELECT * FROM productos WHERE id_producto = $id")->fetch_assoc();
}

$productos = $conn->query("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria");
$categorias = $conn->query("SELECT * FROM categorias");

require_once '../includes/header.php';
?>

<h1>📦 Gestión de Productos</h1>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Operación realizada correctamente.</div>
<?php endif; ?>

<!-- Formulario para crear/editar -->
<div class="admin-form">
    <h2><?= $editar ? 'Editar producto' : 'Nuevo producto' ?></h2>
    <form method="POST" action="/admin/productos.php">
        <?php if ($editar): ?>
            <input type="hidden" name="id_producto" value="<?= $editar['id_producto'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion"><?= htmlspecialchars($editar['descripcion'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Precio (€/mes)</label>
            <input type="number" name="precio" step="0.01" required value="<?= $editar['precio'] ?? '' ?>">
        </div>
        <div class="form-group">
            <label>Stock</label>
            <input type="number" name="stock" required value="<?= $editar['stock'] ?? '' ?>">
        </div>
        <div class="form-group">
            <label>Categoría</label>
            <select name="id_categoria" required>
                <?php $categorias->data_seek(0); while ($cat = $categorias->fetch_assoc()): ?>
                    <option value="<?= $cat['id_categoria'] ?>" 
                        <?= isset($editar) && $editar['id_categoria'] == $cat['id_categoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn-primary"><?= $editar ? 'Guardar cambios' : 'Crear producto' ?></button>
        <?php if ($editar): ?>
            <a href="/admin/productos.php" class="btn-secondary">Cancelar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla de productos -->
<table class="tabla-datos">
    <thead>
        <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Acciones</th></tr>
    </thead>
    <tbody>
        <?php while ($prod = $productos->fetch_assoc()): ?>
        <tr>
            <td><?= $prod['id_producto'] ?></td>
            <td><?= htmlspecialchars($prod['nombre']) ?></td>
            <td><?= number_format($prod['precio'], 2) ?> €</td>
            <td><?= $prod['stock'] ?></td>
            <td><?= htmlspecialchars($prod['categoria']) ?></td>
            <td>
                <a href="/admin/productos.php?editar=<?= $prod['id_producto'] ?>" class="btn-secondary">Editar</a>
                <a href="/admin/productos.php?eliminar=<?= $prod['id_producto'] ?>" 
                   onclick="return confirm('¿Eliminar este producto?')"
                   class="btn-danger">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>