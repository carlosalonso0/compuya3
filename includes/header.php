<?php
require_once dirname(__FILE__) . '/../config.php';
require_once INCLUDES_PATH . '/functions.php';
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

    
    <link rel="icon" type="image/png" href="<?php echo SITE_IMG_URL; ?>/logo/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="site-header">
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