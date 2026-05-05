<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requiereLogin();  // Solo usuarios logueados pueden ver el carrito

// El carrito se guarda en la sesión como array
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar producto al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'agregar') {
    $id = (int)$_POST['id_producto'];
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]++;
    } else {
        $_SESSION['carrito'][$id] = 1;
    }
    header("Location: /carrito.php");
    exit();
}

// Eliminar producto del carrito
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    unset($_SESSION['carrito'][$id]);
    header("Location: /carrito.php");
    exit();
}

// Vaciar carrito
if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    header("Location: /carrito.php");
    exit();
}

// Obtener los productos del carrito con sus datos
$items = [];
$total = 0;

foreach ($_SESSION['carrito'] as $id_prod => $cantidad) {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id_prod);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    if ($prod) {
        $prod['cantidad'] = $cantidad;
        $prod['subtotal'] = $prod['precio'] * $cantidad;
        $total += $prod['subtotal'];
        $items[] = $prod;
    }
}

require_once 'includes/header.php';
?>

<h1>🛒 Mi Carrito</h1>

<?php if (empty($items)): ?>
    <div class="alert alert-info">Tu carrito está vacío. <a href="/index.php">Ver productos</a></div>
<?php else: ?>

<table class="tabla-carrito">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['nombre']) ?></td>
            <td><?= number_format($item['precio'], 2) ?> €</td>
            <td><?= $item['cantidad'] ?></td>
            <td><?= number_format($item['subtotal'], 2) ?> €</td>
            <td><a href="/carrito.php?eliminar=<?= $item['id_producto'] ?>" class="btn-danger">Eliminar</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTAL</strong></td>
            <td colspan="2"><strong><?= number_format($total, 2) ?> €/mes</strong></td>
        </tr>
    </tfoot>
</table>

<div class="carrito-acciones">
    <a href="/carrito.php?vaciar=1" class="btn-secondary">Vaciar carrito</a>
    <a href="/checkout.php" class="btn-primary">Finalizar compra →</a>
</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>