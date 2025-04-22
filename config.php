<?php
/**
 * Configuración global del sitio
 */

// Información del sitio
define('SITE_NAME', 'Mi E-commerce');
define('SITE_URL', '/compuya');
define('ADMIN_EMAIL', 'admin@miecommerce.com');

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'compuya_db');
define('DB_CHARSET', 'utf8mb4');

// Rutas del sistema
define('BASE_PATH', dirname(__FILE__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('ADMIN_PATH', BASE_PATH . '/admin');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// Rutas de uploads
define('UPLOADS_URL', SITE_URL . '/uploads');
define('PRODUCTOS_URL', UPLOADS_URL . '/productos');
define('BANNERS_URL', UPLOADS_URL . '/banners');
define('CATEGORIAS_URL', UPLOADS_URL . '/categorias');
define('MARCAS_URL', UPLOADS_URL . '/marcas');
define('PROMOCIONES_URL', UPLOADS_URL . '/promociones');
define('SITE_IMG_URL', UPLOADS_URL . '/site');

// Configuración de carruseles
define('CAROUSEL_BANNERS', 'banner');
define('CAROUSEL_PRODUCTOS', 'producto');
define('CAROUSEL_TIPO_MANUAL', 'manual');
define('CAROUSEL_TIPO_CATEGORIA', 'categoria');

// Número de elementos por carrusel
define('CAROUSEL_ITEMS_6', 1); // 1 producto a la vez
define('CAROUSEL_ITEMS_8', 4); // 4 productos a la vez
define('CAROUSEL_ITEMS_9', 4); // 4 productos a la vez

// Carruseles especiales
$CARRUSELES_ESPECIALES = [
    6 => ['tipo' => CAROUSEL_PRODUCTOS, 'items' => CAROUSEL_ITEMS_6, 'nombre' => 'Ofertas Destacadas'],
    8 => ['tipo' => CAROUSEL_PRODUCTOS, 'items' => CAROUSEL_ITEMS_8],
    9 => ['tipo' => CAROUSEL_PRODUCTOS, 'items' => CAROUSEL_ITEMS_9]
];

// Configuración de debug
define('DEBUG_MODE', true);

// Configurar errores según modo
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Zona horaria
date_default_timezone_set('America/Lima');