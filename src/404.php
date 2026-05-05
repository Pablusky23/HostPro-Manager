<?php
require_once 'includes/auth.php';
http_response_code(404);
require_once 'includes/header.php';
?>

<div style="
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 5rem 2rem;
    min-height: 50vh;
">

    <!-- Número de error -->
    <div style="
        font-size: 8rem;
        font-weight: 900;
        color: var(--color-borde);
        line-height: 1;
        margin-bottom: 0.5rem;
        user-select: none;
    ">404</div>

    <!-- Icono -->
    <div style="font-size: 3rem; margin-bottom: 1.5rem;">🔌</div>

    <!-- Título -->
    <h1 style="font-size: 1.8rem; margin-bottom: 0.8rem; color: var(--color-texto);">
        Página no encontrada
    </h1>

    <!-- Descripción -->
    <p style="
        color: var(--color-secundario);
        font-size: 1rem;
        max-width: 420px;
        line-height: 1.7;
        margin-bottom: 2.5rem;
    ">
        La dirección que has introducido no existe o ha sido eliminada.
        Comprueba que la URL sea correcta o vuelve al inicio.
    </p>

    <!-- Botones -->
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
        <a href="/index.php" class="btn-primary">
            ← Volver al inicio
        </a>
        <a href="/productos.php" class="btn-secondary">
            Ver servicios
        </a>
        <?php if (estaLogueado()): ?>
            <a href="/pedidos.php" class="btn-secondary">
                Mis pedidos
            </a>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>