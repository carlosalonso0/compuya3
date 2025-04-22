<?php
/**
 * Renderiza un carrusel de productos
 * 
 * @param int $carrusel_id ID del carrusel
 * @param string $titulo Título opcional del carrusel
 */
function renderizar_carrusel_productos($carrusel_id, $titulo = '') {
    global $CARRUSELES_ESPECIALES;
    
    // Obtener información del carrusel
    $carrusel = obtener_carrusel($carrusel_id);
    
    // Si el carrusel no existe o no está activo, salir
    if (empty($carrusel) || $carrusel['tipo'] != CAROUSEL_PRODUCTOS) {
        return;
    }
    
    // Obtener los productos según el tipo de contenido (manual o por categoría)
    $productos = [];
    
    // Para todos los carruseles, usar el mismo método de obtención
    if ($carrusel['tipo_contenido'] == CAROUSEL_TIPO_MANUAL) {
        $productos = obtener_productos_carrusel_manual($carrusel_id);
    } elseif ($carrusel['tipo_contenido'] == CAROUSEL_TIPO_CATEGORIA && !empty($carrusel['categoria_id'])) {
        $productos = obtener_productos_categoria($carrusel['categoria_id']);
    }
    
    // Si no hay productos, salir
    if (empty($productos)) {
        return;
    }
    
    // Usar el título personalizado si está establecido, sino usar el del carrusel
    $titulo_mostrar = !empty($titulo) ? $titulo : $carrusel['nombre'];
    
    // Si es un carrusel especial (6, 8, 9), usar su configuración específica
    $items_mostrar = 1; // Por defecto, mostrar 1 producto a la vez
    if (isset($CARRUSELES_ESPECIALES[$carrusel_id])) {
        $items_mostrar = $CARRUSELES_ESPECIALES[$carrusel_id]['items'];
        
        // Para el carrusel 6, usar el nombre predefinido si está establecido
        if ($carrusel_id == 6 && !empty($CARRUSELES_ESPECIALES[$carrusel_id]['nombre'])) {
            $titulo_mostrar = $CARRUSELES_ESPECIALES[$carrusel_id]['nombre'];
        }
    }
    
    // Renderizar el carrusel
    $tipo = CAROUSEL_PRODUCTOS;
    $items = $productos;
    include INCLUDES_PATH . '/components/carousel.php';
}