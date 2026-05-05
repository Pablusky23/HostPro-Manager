<?php
// Datos de conexión (deben coincidir con el docker-compose.yml)
define('DB_HOST', 'db');           // Nombre del contenedor de MySQL
define('DB_USER', 'hostpro_user');
define('DB_PASS', 'hostpro_pass');
define('DB_NAME', 'hostpro_db');

// Crear la conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Comprobar si hay error
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Codificación de caracteres
$conn->set_charset("utf8mb4");
?>