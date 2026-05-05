<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
requiereAdmin();

// Cambiar estado de un pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['estado'])) {
    $id     = (int)$_POST['id_pedido'];
    $estado = $_POST['estado'];
    $estados_validos = ['pendiente', 'pagado', 'procesando', 'cancelado'];

    if (in_array($estado, $estados_validos)) {
        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->bind_param("si", $estado, $id);
        $stmt->execute();
    }
    header("Location: /admin/pedidos.php?ok=actualizado");
    exit();
}

// Filtros
$filtro_estado  = isset($_GET['estado'])  ? $_GET['estado']        : '';
$filtro_usuario = isset($_GET['usuario']) ? (int)$_GET['usuario']  : 0;

// Construir la consulta con filtros
$where = [];
$params = [];
$tipos  = '';

if ($filtro_estado !== '') {
    $where[]  = "p.estado = ?";
    $params[] = $filtro_estado;
    $tipos   .= 's';
}
if ($filtro_usuario > 0) {
    $where[]  = "p.id_usuario = ?";
    $params[] = $filtro_usuario;
    $tipos   .= 'i';
}

$sql = "SELECT p.*, u.nombre AS nombre_usuario, u.email
        FROM pedidos p
        JOIN usuarios u ON p.id_usuario = u.id_usuario";

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY p.fecha DESC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    $pedidos = $stmt->get_result();
} else {
    $pedidos = $conn->query($sql);
}

// Estadísticas rápidas por estado
$stats = [];
foreach (['pendiente', 'pagado', 'procesando', 'cancelado'] as $e) {
    $r = $conn->query("SELECT COUNT(*) as c FROM pedidos WHERE estado = '$e'");
    $stats[$e] = $r->fetch_assoc()['c'];
}

require_once '../includes/header.php';

$badge = [
    'pendiente'  => 'background:#fef3c7; color:#92400e;',
    'pagado'     => 'background:#dcfce7; color:#166534;',
    'cancelado'  => 'background:#fee2e2; color:#991b1b;',
    'procesando' => 'background:#dbeafe; color:#1e40af;',
];
?>

<div style="display:flex; justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
    <div>
        <h1 style="margin-bottom:0.3rem;">🛒 Gestión de Pedidos</h1>
        <p style="color:var(--color-secundario); font-size:0.9rem;">
            Total: <?= array_sum($stats) ?> pedidos registrados
        </p>
    </div>
    <a href="/admin/index.php" style="color:var(--color-secundario);
       text-decoration:none; font-size:0.9rem;">← Volver al panel</a>
</div>

<!-- Alertas -->
<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Estado actualizado correctamente.</div>
<?php endif; ?>

<!-- Tarjetas de estadísticas por estado -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
    <?php
    $iconos = ['pendiente'=>'⏳','pagado'=>'✅','procesando'=>'⚙️','cancelado'=>'❌'];
    foreach ($stats as $estado => $total):
    ?>
    <a href="/admin/pedidos.php?estado=<?= $estado ?>"
       style="background:white; border:1px solid var(--color-borde); border-radius:12px;
              padding:1.2rem; text-align:center; text-decoration:none; color:inherit;
              transition:box-shadow 0.2s;"
       onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'"
       onmouseout="this.style.boxShadow='none'">
        <div style="font-size:1.6rem; margin-bottom:0.3rem;"><?= $iconos[$estado] ?></div>
        <div style="font-size:1.6rem; font-weight:700; color:var(--color-primario);"><?= $total ?></div>
        <div style="font-size:0.82rem; text-transform:capitalize;
                    color:var(--color-secundario);"><?= ucfirst($estado) ?></div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Filtros activos -->
<div style="display:flex; gap:0.6rem; flex-wrap:wrap; margin-bottom:1.2rem; align-items:center;">
    <span style="font-size:0.85rem; color:var(--color-secundario);">Filtrar por estado:</span>
    <a href="/admin/pedidos.php"
       class="btn-filtro <?= $filtro_estado === '' ? 'activo' : '' ?>">Todos</a>
    <?php foreach (['pendiente','pagado','procesando','cancelado'] as $e): ?>
        <a href="/admin/pedidos.php?estado=<?= $e ?>"
           class="btn-filtro <?= $filtro_estado === $e ? 'activo' : '' ?>">
            <?= ucfirst($e) ?>
        </a>
    <?php endforeach; ?>
    <?php if ($filtro_usuario): ?>
        <span style="background:#dbeafe; color:#1e40af; padding:0.2rem 0.8rem;
                     border-radius:20px; font-size:0.82rem;">
            Filtrado por usuario #<?= $filtro_usuario ?>
            <a href="/admin/pedidos.php" style="color:#1e40af; margin-left:0.4rem;">✕</a>
        </span>
    <?php endif; ?>
</div>

<!-- Tabla de pedidos -->
<?php if ($pedidos->num_rows === 0): ?>
    <div class="alert alert-info">No hay pedidos con ese criterio.</div>
<?php else: ?>
<div style="background:white; border:1px solid var(--color-borde);
            border-radius:12px; overflow:hidden;">
    <table class="tabla-datos" style="margin:0;">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado actual</th>
                <th>Cambiar estado</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $pedidos->fetch_assoc()): ?>
            <tr>
                <td style="font-weight:600;">#<?= $p['id_pedido'] ?></td>
                <td>
                    <div style="font-weight:600; font-size:0.9rem;">
                        <?= htmlspecialchars($p['nombre_usuario']) ?>
                    </div>
                    <div style="font-size:0.8rem; color:var(--color-secundario);">
                        <?= htmlspecialchars($p['email']) ?>
                    </div>
                </td>
                <td style="font-size:0.88rem; color:var(--color-secundario);">
                    <?= date('d/m/Y H:i', strtotime($p['fecha'])) ?>
                </td>
                <td style="font-weight:600; color:var(--color-primario);">
                    <?= number_format($p['total'], 2) ?> €
                </td>
                <td>
                    <span style="padding:0.25rem 0.8rem; border-radius:20px; font-size:0.8rem;
                                 font-weight:600; <?= $badge[$p['estado']] ?? '' ?>">
                        <?= ucfirst($p['estado']) ?>
                    </span>
                </td>
                <td>
                    <form method="POST" action="/admin/pedidos.php"
                          style="display:flex; gap:0.5rem; align-items:center;">
                        <input type="hidden" name="id_pedido" value="<?= $p['id_pedido'] ?>">
                        <select name="estado"
                                style="padding:0.35rem 0.6rem; border:1px solid var(--color-borde);
                                       border-radius:6px; font-size:0.85rem; background:white;">
                            <?php foreach (['pendiente','pagado','procesando','cancelado'] as $e): ?>
                                <option value="<?= $e ?>" <?= $p['estado'] === $e ? 'selected' : '' ?>>
                                    <?= ucfirst($e) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-primary"
                                style="padding:0.35rem 0.8rem; font-size:0.82rem;">
                            ✓
                        </button>
                    </form>
                </td>
                <td>
                    <a href="/pedido_detalle.php?id=<?= $p['id_pedido'] ?>"
                       class="btn-secondary"
                       style="padding:0.35rem 0.8rem; font-size:0.82rem;">
                        Ver
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>