<?php
// Incluir archivos necesarios si no están ya incluidos
require_once dirname(__FILE__) . '/../config.php';
require_once INCLUDES_PATH . '/functions.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/components.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_IMG_URL; ?>/logo/favicon.png">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados para carruseles -->
    <style>
    /* Reset completo para los carruseles */
    .product-carousel {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 10px !important; /* Reducir el gap de 15px a 10px */
        width: 100% !important;
        padding: 10px !important; /* Agregar pequeño padding */
        box-sizing: border-box !important;
    }
    
    /* Garantiza que cada wrapper ocupe exactamente 1/4 del contenedor */
    .product-card-wrapper {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    /* Asegura que ambos carruseles tengan un tamaño idéntico */
    #carousel-8 .product-carousel,
    #carousel-9 .product-carousel {
        grid-template-columns: repeat(4, 1fr) !important;
    }
    
    /* Cada tarjeta mantiene la altura de 380px pero con ajustes internos */
    .product-card {
        width: 100% !important;
        box-sizing: border-box !important;
        height: 360px !important; /* Ligeramente reducido para dar margen */
        display: flex !important;
        flex-direction: column !important;
    }
    
    /* Ajusta los elementos internos */
    .product-image-container {
        height: 110px !important;
        padding: 5px !important;
    }
    
    .product-info {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        padding: 8px !important;
    }
    
    .product-name {
        height: 36px !important;
        overflow: hidden !important;
        margin-bottom: 5px !important;
    }
    
    .btn-add-cart {
        margin-top: auto !important;
    }
    
    /* Unificar tamaños de texto y elementos en ambos carruseles */
    #carousel-8 .product-name,
    #carousel-9 .product-name {
        font-size: 13px !important;
        line-height: 1.2 !important;
        font-weight: bold !important;
    }

    #carousel-8 .current-price,
    #carousel-9 .current-price {
        color: #e53935 !important;
        font-size: 16px !important;
        font-weight: bold !important;
    }

    #carousel-8 .original-price,
    #carousel-9 .original-price {
        font-size: 13px !important;
    }

    #carousel-8 .stock-info,
    #carousel-9 .stock-info {
        font-size: 11px !important;
        margin-bottom: 5px !important;
    }

    #carousel-8 .btn-add-cart,
    #carousel-9 .btn-add-cart {
        padding: 6px 10px !important;
        font-size: 13px !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
    }

    #carousel-8 .promo-tag,
    #carousel-9 .promo-tag {
        font-size: 11px !important;
        padding: 3px 8px !important;
        margin-bottom: 5px !important;
    }

    /* Estructura de la sección con anchos fijos */
    .product-section-6789 {
        display: grid;
        grid-template-columns: 450px auto;
        gap: 15px;
        margin-bottom: 30px;
    }

    .product-left {
        width: 450px !important;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .product-right {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    /* TODOS los carruseles mantienen la misma altura */
    .carousel-6,
    .carousel-7,
    .carousel-8,
    .carousel-9 {
        height: 380px !important; /* Todos con la misma altura */
        width: 100% !important;
        overflow: hidden !important;
        position: relative !important;
    }

    /* Carrusel 6 - Sin scroll */
    .carousel-6 {
        width: 450px !important;
    }

    #carousel-6,
    .carousel-6 .carousel {
        width: 450px !important;
        height: 100% !important;
        overflow: hidden !important;
    }

    .special-offer-container {
        width: 450px !important;
        height: 100% !important;
        overflow: hidden !important;
    }

    .special-offer-card {
        width: 450px !important;
        height: 100% !important; /* Mantener altura completa */
        display: flex !important;
        flex-direction: column !important;
        border: 1px solid #e8e8e8 !important;
        background-color: #ffffff !important;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08) !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    /* Ajustes para que todo quepa sin scroll */
    .offer-image {
        height: 45% !important;
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background-color: #f8f8f8 !important;
        padding: 10px !important;
        box-sizing: border-box !important;
    }

    .offer-image img {
        max-height: 100% !important;
        max-width: 100% !important;
        object-fit: contain !important;
    }

    .offer-content {
        height: 55% !important;
        width: 100% !important;
        padding: 10px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }

    /* Ajustes de texto para que quepa todo */
    .offer-title {
        font-size: 15px !important;
        margin-bottom: 3px !important;
    }

    .offer-description {
        font-size: 12px !important;
        margin-bottom: 3px !important;
        line-height: 1.2 !important;
        overflow: hidden !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
    }

    .countdown-container {
        padding: 3px !important;
        margin-bottom: 3px !important;
    }

    .countdown {
        font-size: 11px !important;
    }

    .price-container {
        margin-bottom: 5px !important;
    }

    .current-price {
        font-size: 16px !important;
    }

    .original-price {
        font-size: 13px !important;
    }

    .btn-add-cart {
        padding: 6px 10px !important;
        font-size: 12px !important;
    }

    /* Carrusel 7 - Alineación correcta */
    .carousel-7 {
        width: 450px !important;
        margin: 0 !important;
        padding: 0 !important;
        position: relative !important;
        top: 0 !important;
    }

    #carousel-7 {
        width: 450px !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* SOLUCIÓN AL DESBORDAMIENTO DE CARRUSELES 8 Y 9 */
    #carousel-8,
    #carousel-9 {
        height: 100% !important;
        width: 100% !important;
        overflow: hidden !important;
        position: relative !important;
    }

    /* Evitar desbordamiento de los product-carousel */
    #carousel-8 .product-carousel,
    #carousel-9 .product-carousel {
        height: 380px !important; /* Mismo tamaño que el contenedor */
        overflow: hidden !important;
        position: relative !important;
        box-sizing: border-box !important;
    }

    /* Ajustar las product-cards para que no excedan el contenedor */
    .carousel-8 .product-card,
    .carousel-9 .product-card {
        height: 360px !important; /* Ligeramente más pequeño que el contenedor */
        margin-bottom: 0 !important;
        box-sizing: border-box !important;
    }

    /* Asegurar que las tarjetas no tengan márgenes que causen desbordamiento */
    .carousel-8 .product-card-wrapper,
    .carousel-9 .product-card-wrapper {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }

    /* Asegurar alineación perfecta */
    .carousel-6,
    .carousel-8 {
        margin-bottom: 15px !important;
    }

    .carousel-7,
    .carousel-9 {
        margin-bottom: 0 !important;
        margin-top: 0 !important;
    }

    /* Eliminar cualquier scroll en todo el carrusel 6 */
    .carousel-6 *,
    #carousel-6 *,
    .special-offer-container *,
    .special-offer-card * {
        overflow: hidden !important;
    }

    /* Debug - agregar bordes temporales para ver el problema */
    .carousel-7 {
        border: 2px solid red !important;
    }
    
    .carousel-9 {
        border: 2px solid blue !important;
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .product-section-6789 {
            grid-template-columns: 400px auto;
        }
        
        .product-left,
        .carousel-6,
        .carousel-7,
        #carousel-6,
        #carousel-7,
        .special-offer-container,
        .special-offer-card {
            width: 400px !important;
        }
    }

    @media (max-width: 1200px) {
        .product-section-6789 {
            grid-template-columns: 350px auto;
        }
        
        .product-left,
        .carousel-6,
        .carousel-7,
        #carousel-6,
        #carousel-7,
        .special-offer-container,
        .special-offer-card {
            width: 350px !important;
        }
    }

    @media (max-width: 992px) {
        .product-section-6789 {
            grid-template-columns: 1fr;
        }
        
        .product-left,
        .carousel-6,
        .carousel-7,
        #carousel-6,
        #carousel-7,
        .special-offer-container,
        .special-offer-card {
            width: 100% !important;
        }
        
        .product-left,
        .product-right {
            width: 100% !important;
        }
    }
    </style>
</head>
<body>
    <!-- Header y menú principal -->
    <header class="site-header">
        <div class="top-bar">
            <div class="container">
                <div class="contact-info">
                    <a href="tel:+51999999999"><i class="fas fa-phone"></i> +51 999 999 999</a>
                    <a href="mailto:info@miecommerce.com"><i class="fas fa-envelope"></i> info@miecommerce.com</a>
                </div>
                <div class="user-actions">
                    <a href="<?php echo SITE_URL; ?>/mi-cuenta.php"><i class="fas fa-user"></i> Mi Cuenta</a>
                    <a href="<?php echo SITE_URL; ?>/carrito.php"><i class="fas fa-shopping-cart"></i> Carrito</a>
                </div>
            </div>
        </div>
        <div class="main-header">
            <div class="container">
                <div class="logo">
                    <a href="<?php echo SITE_URL; ?>">
                        <img src="<?php echo SITE_IMG_URL; ?>/logo/logo.png" alt="<?php echo SITE_NAME; ?>">
                    </a>
                </div>
                <div class="search-form">
                    <form action="<?php echo SITE_URL; ?>/buscar.php" method="get">
                        <input type="text" name="q" placeholder="Buscar productos...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/categorias.php">Categorías</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/ofertas.php">Ofertas</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/nuevos.php">Nuevos Productos</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contacto.php">Contacto</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Contenido principal -->
    <main class="main-content">