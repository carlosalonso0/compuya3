<?php
/**
 * Funciones generales del sitio
 */

// Cargar configuración global
require_once dirname(__FILE__) . '/../config.php';

// Cargar la conexión a la base de datos
require_once INCLUDES_PATH . '/db.php';

/**
 * Obtener información de un carrusel por su ID
 */
function obtener_carrusel($id) {
    $sql = "SELECT * FROM carruseles WHERE id = ? AND activo = 1";
    return get_row($sql, [$id]);
}

/**
 * Obtener banners asociados a un carrusel
 */
function obtener_banners_carrusel($carrusel_id) {
    $sql = "SELECT b.* 
            FROM banners b
            INNER JOIN carrusel_banners cb ON b.id = cb.banner_id 
            WHERE cb.carrusel_id = ? AND b.activo = 1 AND cb.activo = 1
            ORDER BY cb.orden ASC";
    return get_rows($sql, [$carrusel_id]);
}

/**
 * Obtener productos asociados a un carrusel (modo manual)
 */
function obtener_productos_carrusel_manual($carrusel_id) {
    $sql = "SELECT p.* 
            FROM productos p
            INNER JOIN carrusel_productos cp ON p.id = cp.producto_id 
            WHERE cp.carrusel_id = ? AND p.activo = 1 AND cp.activo = 1
            ORDER BY cp.orden ASC";
    return get_rows($sql, [$carrusel_id]);
}

/**
 * Obtener productos por categoría (para carruseles por categoría)
 */
function obtener_productos_categoria($categoria_id) {
    $sql = "SELECT * FROM productos WHERE categoria_id = ? AND activo = 1";
    return get_rows($sql, [$categoria_id]);
}

/**
 * Obtener productos en oferta (para carrusel 6)
 */
function obtener_productos_oferta() {
    $sql = "SELECT * FROM productos WHERE en_oferta = 1 AND activo = 1 AND precio_oferta > 0";
    return get_rows($sql);
}

/**
 * Obtener ofertas especiales por posición (10, 11)
 */
function obtener_ofertas_especiales($posicion) {
    $fecha_actual = date('Y-m-d H:i:s');
    $sql = "SELECT oe.*, p.*
            FROM ofertas_especiales oe
            INNER JOIN productos p ON oe.producto_id = p.id
            WHERE oe.posicion = ? 
            AND oe.activo = 1 
            AND oe.fecha_inicio <= ? 
            AND oe.fecha_fin >= ?
            AND p.activo = 1";
    return get_row($sql, [$posicion, $fecha_actual, $fecha_actual]);
}

/**
 * Calcular tiempo restante de una oferta en formato legible
 */
function calcular_tiempo_restante($fecha_fin) {
    $ahora = new DateTime();
    $fin = new DateTime($fecha_fin);
    $diff = $ahora->diff($fin);
    
    if ($diff->invert) {
        return "Finalizada";
    }
    
    $dias = $diff->days;
    $horas = $diff->h;
    $minutos = $diff->i;
    
    if ($dias > 1) {
        return "{$dias}d {$horas}h {$minutos}m";
    } elseif ($dias == 1) {
        return "1d {$horas}h {$minutos}m";
    } elseif ($horas > 0) {
        return "{$horas}h {$minutos}m";
    } else {
        return "{$minutos}m";
    }
}

/**
 * Formatear precio en soles
 */
function formatear_precio($precio) {
    return 'S/. ' . number_format($precio, 2, '.', ',');
}

/**
 * Truncar texto a una longitud específica
 */
function truncar_texto($texto, $longitud = 100) {
    if (strlen($texto) <= $longitud) {
        return $texto;
    }
    
    return substr($texto, 0, $longitud) . '...';
}

/**
 * Generar URL de imagen para producto
 */
function get_imagen_producto($producto_id, $tipo = 'tarjeta', $numero = 1) {
    $ruta = PRODUCTOS_URL . '/' . $tipo . '/' . $producto_id . '/' . $producto_id . '_' . $tipo . '_' . $numero . '.webp';
    
    // Si no existe, devolver imagen por defecto
    if (!file_exists(str_replace(SITE_URL, BASE_PATH, $ruta))) {
        return SITE_IMG_URL . '/no-image.webp';
    }
    
    return $ruta;
}

/**
 * Generar URL de imagen para banner
 */
// En functions.php - Modificar esta función
/**
 * Generar URL de imagen para banner
 */
function get_imagen_banner($banner_id, $carrusel_id) {
    // Esta es la parte importante: debemos obtener la imagen del banner desde la base de datos
    // en lugar de intentar construir el nombre del archivo
    $sql = "SELECT imagen FROM banners WHERE id = ?";
    $banner = get_row($sql, [$banner_id]);
    
    if ($banner && !empty($banner['imagen'])) {
        // Si el banner tiene una imagen en la base de datos, usamos esa
        $archivo = BANNERS_URL . '/' . $carrusel_id . '/' . $banner['imagen'];
        $ruta_fisica = str_replace(SITE_URL, BASE_PATH, $archivo);
        
        if (file_exists($ruta_fisica)) {
            return $archivo;
        }
    }
    
    // Plan B: intentar con nombres de archivo estándar
    $extensiones = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    
    foreach ($extensiones as $ext) {
        // Probamos primero con el formato que vemos en tu screenshot: banner_NNN
        $archivo = BANNERS_URL . '/' . $carrusel_id . '/banner_' . $banner_id . '.' . $ext;
        $ruta_fisica = str_replace(SITE_URL, BASE_PATH, $archivo);
        
        if (file_exists($ruta_fisica)) {
            return $archivo;
        }
        
        // Probamos con el formato que intentamos antes: banner_carrusel_banner
        $archivo = BANNERS_URL . '/' . $carrusel_id . '/banner_' . $carrusel_id . '_' . $banner_id . '.' . $ext;
        $ruta_fisica = str_replace(SITE_URL, BASE_PATH, $archivo);
        
        if (file_exists($ruta_fisica)) {
            return $archivo;
        }
    }
    
    // Si llegamos aquí, no encontramos la imagen, devolvemos la imagen por defecto
    error_log("No se pudo encontrar imagen para banner ID: $banner_id, carrusel ID: $carrusel_id");
    return SITE_IMG_URL . '/no-banner.webp';
}
/**
 * Obtener la URL actual
 */
function get_current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Redirigir a otra URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Verificar si una ruta existe, si no, crearla
 */
function verificar_crear_ruta($ruta) {
    if (!file_exists($ruta)) {
        mkdir($ruta, 0755, true);
    }
}

/**
 * Generar SKU según formato predefinido
 */

/**
 * Obtener información de una marca por su ID
 */
function obtener_marca($id) {
    $sql = "SELECT * FROM marcas WHERE id = ?";
    return get_row($sql, [$id]);
}