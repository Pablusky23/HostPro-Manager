<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Comprobar que se ha pasado un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /productos.php");
    exit();
}

$id = (int)$_GET['id'];

// Obtener el producto con su categoría
$stmt = $conn->prepare("SELECT p.*, c.nombre AS categoria 
                        FROM productos p 
                        JOIN categorias c ON p.id_categoria = c.id_categoria 
                        WHERE p.id_producto = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();

// Si el producto no existe, redirigir al catálogo
if (!$prod) {
    header("Location: /productos.php");
    exit();
}

require_once 'includes/header.php';
?>

<!-- Enlace para volver -->
<div style="margin-bottom: 1.5rem;">
    <a href="/productos.php" style="color: var(--color-secundario); text-decoration: none; font-size: 0.9rem;">
        ← Volver al catálogo
    </a>
</div>

<!-- Detalle del producto -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; align-items: start;">

    <!-- Columna izquierda: info del producto -->
    <div style="background: white; border: 1px solid var(--color-borde); border-radius: 12px; padding: 2rem;">

        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-primario);
                    font-weight: 600; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
            <?= htmlspecialchars($prod['categoria']) ?>
        </div>

        <h1 style="font-size: 1.8rem; margin-bottom: 1rem;">
            <?= htmlspecialchars($prod['nombre']) ?>
        </h1>

        <div style="font-size: 2.2rem; font-weight: 700; color: var(--color-primario); margin-bottom: 1.5rem;">
            <?= number_format($prod['precio'], 2) ?> €
            <span style="font-size: 1rem; font-weight: 400; color: var(--color-secundario)">/mes</span>
        </div>

        <p style="color: var(--color-secundario); line-height: 1.7; margin-bottom: 1.5rem;">
            <?= nl2br(htmlspecialchars($prod['descripcion'])) ?>
        </p>

        <!-- Estado del stock -->
        <?php if ($prod['stock'] > 0): ?>
            <div style="display: inline-flex; align-items: center; gap: 0.4rem;
                        background: #dcfce7; color: #166534; padding: 0.4rem 0.9rem;
                        border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1.5rem;">
                ✅ Disponible — <?= $prod['stock'] ?> unidades
            </div>
        <?php else: ?>
            <div style="display: inline-flex; align-items: center; gap: 0.4rem;
                        background: #fee2e2; color: #991b1b; padding: 0.4rem 0.9rem;
                        border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1.5rem;">
                ❌ Agotado
            </div>
        <?php endif; ?>
    </div>

    <!-- Columna derecha: botones de acción -->
    <div style="background: white; border: 1px solid var(--color-borde); border-radius: 12px; padding: 2rem; position: sticky; top: 80px;">

        <h2 style="font-size: 1.1rem; margin-bottom: 1.5rem;">Contratar este servicio</h2>

        <?php if ($prod['stock'] > 0): ?>

            <?php if (estaLogueado()): ?>
                <!-- Usuario logueado: puede añadir al carrito -->
                <form method="POST" action="/carrito.php">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="id_producto" value="<?= $prod['id_producto'] ?>">
                    <button type="submit" class="btn-primary btn-block" style="margin-bottom: 0.8rem;">
                        🛒 Añadir al carrito
                    </button>
                </form>
                <a href="/carrito.php" class="btn-secondary btn-block">
                    Ver carrito
                </a>
            <?php else: ?>
                <!-- Visitante: redirigir al login -->
                <a href="/login.php" class="btn-primary btn-block" style="margin-bottom: 0.8rem;">
                    Iniciar sesión para contratar
                </a>
                <a href="/register.php" class="btn-secondary btn-block">
                    Crear cuenta gratis
                </a>
            <?php endif; ?>

        <?php else: ?>
            <button disabled class="btn-primary btn-block"
                    style="opacity: 0.5; cursor: not-allowed;">
                No disponible
            </button>
        <?php endif; ?>

        <hr style="margin: 1.5rem 0; border-color: var(--color-borde);">

        <!-- Info adicional -->
        <div style="font-size: 0.85rem; color: var(--color-secundario); display: flex; flex-direction: column; gap: 0.5rem;">
            <div>🔒 Pago 100% seguro</div>
            <div>⚡ Activación inmediata</div>
            <div>📞 Soporte técnico incluido</div>
            <div>🔄 Cancela cuando quieras</div>
        </div>
    </div>
</div>

<!-- Otros productos de la misma categoría -->
<?php
$stmt2 = $conn->prepare("SELECT p.*, c.nombre AS categoria 
                         FROM productos p 
                         JOIN categorias c ON p.id_categoria = c.id_categoria 
                         WHERE p.id_categoria = ? AND p.id_producto != ? 
                         LIMIT 3");
$stmt2->bind_param("ii", $prod['id_categoria'], $id);
$stmt2->execute();
$relacionados = $stmt2->get_result();

if ($relacionados->num_rows > 0):
?>
<div style="margin-top: 3rem;">
    <h2 style="margin-bottom: 1.5rem;">Otros productos en <?= htmlspecialchars($prod['categoria']) ?></h2>
    <div class="productos-grid">
        <?php while ($rel = $relacionados->fetch_assoc()): ?>
        <div class="producto-card">
            <div class="producto-categoria"><?= htmlspecialchars($rel['categoria']) ?></div>
            <h3><?= htmlspecialchars($rel['nombre']) ?></h3>
            <p><?= htmlspecialchars($rel['descripcion']) ?></p>
            <div class="producto-precio">
                <?= number_format($rel['precio'], 2) ?> €<span style="font-size:0.9rem; font-weight:400; color:var(--color-secundario)">/mes</span>
            </div>
            <a href="/producto.php?id=<?= $rel['id_producto'] ?>" class="btn-secondary">
                Ver detalles
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>