<?php
/**
 * Componente de carrusel genérico
 * 
 * @param int $carrusel_id ID del carrusel
 * @param string $tipo Tipo de carrusel (banner o producto)
 * @param array $items Elementos a mostrar en el carrusel
 * @param string $titulo Título opcional para el carrusel
 * @param int $items_mostrar Cantidad de elementos a mostrar a la vez
 */

// Si no se han pasado los parámetros requeridos, salir
if (empty($carrusel_id) || empty($tipo) || empty($items)) {
    return;
}

// Generar ID único para este carrusel
$carousel_id = "carousel-" . $carrusel_id;

// Determinar si es el carrusel 1 (banner principal a ancho completo)
$es_banner_principal = ($carrusel_id == 1 && $tipo == CAROUSEL_BANNERS);

// Si es el banner principal, se maneja de forma especial
if ($es_banner_principal) {
    // Crear contenedor para el banner principal
    echo '<div class="banner-container">';
    
    // Mostrar todos los banners (solo uno será visible a la vez)
    foreach ($items as $key => $banner) {
        $style = ($key == 0) ? 'display: block; opacity: 1;' : 'display: none; opacity: 0;';
        echo '<div style="' . $style . '" class="banner-slide" data-index="' . $key . '">';
        include INCLUDES_PATH . '/components/banner.php';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Mostrar puntos de navegación si hay más de un banner
    if (count($items) > 1) {
        echo '<div class="carousel-dots">';
        foreach ($items as $key => $item) {
            $active = ($key == 0) ? 'active' : '';
            echo '<div class="dot ' . $active . '" data-slide="' . $key . '"></div>';
        }
        echo '</div>';
    }
    return;
}

// Para el resto de carruseles...
?>

<?php if (!empty($titulo)): ?>
    <h3 class="section-title"><?php echo htmlspecialchars($titulo); ?></h3>
<?php endif; ?>

<div class="carousel" id="<?php echo $carousel_id; ?>">
    <?php if ($tipo == CAROUSEL_BANNERS): ?>
        <!-- Carrusel de banners -->
        <div class="banner-container">
            <?php foreach ($items as $key => $banner): ?>
                <?php
                // El primer banner estará visible, los demás ocultos
                $style = ($key == 0) ? 'display: block; opacity: 1;' : 'display: none; opacity: 0;';
                ?>
                <div class="banner-slide" data-index="<?php echo $key; ?>" style="<?php echo $style; ?>">
                    <?php include INCLUDES_PATH . '/components/banner.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Mostrar puntos de navegación si hay más de un banner -->
        <?php if (count($items) > 1): ?>
            <div class="carousel-dots">
                <?php foreach ($items as $key => $item): ?>
                    <div class="dot <?php echo ($key == 0) ? 'active' : ''; ?>" data-slide="<?php echo $key; ?>"></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php else: ?>
            <!-- Carrusel de productos -->
            <div class="product-carousel <?php echo (count($items) > 4) ? 'full-carousel' : 'few-products'; ?>" id="product-carousel-<?php echo $carrusel_id; ?>">
                <?php 
                // Si es el carrusel 6 (ofertas especiales)
                if ($carrusel_id == 6) {
                    // Mostrar el primer producto con el formato de oferta especial
                    if (!empty($items[0])) {
                        $producto = $items[0];
                        include INCLUDES_PATH . '/components/special-offer.php';
                    }
                } else {
                    // Para carruseles 8 y 9, mostrar productos con el mismo tamaño fijo
                    foreach ($items as $producto) {
                        echo '<div class="product-card-wrapper">';
                        include INCLUDES_PATH . '/components/product-card.php';
                        echo '</div>';
                    }
                    
                    // Si hay menos de 4 productos, agregar espacios vacíos para mantener la estructura
                    $total_items = count($items);
                    if ($total_items < 4) {
                        for ($i = 0; $i < (4 - $total_items); $i++) {
                            echo '<div class="product-card-wrapper empty-space"></div>';
                        }
                    }
                }
                ?>
            </div>
        <?php endif; ?>
    
    <?php if (count($items) > 1): ?>
        <!-- Controles de navegación -->
        <div class="carousel-controls">
            <button class="carousel-control prev" data-carousel="<?php echo $carousel_id; ?>">&#10094;</button>
            <button class="carousel-control next" data-carousel="<?php echo $carousel_id; ?>">&#10095;</button>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrusel
    initCarousel('<?php echo $carousel_id; ?>', <?php echo $tipo == CAROUSEL_BANNERS ? 'true' : 'false'; ?>);
});
</script>