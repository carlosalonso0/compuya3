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
    $items_mostrar = 4; // Por defecto, mostrar 4 productos a la vez
    if (isset($CARRUSELES_ESPECIALES[$carrusel_id])) {
        $items_mostrar = $CARRUSELES_ESPECIALES[$carrusel_id]['items'];
        
        // Para el carrusel 6, usar el nombre predefinido si está establecido
        if ($carrusel_id == 6 && !empty($CARRUSELES_ESPECIALES[$carrusel_id]['nombre'])) {
            $titulo_mostrar = $CARRUSELES_ESPECIALES[$carrusel_id]['nombre'];
        }
    }
    
    // Limitar a 4 productos por página visibles
    $total_productos = count($productos);
    
    // Determinar clase de carrusel según cantidad de productos
    $carousel_class = ($total_productos > 4) ? 'full-carousel' : 'few-products items-' . $total_productos;
    
    // Mostrar título del carrusel si está definido
    if (!empty($titulo_mostrar)) {
        echo '<h3 class="section-title">' . htmlspecialchars($titulo_mostrar) . '</h3>';
    }
    
    // Iniciar el contenedor del carrusel
    echo '<div class="carousel" id="carousel-' . $carrusel_id . '">';
    
    // Contenedor de productos
    echo '<div class="product-carousel ' . $carousel_class . '" id="product-carousel-' . $carrusel_id . '">';
    
    // Si es el carrusel 6 (ofertas especiales)
    if ($carrusel_id == 6) {
        // Mostrar el primer producto con el formato de oferta especial
        if (!empty($productos[0])) {
            $producto = $productos[0];
            include INCLUDES_PATH . '/components/special-offer.php';
        }
    } else {
        // Para carruseles 8 y 9, mostrar productos con el mismo tamaño fijo
        foreach ($productos as $producto) {
            echo '<div class="product-card-wrapper">';
            include INCLUDES_PATH . '/components/product-card.php';
            echo '</div>';
        }
        
        // Si hay menos de 4 productos, agregar espacios vacíos para mantener la estructura
        if ($total_productos < 4) {
            for ($i = 0; $i < (4 - $total_productos); $i++) {
                echo '<div class="product-card-wrapper empty-space"></div>';
            }
        }
    }
    
    echo '</div>'; // .product-carousel
    
    // Mostrar controles de navegación solo si hay más de 4 productos
    if ($total_productos > 4) {
        echo '<div class="carousel-controls">';
        echo '<button class="carousel-control prev" data-carousel="carousel-' . $carrusel_id . '">&#10094;</button>';
        echo '<button class="carousel-control next" data-carousel="carousel-' . $carrusel_id . '">&#10095;</button>';
        echo '</div>';
    }
    
    echo '</div>'; // .carousel
    
    // Inicializar el carrusel con JavaScript
    echo '<script>';
    echo 'document.addEventListener("DOMContentLoaded", function() {';
    echo '    initCarousel("carousel-' . $carrusel_id . '", false);';
    echo '});';
    echo '</script>';
}