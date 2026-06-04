-- =====================================================================
--  Proyecto: Gestión de un catálogo de productos
--  Base de datos: catalogo
--  Curso DAW 2025/2026
-- ---------------------------------------------------------------------
--  Para cargar todo desde cero:
--    mysql -u root -p < database/catalogo.sql
--  (o copia y pega este script en phpMyAdmin)
-- =====================================================================

DROP DATABASE IF EXISTS catalogo;
CREATE DATABASE catalogo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE catalogo;

-- ---------------------------------------------------------------------
--  Tabla: usuario  (credenciales de acceso a la zona privada)
-- ---------------------------------------------------------------------
CREATE TABLE usuario (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nombre    VARCHAR(50)  NOT NULL,
    apellido1 VARCHAR(50)  NOT NULL,
    apellido2 VARCHAR(50)  DEFAULT NULL,
    email     VARCHAR(100) NOT NULL UNIQUE,
    login     VARCHAR(50)  NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL   -- guardamos el HASH, nunca la contraseña en claro
);

-- ---------------------------------------------------------------------
--  Tabla: fabricante
-- ---------------------------------------------------------------------
CREATE TABLE fabricante (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- ---------------------------------------------------------------------
--  Tabla: producto
--  id_fabricante es CLAVE FORÁNEA que apunta a fabricante(id)
-- ---------------------------------------------------------------------
CREATE TABLE producto (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100)   NOT NULL,
    descripcion   TEXT           DEFAULT NULL,
    precio        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    imagen        VARCHAR(255)   DEFAULT NULL,   -- nombre del fichero guardado en /uploads
    id_fabricante INT            DEFAULT NULL,
    CONSTRAINT fk_producto_fabricante
        FOREIGN KEY (id_fabricante) REFERENCES fabricante(id)
        ON DELETE SET NULL    -- si se borra el fabricante, el producto queda sin fabricante
        ON UPDATE CASCADE
);

-- =====================================================================
--  DATOS DE EJEMPLO
-- =====================================================================

-- Usuario de prueba.
--   login:    admin
--   password: admin123
-- El campo password contiene el hash bcrypt de "admin123".
INSERT INTO usuario (nombre, apellido1, apellido2, email, login, password) VALUES
('Admin', 'Del', 'Sistema', 'admin@catalogo.test', 'admin',
 '$2y$10$wcBb2jzfzC835hJCBzHh0.5MfUPvAJZKt8zuOn7RA1p87FVS2r4ny');

INSERT INTO fabricante (nombre) VALUES
('Asus'),
('Lenovo'),
('Apple'),
('Samsung'),
('Logitech');

INSERT INTO producto (nombre, descripcion, precio, imagen, id_fabricante) VALUES
('Portátil ZenBook 14',  'Portátil ultraligero con pantalla OLED de 14 pulgadas y 16 GB de RAM.', 1099.00, 'zenbook.jpg',  1),
('ThinkPad X1 Carbon',   'Portátil empresarial con teclado retroiluminado y gran autonomía.',    1499.99, 'thinkpad.jpg', 2),
('MacBook Air M3',       'Portátil con chip M3, silencioso y muy eficiente.',                    1299.00, 'macbook.jpg',  3),
('Monitor 27" 4K',       'Monitor profesional 4K con cobertura de color sRGB del 99%.',           379.50, 'monitor.jpg',  4),
('Ratón MX Master 3S',   'Ratón inalámbrico ergonómico con scroll de alta precisión.',             99.99, 'raton.jpg',    5),
('Teclado Mecánico TKL', 'Teclado mecánico compacto con switches rojos y retroiluminación.',       79.95, 'teclado.jpg',  5),
('Galaxy Tab S9',        'Tablet Android con pantalla AMOLED y lápiz incluido.',                  749.00, 'tablet.jpg',   4),
('Webcam Full HD',       'Cámara web 1080p con micrófono integrado y enfoque automático.',         59.90, 'webcam.jpg',   5);
