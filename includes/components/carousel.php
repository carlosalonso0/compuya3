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

// Si es el banner principal, no se muestra dentro de un div.carousel
if ($es_banner_principal) {
    // Mostrar el primer banner (solo se muestra uno a la vez)
    if (!empty($items[0])) {
        $banner = $items[0];
        include INCLUDES_PATH . '/components/banner.php';
    }
    
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
        <?php 
        // Mostrar el primer banner (solo se muestra uno a la vez)
        if (!empty($items[0])) {
            $banner = $items[0];
            include INCLUDES_PATH . '/components/banner.php';
        }
        
        // Mostrar puntos de navegación si hay más de un banner
        if (count($items) > 1):
        ?>
            <div class="carousel-dots">
                <?php foreach ($items as $key => $item): ?>
                    <div class="dot <?php echo ($key == 0) ? 'active' : ''; ?>" data-slide="<?php echo $key; ?>"></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Carrusel de productos -->
        <div class="product-carousel" id="product-carousel-<?php echo $carrusel_id; ?>">
            <?php 
            // Si es el carrusel 6 (ofertas especiales)
            if ($carrusel_id == 6) {
                // Mostrar el primer producto con el formato de oferta especial
                if (!empty($items[0])) {
                    $producto = $items[0];
                    include INCLUDES_PATH . '/components/special-offer.php';
                }
            } else {
                // Para carruseles 8 y 9, mostrar múltiples productos a la vez
                foreach ($items as $producto) {
                    include INCLUDES_PATH . '/components/product-card.php';
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

<?php if ($carrusel_id == 6 || ($tipo == CAROUSEL_BANNERS && count($items) > 1)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrusel
    initCarousel('<?php echo $carousel_id; ?>', <?php echo $tipo == CAROUSEL_BANNERS ? 'true' : 'false'; ?>);
});
</script>
<?php endif; ?>