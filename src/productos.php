<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Obtener categorías para el filtro
$categorias = $conn->query("SELECT * FROM categorias");

// Filtrar por categoría si se selecciona una
$filtro = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

if ($filtro > 0) {
    $stmt = $conn->prepare("SELECT p.*, c.nombre AS categoria 
                            FROM productos p 
                            JOIN categorias c ON p.id_categoria = c.id_categoria 
                            WHERE p.id_categoria = ?");
    $stmt->bind_param("i", $filtro);
    $stmt->execute();
    $productos = $stmt->get_result();
} else {
    $productos = $conn->query("SELECT p.*, c.nombre AS categoria 
                               FROM productos p 
                               JOIN categorias c ON p.id_categoria = c.id_categoria");
}

require_once 'includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1>Nuestros Servicios</h1>
    <p style="color: var(--color-secundario); margin-top: 0.5rem;">
        Explora todo nuestro catálogo de servicios de hosting profesional.
    </p>
</div>

<!-- Filtro por categoría -->
<div class="filtros">
    <a href="/productos.php" class="btn-filtro <?= $filtro === 0 ? 'activo' : '' ?>">
        Todos
    </a>
    <?php
    // Resetear el puntero del resultado
    $categorias->data_seek(0);
    while ($cat = $categorias->fetch_assoc()):
    ?>
        <a href="/productos.php?categoria=<?= $cat['id_categoria'] ?>"
           class="btn-filtro <?= $filtro === (int)$cat['id_categoria'] ? 'activo' : '' ?>">
            <?= htmlspecialchars($cat['nombre']) ?>
        </a>
    <?php endwhile; ?>
</div>

<!-- Grid de productos -->
<?php if ($productos->num_rows === 0): ?>
    <div class="alert alert-info">
        No hay productos disponibles en esta categoría de momento.
    </div>
<?php else: ?>
    <div class="productos-grid">
        <?php while ($prod = $productos->fetch_assoc()): ?>
        <div class="producto-card">
            <div class="producto-categoria">
                <?= htmlspecialchars($prod['categoria']) ?>
            </div>
            <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
            <p><?= htmlspecialchars($prod['descripcion']) ?></p>
            <div class="producto-precio">
                <?= number_format($prod['precio'], 2) ?> €<span style="font-size:0.9rem; font-weight:400; color:var(--color-secundario)">/mes</span>
            </div>

            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:auto;">
                <a href="/producto.php?id=<?= $prod['id_producto'] ?>" class="btn-secondary">
                    Ver detalles
                </a>

                <?php if (estaLogueado()): ?>
                    <form method="POST" action="/carrito.php" style="margin:0;">
                        <input type="hidden" name="accion" value="agregar">
                        <input type="hidden" name="id_producto" value="<?= $prod['id_producto'] ?>">
                        <button type="submit" class="btn-primary">Añadir al carrito</button>
                    </form>
                <?php else: ?>
                    <a href="/login.php" class="btn-primary">Contratar</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>