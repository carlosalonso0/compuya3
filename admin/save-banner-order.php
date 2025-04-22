<?php
// admin/save-banner-order.php
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrusel_id = isset($_POST['carrusel_id']) ? (int)$_POST['carrusel_id'] : 0;
    $banner_ids = isset($_POST['banner_ids']) ? json_decode($_POST['banner_ids']) : [];
    
    if ($carrusel_id && !empty($banner_ids)) {
        try {
            // Comenzar transacción
            db()->autocommit(FALSE);
            
            // Actualizar orden para cada banner
            foreach ($banner_ids as $orden => $banner_id) {
                $sql = "UPDATE carrusel_banners SET orden = ? WHERE carrusel_id = ? AND banner_id = ?";
                update($sql, [$orden + 1, $carrusel_id, $banner_id]);
            }
            
            // Confirmar cambios
            db()->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            db()->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}