<?php
/**
 * Renderiza un carrusel de banners
 * 
 * @param int $carrusel_id ID del carrusel
 * @param string $titulo Título opcional del carrusel
 */
function renderizar_carrusel_banners($carrusel_id, $titulo = '') {
    // Obtener información del carrusel
    $carrusel = obtener_carrusel($carrusel_id);
    
    // Si el carrusel no existe o no está activo, salir
    if (empty($carrusel) || $carrusel['tipo'] != CAROUSEL_BANNERS) {
        return;
    }
    
    // Obtener los banners asociados al carrusel
    $banners = obtener_banners_carrusel($carrusel_id);
    
    // Si no hay banners, salir
    if (empty($banners)) {
        return;
    }
    
    // Usar el título personalizado si está establecido, sino usar el del carrusel
    $titulo_mostrar = !empty($titulo) ? $titulo : $carrusel['nombre'];
    
    // Renderizar el carrusel
    $tipo = CAROUSEL_BANNERS;
    $items = $banners;
    include INCLUDES_PATH . '/components/carousel.php';
}