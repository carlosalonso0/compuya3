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
function get_imagen_banner($banner_id, $carrusel_id) {
    // Comprobar múltiples extensiones posibles
    $extensiones = ['png', 'jpg', 'jpeg', 'webp'];
    $ruta = '';
    
    foreach ($extensiones as $ext) {
        $archivo = BANNERS_URL . '/' . $carrusel_id . '/banner_' . $carrusel_id . '_' . $banner_id . '.' . $ext;
        $ruta_fisica = str_replace(SITE_URL, BASE_PATH, $archivo);
        
        if (file_exists($ruta_fisica)) {
            return $archivo;
        }
    }
    
    // Si no encontramos el archivo con ninguna extensión, usar imagen por defecto
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
function generar_sku($tipo, $marca, $serie, $modelo, $extra) {
    $formatos = [
        'tarjeta_grafica' => 'TG-[MARCA2L]-[SERIE]-[MEMORIA]-[ID3D]',
        'procesador' => 'CPU-[MARCA1L]-[SERIE]-[MODELO]-[ID3D]',
        'case' => 'CASE-[MARCA2L]-[MODELO]-[COLOR2L]-[ID3D]',
        'laptop' => 'LT-[MARCA2L]-[SERIE2L]-[TAMAÑO]-[ID3D]',
        'pc_gamer' => 'PC-[NIVEL]-[CPU2L]-[GPU2L]-[RAM]-[ID3D]',
        'impresora' => 'IMP-[MARCA2L]-[SERIE]-[TIPO2L]-[ID3D]',
        'placa_madre' => 'MB-[MARCA2L]-[SERIE2L]-[CHIPSET]-[ID3D]',
        'monitor' => 'MON-[MARCA2L]-[SERIE2L]-[TAMAÑO]-[ID3D]'
    ];
    
    // Generar ID único de 3 caracteres alfanuméricos
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $id3d = '';
    for ($i = 0; $i < 3; $i++) {
        $id3d .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    // Reemplazar marcadores según formato
    $sku = $formatos[$tipo];
    $sku = str_replace('[MARCA2L]', substr($marca, 0, 2), $sku);
    $sku = str_replace('[MARCA1L]', substr($marca, 0, 1), $sku);
    $sku = str_replace('[SERIE]', $serie, $sku);
    $sku = str_replace('[SERIE2L]', substr($serie, 0, 2), $sku);
    $sku = str_replace('[MODELO]', $modelo, $sku);
    $sku = str_replace('[ID3D]', $id3d, $sku);
    
    // Reemplazar campos específicos por tipo
    switch ($tipo) {
        case 'tarjeta_grafica':
            $sku = str_replace('[MEMORIA]', $extra['memoria'], $sku);
            break;
        case 'case':
            $sku = str_replace('[COLOR2L]', substr($extra['color'], 0, 2), $sku);
            break;
        case 'laptop':
            $sku = str_replace('[TAMAÑO]', $extra['tamano'], $sku);
            break;
        case 'pc_gamer':
            $sku = str_replace('[NIVEL]', $extra['nivel'], $sku);
            $sku = str_replace('[CPU2L]', $extra['cpu'], $sku);
            $sku = str_replace('[GPU2L]', $extra['gpu'], $sku);
            $sku = str_replace('[RAM]', $extra['ram'], $sku);
            break;
        case 'impresora':
            $sku = str_replace('[TIPO2L]', substr($extra['tipo'], 0, 2), $sku);
            break;
        case 'placa_madre':
            $sku = str_replace('[CHIPSET]', $extra['chipset'], $sku);
            break;
        case 'monitor':
            $sku = str_replace('[TAMAÑO]', $extra['tamano'], $sku);
            break;
    }
    
    return strtoupper($sku);
}
/**
 * Obtener información de una marca por su ID
 */
function obtener_marca($id) {
    $sql = "SELECT * FROM marcas WHERE id = ?";
    return get_row($sql, [$id]);
}