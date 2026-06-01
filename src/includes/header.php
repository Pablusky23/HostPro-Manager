<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostPro Manager</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="/src/favicon.ico">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <a href="/index.php">🖥️ HostPro Manager</a>
    </div>
    <ul class="nav-links">
        <li><a href="/index.php">Inicio</a></li>
        <li><a href="/productos.php">Servicios</a></li>
        <li><a href="/contacto.php">Contacto</a></li>

        <?php if (estaLogueado()): ?>
            <li><a href="/carrito.php">🛒 Carrito</a></li>
            <li><a href="/pedidos.php">Mis Pedidos</a></li>
            <li><a href="/perfil.php">👤 <?= htmlspecialchars($_SESSION['nombre']) ?></a></li>
            <?php if (esAdmin()): ?>
                <li><a href="/admin/index.php" class="btn-admin">⚙️ Admin</a></li>
            <?php endif; ?>
            <li><a href="/logout.php">Salir</a></li>
        <?php else: ?>
            <li><a href="/login.php">Iniciar sesión</a></li>
            <li><a href="/register.php" class="btn-primary">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</nav>

<main class="container">