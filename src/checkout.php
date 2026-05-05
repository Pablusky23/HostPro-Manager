<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requiereLogin();

// Si el carrito está vacío, redirigir
if (empty($_SESSION['carrito'])) {
    header("Location: /carrito.php");
    exit();
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_pago = $_POST['metodo_pago'];
    $id_usuario = $_SESSION['id_usuario'];

    // Calcular el total
    $total = 0;
    $items = [];
    foreach ($_SESSION['carrito'] as $id_prod => $cantidad) {
        $stmt = $conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->bind_param("i", $id_prod);
        $stmt->execute();
        $prod = $stmt->get_result()->fetch_assoc();
        if ($prod) {
            $prod['cantidad'] = $cantidad;
            $total += $prod['precio'] * $cantidad;
            $items[] = $prod;
        }
    }

    // 1. Crear el pedido
    $stmt = $conn->prepare("INSERT INTO pedidos (total, estado, id_usuario) VALUES (?, 'pagado', ?)");
    $stmt->bind_param("di", $total, $id_usuario);
    $stmt->execute();
    $id_pedido = $conn->insert_id;

    // 2. Insertar los detalles del pedido
    foreach ($items as $item) {
        $stmt2 = $conn->prepare("INSERT INTO detalle_pedido (cantidad, precio, id_pedido, id_producto) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("idii", $item['cantidad'], $item['precio'], $id_pedido, $item['id_producto']);
        $stmt2->execute();
    }

    // 3. Registrar el pago
    $stmt3 = $conn->prepare("INSERT INTO pagos (metodo_pago, estado, id_pedido) VALUES (?, 'completado', ?)");
    $stmt3->bind_param("si", $metodo_pago, $id_pedido);
    $stmt3->execute();

    // 4. Vaciar el carrito
    $_SESSION['carrito'] = [];

    // 5. Redirigir a confirmación
    header("Location: /pedidos.php?exito=1");
    exit();
}

require_once 'includes/header.php';
?>

<div class="checkout-container">
    <h1>💳 Finalizar compra</h1>

    <div class="resumen-pedido">
        <h2>Resumen del pedido</h2>
        <?php
        $total = 0;
        foreach ($_SESSION['carrito'] as $id_prod => $cantidad) {
            $stmt = $conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
            $stmt->bind_param("i", $id_prod);
            $stmt->execute();
            $prod = $stmt->get_result()->fetch_assoc();
            if ($prod) {
                echo "<p>" . htmlspecialchars($prod['nombre']) . " x{$cantidad} — " . number_format($prod['precio'] * $cantidad, 2) . " €</p>";
                $total += $prod['precio'] * $cantidad;
            }
        }
        ?>
        <p class="total-final"><strong>Total: <?= number_format($total, 2) ?> €/mes</strong></p>
    </div>

    <form method="POST" action="/checkout.php">
        <h2>Método de pago</h2>
        <div class="metodos-pago">
            <label>
                <input type="radio" name="metodo_pago" value="tarjeta" required>
                💳 Tarjeta de crédito/débito
            </label>
            <label>
                <input type="radio" name="metodo_pago" value="paypal">
                🅿️ PayPal
            </label>
            <label>
                <input type="radio" name="metodo_pago" value="transferencia">
                🏦 Transferencia bancaria
            </label>
        </div>
        <p class="aviso-simulado">⚠️ Este es un pago simulado. No se realizará ningún cargo real.</p>
        <button type="submit" class="btn-primary btn-block">✅ Confirmar y pagar</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>