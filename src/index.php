<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Obtener categorías para el filtro
$categorias = $conn->query("SELECT * FROM categorias");

// Filtrar por categoría si se selecciona una
$filtro = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

if ($filtro > 0) {
    $stmt = $conn->prepare("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.id_categoria = ?");
    $stmt->bind_param("i", $filtro);
    $stmt->execute();
    $productos = $stmt->get_result();
} else {
    $productos = $conn->query("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria");
}
?>

<!-- Hero Section -->
<section class="hero">
    <h1>🖥️ HostPro Manager</h1>
    <p>La plataforma profesional para gestionar y contratar servicios de hosting</p>
    <a href="/productos.php" class="btn-primary">Ver todos los servicios</a>
</section>

<!-- Filtro por categoría -->
<section class="catalogo">
    <h2>Nuestros Servicios</h2>

    <div class="filtros">
        <a href="/index.php" class="btn-filtro <?= $filtro === 0 ? 'activo' : '' ?>">Todos</a>
        <?php while ($cat = $categorias->fetch_assoc()): ?>
            <a href="/index.php?categoria=<?= $cat['id_categoria'] ?>" 
               class="btn-filtro <?= $filtro === $cat['id_categoria'] ? 'activo' : '' ?>">
                <?= htmlspecialchars($cat['nombre']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Grid de productos -->
    <div class="productos-grid">
        <?php while ($prod = $productos->fetch_assoc()): ?>
        <div class="producto-card">
            <div class="producto-categoria"><?= htmlspecialchars($prod['categoria']) ?></div>
            <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
            <p><?= htmlspecialchars($prod['descripcion']) ?></p>
            <div class="producto-precio">
                <?= number_format($prod['precio'], 2) ?> €/mes
            </div>
            <a href="/producto.php?id=<?= $prod['id_producto'] ?>" class="btn-secondary">Ver detalles</a>
            <?php if (estaLogueado()): ?>
                <form method="POST" action="/carrito.php">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="id_producto" value="<?= $prod['id_producto'] ?>">
                    <button type="submit" class="btn-primary">Añadir al carrito</button>
                </form>
            <?php else: ?>
                <a href="/login.php" class="btn-primary">Contratar</a>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>