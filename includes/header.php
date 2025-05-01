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
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/search.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/product.css">
    
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
                            <div class="new-submenu-item new-has-children">
                                <a href="#">Componentes</a>
                                <div class="new-submenu-children">
                                    <a href="<?php echo SITE_URL; ?>/categoria/cases">Cases</a>
                                    <a href="<?php echo SITE_URL; ?>/categoria/placas-madre">Placas Madre</a>
                                    <a href="<?php echo SITE_URL; ?>/categoria/procesadores">Procesadores</a>
                                    <a href="<?php echo SITE_URL; ?>/categoria/tarjetas-graficas">Tarjetas Gráficas</a>
                                </div>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/categoria/impresoras">Impresoras</a>
                            <a href="<?php echo SITE_URL; ?>/categoria/laptops">Laptops</a>
                            <a href="<?php echo SITE_URL; ?>/categoria/monitores">Monitores</a>
                            <a href="<?php echo SITE_URL; ?>/categoria/pc-gamers">PC Gamers</a>
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
    <main class="main-content">