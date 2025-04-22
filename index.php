<?php
/**
 * Página de inicio del sitio
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/content/banner-carousel.php';
require_once INCLUDES_PATH . '/content/product-carousel.php';
require_once INCLUDES_PATH . '/content/special-offers.php';

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<!-- Carrusel 1: Banner principal a ancho completo -->
<div class="banner-section-1">
    <div id="carousel-1" class="fixed-height-carousel">
        <?php renderizar_carrusel_banners(1); ?>
    </div>
</div>

<!-- Sección con carruseles 2-5 -->
<div class="container">
    <div class="banner-section-2345">
        <!-- Columna izquierda - Carrusel 2 -->
        <div class="banner-left">
            <div id="carousel-2" class="fixed-height-carousel">
                <?php renderizar_carrusel_banners(2); ?>
            </div>
        </div>
        
        <!-- Columna central - Carrusel 3 -->
        <div class="banner-center">
            <div id="carousel-3" class="fixed-height-carousel">   
                <?php renderizar_carrusel_banners(3); ?>
            </div>
        </div>
        
        <!-- Columna derecha - Carruseles 4 y 5 -->
        <div class="banner-right">
            <div id="carousel-4" class="fixed-height-carousel">
                <?php renderizar_carrusel_banners(4); ?>
            </div>
            <div id="carousel-5" class="fixed-height-carousel">
                <?php renderizar_carrusel_banners(5); ?>
            </div>
        </div>
    </div>

    <!-- Sección con carruseles 6-9 (Alineados) -->
    <div class="product-section-6789">
        <!-- Columna izquierda - Carruseles 6 y 7 -->
        <div class="product-left">
            <!-- Carrusel 6: Ofertas Destacadas (misma altura que 8) -->
            <div class="carousel-6">
                <div id="carousel-6">
                    <?php renderizar_carrusel_productos(6); ?>
                </div>
            </div>
            
            <!-- Carrusel 7: Banner (misma altura que 9) -->
            <div class="carousel-7">
                <div id="carousel-7" class="fixed-height-carousel">
                    <?php renderizar_carrusel_banners(7); ?>
                </div>
            </div>
        </div>
        
        <!-- Columna derecha - Carruseles 8 y 9 -->
        <div class="product-right">
            <!-- Carrusel 8: Productos (4 a la vez con bucle) -->
            <div class="carousel-8">
                <div id="carousel-8">
                    <?php renderizar_carrusel_productos(8); ?>
                </div>
            </div>
            
            <!-- Carrusel 9: Productos (4 a la vez con bucle) -->
            <div class="carousel-9">
                <div id="carousel-9">
                    <?php renderizar_carrusel_productos(9); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de ofertas estáticas 10-11 -->
    <div class="offers-section">
        <!-- Oferta izquierda - Posición 10 -->
        <div class="offer-left">
            <?php renderizar_oferta_especial(10); ?>
        </div>
        
        <!-- Oferta derecha - Posición 11 -->
        <div class="offer-right">
            <?php renderizar_oferta_especial(11); ?>
        </div>
    </div>
</div>

<?php
// Cargar el footer del sitio
include_once INCLUDES_PATH . '/footer.php';
?>