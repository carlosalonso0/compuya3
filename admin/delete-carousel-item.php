<?php
/**
 * Eliminar un elemento (banner o producto) de un carrusel
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener parámetros
$type = isset($_POST['type']) ? $_POST['type'] : '';
$carousel_id = isset($_POST['carousel_id']) ? (int)$_POST['carousel_id'] : 0;
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

if (empty($type) || empty($carousel_id) || empty($item_id)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
    exit;
}

$success = false;
$message = '';

if ($type === 'banner') {
    // Eliminar asociación entre carrusel y banner
    $sql = "DELETE FROM carrusel_banners WHERE carrusel_id = ? AND banner_id = ?";
    $result = update($sql, [$carousel_id, $item_id]);
    
    if ($result !== false) {
        $success = true;
        $message = 'Banner eliminado del carrusel';
    } else {
        $message = 'Error al eliminar el banner';
    }
} elseif ($type === 'producto') {
    // Eliminar asociación entre carrusel y producto
    $sql = "DELETE FROM carrusel_productos WHERE carrusel_id = ? AND producto_id = ?";
    $result = update($sql, [$carousel_id, $item_id]);
    
    if ($result !== false) {
        $success = true;
        $message = 'Producto eliminado del carrusel';
    } else {
        $message = 'Error al eliminar el producto';
    }
} else {
    $message = 'Tipo no válido';
}

// Devolver respuesta JSON
echo json_encode(['success' => $success, 'message' => $message]);