-- =============================================
-- BASE DE DATOS: hostpro_db
-- =============================================

USE hostpro_db;

#Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL DEFAULT 'cliente',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

#Tabla de categorías
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL
);

#Tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    id_categoria INT,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

#Tabla de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(50) DEFAULT 'pendiente',
    id_usuario INT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Tabla de detalle de pedido
CREATE TABLE IF NOT EXISTS detalle_pedido (
    id_detalle INT PRIMARY KEY AUTO_INCREMENT,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    id_pedido INT,
    id_producto INT,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS pagos (
    id_pago INT PRIMARY KEY AUTO_INCREMENT,
    metodo_pago VARCHAR(50),
    estado VARCHAR(50) DEFAULT 'pendiente',
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_pedido INT,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
);

-- Tabla de mensajes (contacto)
CREATE TABLE IF NOT EXISTS mensajes (
    id_mensaje INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- DATOS DE EJEMPLO
-- =============================================

-- Categorías
INSERT INTO categorias (nombre) VALUES 
('Hosting Compartido'),
('VPS'),
('Servidores Dedicados'),
('Dominios');

-- Administrador por defecto
-- Contraseña: Admin1234 (hasheada con bcrypt)
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@hostpro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Productos de ejemplo
INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria) VALUES
('Hosting Básico', 'Hosting compartido 10GB SSD, 1 dominio, SSL gratis', 4.99, 100, 1),
('Hosting Pro', 'Hosting compartido 50GB SSD, 5 dominios, SSL gratis', 9.99, 100, 1),
('Hosting Business', 'Hosting compartido ilimitado, SSL gratis, copia de seguridad diaria', 19.99, 100, 1),
('VPS Básico', 'VPS 2 vCPU, 2GB RAM, 40GB SSD', 15.00, 50, 2),
('VPS Pro', 'VPS 4 vCPU, 8GB RAM, 100GB SSD', 35.00, 50, 2),
('Servidor Dedicado', 'Servidor dedicado 8 cores, 32GB RAM, 500GB SSD', 99.00, 10, 3),
('Dominio .com', 'Registro de dominio .com por 1 año', 9.99, 999, 4),
('Dominio .es', 'Registro de dominio .es por 1 año', 7.99, 999, 4);