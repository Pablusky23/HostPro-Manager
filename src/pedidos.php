<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requiereLogin();

$id_usuario = $_SESSION['id_usuario'];

// Cancelar pedido
if (isset($_GET['cancelar'])) {
    $id_pedido = (int)$_GET['cancelar'];
    $stmt = $conn->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id_pedido = ? AND id_usuario = ? AND estado = 'pendiente'");
    $stmt->bind_param("ii", $id_pedido, $id_usuario);
    $stmt->execute();
    header("Location: /pedidos.php");
    exit();
}

// Obtener pedidos del usuario
$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$pedidos = $stmt->get_result();

require_once 'includes/header.php';
?>

<h1>📦 Mis Pedidos</h1>

<?php if (isset($_GET['exito'])): ?>
    <div class="alert alert-success">¡Pedido realizado con éxito! Gracias por tu compra.</div>
<?php endif; ?>

<?php if ($pedidos->num_rows === 0): ?>
    <div class="alert alert-info">No tienes pedidos todavía. <a href="/index.php">Ver servicios</a></div>
<?php else: ?>
    <table class="tabla-datos">
        <thead>
            <tr>
                <th># Pedido</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($pedido = $pedidos->fetch_assoc()): ?>
            <tr>
                <td>#<?= $pedido['id_pedido'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>
                <td><?= number_format($pedido['total'], 2) ?> €</td>
                <td>
                    <span class="badge badge-<?= $pedido['estado'] ?>">
                        <?= ucfirst($pedido['estado']) ?>
                    </span>
                </td>
                <td>
                    <a href="/pedido_detalle.php?id=<?= $pedido['id_pedido'] ?>" class="btn-secondary">Ver detalle</a>
                    <?php if ($pedido['estado'] === 'pendiente'): ?>
                        <a href="/pedidos.php?cancelar=<?= $pedido['id_pedido'] ?>" 
                           onclick="return confirm('¿Cancelar este pedido?')"
                           class="btn-danger">Cancelar</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>