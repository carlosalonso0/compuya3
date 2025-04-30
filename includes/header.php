<?php
require_once dirname(__FILE__) . '/../config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/functions_categories.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compu Ya</title>
    
    <!-- Estilos base -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/utilities.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/header.css">
    
    <!-- Componentes -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/components/product-card.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/components/offer-card.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/components/special-offer.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/components/banner.css">
    
    <!-- Carrusel -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/carousel/carousel-base.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/carousel/carousel-controls.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/carousel/carousel-dots.css">
    
    <!-- Secciones -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/sections/section-banner-1.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/sections/section-banners-2345.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/sections/section-products-6789.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/sections/section-offers-1011.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/sections/section-header.css">

    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/search.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/product.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/footer.css"> 

    
    <link rel="icon" type="image/png" href="<?php echo SITE_IMG_URL; ?>/logo/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="new-site-header">
        <!-- NIVEL SUPERIOR: Logo, Buscador, Carrito -->
        <div class="new-top-header">
            <div class="new-container">
                <div class="new-logo">
                    <a href="<?php echo SITE_URL; ?>">
                        <img src="<?php echo SITE_IMG_URL; ?>/logo/logo.png" alt="Compu Ya">
                    </a>
                </div>
                
                <div class="new-search-bar">
                    <form action="<?php echo SITE_URL; ?>/buscar.php" method="get">
                        <input type="text" name="q" placeholder="Buscar productos en CompuYa...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <div class="new-cart-icon">
                    <a href="<?php echo SITE_URL; ?>/carrito.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="new-cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- MENÚ PRINCIPAL -->
        <nav class="new-main-menu">
            <div class="new-container">
                <button class="new-mobile-menu-toggle"><i class="fas fa-bars"></i></button>
                
                <ul class="new-main-menu-items">
                    <li class="new-has-submenu">
                        <a href="#">Categorías</a>
                        <div class="new-submenu">
                            <?php
                            // Obtener categorías de la base de datos
                            $categorias = get_parent_categories();
                            if (!empty($categorias)) {
                                foreach ($categorias as $categoria) {
                                    echo '<a href="' . SITE_URL . '/categoria.php?id=' . $categoria['id'] . '">' . htmlspecialchars($categoria['nombre']) . '</a>';
                                }
                            } else {
                                // Categorías de fallback
                                echo '<a href="' . SITE_URL . '/categoria.php?id=1">Tarjetas Gráficas</a>';
                                echo '<a href="' . SITE_URL . '/categoria.php?id=2">Procesadores</a>';
                                echo '<a href="' . SITE_URL . '/categoria.php?id=3">Cases</a>';
                                echo '<a href="' . SITE_URL . '/categoria.php?id=4">Laptops</a>';
                                echo '<a href="' . SITE_URL . '/categoria.php?id=5">PC Gaming</a>';
                            }
                            ?>
                        </div>
                    </li>
                    <li><a href="<?php echo SITE_URL; ?>/ofertas.php" class="new-highlight">Ofertas</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/nosotros.php">Nosotros</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contacto.php">Contacto</a></li>
                </ul>
            </div>
        </nav>
        
        <!-- CATEGORÍAS DESTACADAS -->
        <div class="new-featured-categories">
            <div class="new-container">
                <ul class="new-category-list">
                    <li><a href="<?php echo SITE_URL; ?>/nuevo-ingreso.php">Nuevo Ingreso</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pcs-completas.php">PC's Completas</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/laptops.php">Laptops</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/nuestros-locales.php">Nuestros Locales</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/envios-provincia.php">Envíos A Provincia</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/delivery-lima.php">Delivery Lima</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <!-- Conservamos el header original para mantener el layout hasta que terminemos la transición -->
    <header class="site-header" style="display: none;">
        <div class="top-bar">
            <div class="container">
                <div class="contact-info">
                    <a href="tel:+51999999999"><i class="fas fa-phone"></i> +51 999 999 999</a>
                    <a href="mailto:info@miecommerce.com"><i class="fas fa-envelope"></i> info@miecommerce.com</a>
                </div>
                <div class="user-actions">
                    <a href="<?php echo SITE_URL; ?>/mi-cuenta.php">Iniciar Sesión</a>
                    <a href="#">Mis Pedidos</a>
                    <a href="<?php echo SITE_URL; ?>/carrito.php"><i class="fas fa-shopping-cart"></i> 0 Carrito</a>
                </div>
            </div>
        </div>
        <div class="first-nav">
            <div class="container">
                <ul class="first-nav-menu">
                    <li><a href="#" data-active="true">Categorias</a></li>
                    <li><a href="#">Ofertas</a></li>
                    <li><a href="#">Campañas</a></li>
                    <li><a href="#">Distribuidores</a></li>
                    <li><a href="#">¿Que Vas A Jugar?</a></li>
                    <li><a href="#">Contactanos</a></li>
                </ul>
            </div>
        </div>
        <div class="main-header">
            <div class="container">
                <div class="logo">
                    <a href="<?php echo SITE_URL; ?>">
                        <img src="<?php echo SITE_IMG_URL; ?>/logo/logo.png" alt="Compu Ya">
                    </a>
                </div>
                <div class="search-form">
                    <form action="<?php echo SITE_URL; ?>/buscar.php" method="get">
                        <input type="text" name="q" placeholder="Buscar productos en Compu Ya...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="<?php echo SITE_URL; ?>/nuevo-ingreso.php">Nuevo Ingreso</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pcs-completas.php">PC's Completas</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/laptops.php">Laptops</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/nuestros-locales.php">Nuestros Locales</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/modalidades-pago.php">Modalidades De Pago</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/envios-provincia.php">Envíos A Provincia</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/delivery-lima.php">Delivery Lima</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/chatear.php">Chatear Con Nosotros</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="main-content">