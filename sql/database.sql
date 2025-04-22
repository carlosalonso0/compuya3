-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS compuya_db;
USE compuya_db;

-- Tabla de marcas
CREATE TABLE IF NOT EXISTS marcas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria_padre_id INT,
    imagen VARCHAR(255),
    slug VARCHAR(150) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_padre_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    sku VARCHAR(50) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    precio_oferta DECIMAL(10, 2) DEFAULT 0,
    en_oferta TINYINT(1) DEFAULT 0,
    stock INT DEFAULT 0,
    descripcion TEXT,
    marca_id INT,
    categoria_id INT,
    modelo VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE SET NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    UNIQUE KEY (slug),
    UNIQUE KEY (sku)
);

-- Tabla de especificaciones de producto
CREATE TABLE IF NOT EXISTS especificaciones_producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    nombre_especificacion VARCHAR(100) NOT NULL,
    valor_especificacion TEXT NOT NULL,
    orden INT DEFAULT 0,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de imágenes de producto
CREATE TABLE IF NOT EXISTS imagenes_producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo_imagen VARCHAR(50) DEFAULT 'principal', -- principal, tarjeta, thumbnail
    ruta_imagen VARCHAR(255) NOT NULL,
    orden INT DEFAULT 0,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de carruseles
CREATE TABLE IF NOT EXISTS carruseles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- banner o producto
    tipo_contenido VARCHAR(50) NOT NULL, -- manual o categoria
    categoria_id INT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Tabla de banners
CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    imagen VARCHAR(255) NOT NULL,
    url_destino VARCHAR(255),
    tipo_carrusel VARCHAR(50) DEFAULT 'banner',
    activo TINYINT(1) DEFAULT 1,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de relación entre carruseles y banners
CREATE TABLE IF NOT EXISTS carrusel_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carrusel_id INT NOT NULL,
    banner_id INT NOT NULL,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (carrusel_id) REFERENCES carruseles(id) ON DELETE CASCADE,
    FOREIGN KEY (banner_id) REFERENCES banners(id) ON DELETE CASCADE
);

-- Tabla de relación entre carruseles y productos
CREATE TABLE IF NOT EXISTS carrusel_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carrusel_id INT NOT NULL,
    producto_id INT NOT NULL,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (carrusel_id) REFERENCES carruseles(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de ofertas especiales (secciones 10 y 11)
CREATE TABLE IF NOT EXISTS ofertas_especiales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    posicion INT NOT NULL, -- 10 u 11
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Insertar datos iniciales para los carruseles predefinidos
INSERT INTO carruseles (id, nombre, tipo, tipo_contenido, categoria_id, activo)
VALUES 
(1, 'Banner Principal', 'banner', 'manual', NULL, 1),
(2, 'Banner Izquierda', 'banner', 'manual', NULL, 1),
(3, 'Banner Centro', 'banner', 'manual', NULL, 1),
(4, 'Banner Superior Derecha', 'banner', 'manual', NULL, 1),
(5, 'Banner Inferior Derecha', 'banner', 'manual', NULL, 1),
(6, 'Ofertas Destacadas', 'producto', 'manual', NULL, 1),
(7, 'Banner Inferior', 'banner', 'manual', NULL, 1),
(8, 'Tarjetas Gráficas', 'producto', 'categoria', 1, 1),
(9, 'Procesadores', 'producto', 'categoria', 2, 1);

-- Insertar categorías predefinidas
INSERT INTO categorias (id, nombre, descripcion, categoria_padre_id, slug, activo)
VALUES
(1, 'Tarjetas Gráficas', 'Tarjetas gráficas para gaming y diseño', NULL, 'tarjetas-graficas', 1),
(2, 'Procesadores', 'Procesadores de última generación', NULL, 'procesadores', 1),
(3, 'Cases', 'Gabinetes para PC', NULL, 'cases', 1),
(4, 'Laptops', 'Laptops para todos los usos', NULL, 'laptops', 1),
(5, 'PC Gamers', 'Computadoras para gaming', NULL, 'pc-gamers', 1),
(6, 'Impresoras', 'Impresoras y multifuncionales', NULL, 'impresoras', 1),
(7, 'Placas Madre', 'Placas base para diferentes procesadores', NULL, 'placas-madre', 1),
(8, 'Monitores', 'Monitores LED, IPS y más', NULL, 'monitores', 1);

-- Insertar marcas predefinidas
INSERT INTO marcas (id, nombre, descripcion, activo)
VALUES
(1, 'NVIDIA', 'Fabricante de tarjetas gráficas', 1),
(2, 'Intel', 'Fabricante de procesadores', 1),
(3, 'Corsair', 'Fabricante de componentes y periféricos', 1),
(4, 'ASUS', 'Fabricante de hardware y electrónica', 1),
(5, 'AMD', 'Fabricante de procesadores y tarjetas gráficas', 1),
(6, 'HP', 'Fabricante de computadoras e impresoras', 1),
(7, 'MSI', 'Fabricante de hardware y computadoras', 1),
(8, 'LG', 'Fabricante de electrónica y monitores', 1);