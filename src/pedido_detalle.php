<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requiereLogin();

$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_usuario = $_SESSION['id_usuario'];

if (!$id_pedido) {
    header("Location: /pedidos.php");
    exit();
}

// Obtener el pedido (verificando que pertenece al usuario, o que es admin)
if (esAdmin()) {
    $stmt = $conn->prepare("SELECT p.*, u.nombre AS nombre_usuario, u.email 
                            FROM pedidos p 
                            JOIN usuarios u ON p.id_usuario = u.id_usuario 
                            WHERE p.id_pedido = ?");
    $stmt->bind_param("i", $id_pedido);
} else {
    $stmt = $conn->prepare("SELECT p.*, u.nombre AS nombre_usuario, u.email 
                            FROM pedidos p 
                            JOIN usuarios u ON p.id_usuario = u.id_usuario 
                            WHERE p.id_pedido = ? AND p.id_usuario = ?");
    $stmt->bind_param("ii", $id_pedido, $id_usuario);
}

$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    header("Location: /pedidos.php");
    exit();
}

// Obtener los productos del pedido
$stmt2 = $conn->prepare("SELECT dp.*, pr.nombre AS nombre_producto, pr.descripcion,
                                 c.nombre AS categoria
                          FROM detalle_pedido dp
                          JOIN productos pr ON dp.id_producto = pr.id_producto
                          JOIN categorias c ON pr.id_categoria = c.id_categoria
                          WHERE dp.id_pedido = ?");
$stmt2->bind_param("i", $id_pedido);
$stmt2->execute();
$detalles = $stmt2->get_result();

// Obtener el pago del pedido
$stmt3 = $conn->prepare("SELECT * FROM pagos WHERE id_pedido = ?");
$stmt3->bind_param("i", $id_pedido);
$stmt3->execute();
$pago = $stmt3->get_result()->fetch_assoc();

require_once 'includes/header.php';

// Colores según estado
$badge_colores = [
    'pendiente'   => 'background:#fef3c7; color:#92400e;',
    'pagado'      => 'background:#dcfce7; color:#166534;',
    'cancelado'   => 'background:#fee2e2; color:#991b1b;',
    'procesando'  => 'background:#dbeafe; color:#1e40af;',
];
$estado_color = $badge_colores[$pedido['estado']] ?? 'background:#f1f5f9; color:#64748b;';
?>

<!-- Cabecera de la página -->
<div style="margin-bottom:1.5rem;">
    <a href="<?= esAdmin() ? '/admin/pedidos.php' : '/pedidos.php' ?>"
       style="color:var(--color-secundario); text-decoration:none; font-size:0.9rem;">
        ← Volver a pedidos
    </a>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;
            gap:1rem; margin-bottom:2rem;">
    <div>
        <h1 style="margin-bottom:0.3rem;">Pedido #<?= $pedido['id_pedido'] ?></h1>
        <p style="color:var(--color-secundario); font-size:0.9rem;">
            Realizado el <?= date('d/m/Y \a \l\a\s H:i', strtotime($pedido['fecha'])) ?>
        </p>
    </div>
    <span style="padding:0.4rem 1.2rem; border-radius:20px; font-weight:600;
                 font-size:0.9rem; <?= $estado_color ?>">
        <?= ucfirst($pedido['estado']) ?>
    </span>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; align-items:start;">

    <!-- Columna izquierda: productos del pedido -->
    <div>
        <div style="background:white; border:1px solid var(--color-borde);
                    border-radius:12px; overflow:hidden; margin-bottom:1.5rem;">
            <div style="padding:1.2rem 1.5rem; border-bottom:1px solid var(--color-borde);
                        background:#f8fafc;">
                <h2 style="font-size:1rem; margin:0;">Productos contratados</h2>
            </div>

            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f1f5f9;">
                        <th style="padding:0.8rem 1.2rem; text-align:left; font-size:0.82rem;
                                   text-transform:uppercase; color:var(--color-secundario);
                                   letter-spacing:0.05em;">Servicio</th>
                        <th style="padding:0.8rem 1.2rem; text-align:center; font-size:0.82rem;
                                   text-transform:uppercase; color:var(--color-secundario);
                                   letter-spacing:0.05em;">Cantidad</th>
                        <th style="padding:0.8rem 1.2rem; text-align:right; font-size:0.82rem;
                                   text-transform:uppercase; color:var(--color-secundario);
                                   letter-spacing:0.05em;">Precio unit.</th>
                        <th style="padding:0.8rem 1.2rem; text-align:right; font-size:0.82rem;
                                   text-transform:uppercase; color:var(--color-secundario);
                                   letter-spacing:0.05em;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($det = $detalles->fetch_assoc()): ?>
                    <tr style="border-top:1px solid var(--color-borde);">
                        <td style="padding:1rem 1.2rem;">
                            <div style="font-weight:600; margin-bottom:0.2rem;">
                                <?= htmlspecialchars($det['nombre_producto']) ?>
                            </div>
                            <div style="font-size:0.82rem; color:var(--color-secundario);">
                                <?= htmlspecialchars($det['categoria']) ?>
                            </div>
                        </td>
                        <td style="padding:1rem 1.2rem; text-align:center;">
                            <?= $det['cantidad'] ?>
                        </td>
                        <td style="padding:1rem 1.2rem; text-align:right;">
                            <?= number_format($det['precio'], 2) ?> €
                        </td>
                        <td style="padding:1rem 1.2rem; text-align:right; font-weight:600;">
                            <?= number_format($det['precio'] * $det['cantidad'], 2) ?> €
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--color-borde); background:#f8fafc;">
                        <td colspan="3" style="padding:1rem 1.2rem; font-weight:700;
                                               text-align:right;">TOTAL</td>
                        <td style="padding:1rem 1.2rem; font-weight:700; text-align:right;
                                   font-size:1.1rem; color:var(--color-primario);">
                            <?= number_format($pedido['total'], 2) ?> €/mes
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Columna derecha: resumen del pedido y pago -->
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        <!-- Información del cliente (solo admin) -->
        <?php if (esAdmin()): ?>
        <div style="background:white; border:1px solid var(--color-borde); border-radius:12px; padding:1.5rem;">
            <h3 style="font-size:0.95rem; margin-bottom:1rem;">👤 Cliente</h3>
            <p style="margin-bottom:0.3rem; font-weight:600;">
                <?= htmlspecialchars($pedido['nombre_usuario']) ?>
            </p>
            <p style="font-size:0.9rem; color:var(--color-secundario);">
                <?= htmlspecialchars($pedido['email']) ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Información del pago -->
        <div style="background:white; border:1px solid var(--color-borde); border-radius:12px; padding:1.5rem;">
            <h3 style="font-size:0.95rem; margin-bottom:1rem;">💳 Información de pago</h3>

            <?php if ($pago): ?>
                <div style="display:flex; flex-direction:column; gap:0.7rem; font-size:0.9rem;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--color-secundario);">Método</span>
                        <span style="font-weight:600; text-transform:capitalize;">
                            <?= htmlspecialchars($pago['metodo_pago']) ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--color-secundario);">Estado del pago</span>
                        <span style="font-weight:600; text-transform:capitalize;">
                            <?= htmlspecialchars($pago['estado']) ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--color-secundario);">Fecha de pago</span>
                        <span><?= date('d/m/Y H:i', strtotime($pago['fecha'])) ?></span>
                    </div>
                    <hr style="border-color:var(--color-borde); margin:0.3rem 0;">
                    <div style="display:flex; justify-content:space-between; font-size:1rem;">
                        <span style="font-weight:700;">Total cobrado</span>
                        <span style="font-weight:700; color:var(--color-primario);">
                            <?= number_format($pedido['total'], 2) ?> €
                        </span>
                    </div>
                </div>
            <?php else: ?>
                <p style="color:var(--color-secundario); font-size:0.9rem;">
                    Todavía no hay información de pago registrada para este pedido.
                </p>
            <?php endif; ?>
        </div>

        <!-- Acción: cancelar pedido (solo cliente, solo si está pendiente) -->
        <?php if (!esAdmin() && $pedido['estado'] === 'pendiente'): ?>
        <div style="background:#fff7ed; border:1px solid #fdba74;
                    border-radius:12px; padding:1.5rem;">
            <p style="font-size:0.88rem; color:#92400e; margin-bottom:1rem;">
                Este pedido aún está pendiente de procesarse. Puedes cancelarlo si lo necesitas.
            </p>
            <a href="/pedidos.php?cancelar=<?= $pedido['id_pedido'] ?>"
               onclick="return confirm('¿Estás seguro de que quieres cancelar este pedido?')"
               class="btn-danger" style="display:block; text-align:center; padding:0.7rem;">
                Cancelar pedido
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>