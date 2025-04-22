<?php
/**
 * Renderiza una tarjeta de oferta especial (posiciones 10 y 11)
 * 
 * @param int $posicion Posición de la oferta (10 u 11)
 */
function renderizar_oferta_especial($posicion) {
    // Obtener la oferta especial por posición
    $oferta_con_producto = obtener_ofertas_especiales($posicion);
    
    // Si no hay oferta activa para esta posición, salir
    if (empty($oferta_con_producto)) {
        return;
    }
    
    // Separar los datos
    $oferta = [
        'id' => $oferta_con_producto['id'],
        'posicion' => $oferta_con_producto['posicion'],
        'fecha_inicio' => $oferta_con_producto['fecha_inicio'],
        'fecha_fin' => $oferta_con_producto['fecha_fin']
    ];
    
    $producto = [
        'id' => $oferta_con_producto['producto_id'],
        'nombre' => $oferta_con_producto['nombre'],
        'precio' => $oferta_con_producto['precio'],
        'precio_oferta' => $oferta_con_producto['precio_oferta'],
        'en_oferta' => $oferta_con_producto['en_oferta'],
        'stock' => $oferta_con_producto['stock'],
        'marca_id' => $oferta_con_producto['marca_id']
    ];
    
    // Renderizar la tarjeta de oferta
    include INCLUDES_PATH . '/components/offer-card.php';
}

/**
 * Renderiza la sección de ofertas especiales (posiciones 10 y 11)
 */
function renderizar_seccion_ofertas_especiales() {
    echo '<div class="block">';
    echo '<h3 class="section-title">Ofertas por tiempo limitado</h3>';
    echo '<div class="flex-container">';
    
    // Columna izquierda - Oferta 10
    echo '<div class="column-left">';
    renderizar_oferta_especial(10);
    echo '</div>';
    
    // Columna derecha - Oferta 11
    echo '<div class="column-right">';
    renderizar_oferta_especial(11);
    echo '</div>';
    
    echo '</div>'; // .flex-container
    echo '</div>'; // .block
}