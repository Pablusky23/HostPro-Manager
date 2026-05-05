<?php
// Inicia la sesión (necesario para guardar el usuario logueado)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Comprueba si el usuario está logueado
function estaLogueado() {
    return isset($_SESSION['id_usuario']);
}

// Comprueba si el usuario logueado es administrador
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

// Redirige si no está logueado (para páginas protegidas)
function requiereLogin() {
    if (!estaLogueado()) {
        header("Location: /login.php");
        exit();
    }
}

// Redirige si no es administrador (para el panel de admin)
function requiereAdmin() {
    if (!esAdmin()) {
        header("Location: /index.php");
        exit();
    }
}
?>