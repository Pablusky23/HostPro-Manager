<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
requiereAdmin();  // Solo admins pueden entrar aquí

// Estadísticas rápidas
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='cliente'")->fetch_assoc()['total'];
$total_productos = $conn->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'];
$total_pedidos = $conn->query("SELECT COUNT(*) as total FROM pedidos")->fetch_assoc()['total'];
$ingresos = $conn->query("SELECT SUM(total) as suma FROM pedidos WHERE estado='pagado'")->fetch_assoc()['suma'] ?? 0;

require_once '../includes/header.php';
?>

<div class="admin-dashboard">
    <h1>⚙️ Panel de Administración</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total_usuarios ?></div>
            <div class="stat-label">Clientes registrados</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $total_productos ?></div>
            <div class="stat-label">Productos activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $total_pedidos ?></div>
            <div class="stat-label">Pedidos totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($ingresos, 2) ?> €</div>
            <div class="stat-label">Ingresos totales</div>
        </div>
    </div>

    <div class="admin-nav">
        <a href="/admin/productos.php" class="admin-link">📦 Gestionar Productos</a>
        <a href="/admin/categorias.php" class="admin-link">🗂️ Gestionar Categorías</a>
        <a href="/admin/usuarios.php" class="admin-link">👥 Gestionar Usuarios</a>
        <a href="/admin/pedidos.php" class="admin-link">🛒 Gestionar Pedidos</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>