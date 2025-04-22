<?php
// Incluir archivos necesarios si no están ya incluidos
require_once dirname(__FILE__) . '/../../config.php';
if (!function_exists('formatear_precio')) {
    require_once INCLUDES_PATH . '/functions.php';
}

// Definir título de página si no está establecido
if (!isset($page_title)) {
    $page_title = 'Panel de Administración';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | Admin <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_IMG_URL; ?>/logo/favicon.png">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar de navegación -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <a href="<?php echo SITE_URL; ?>/admin/index.php">
                    <img src="<?php echo SITE_IMG_URL; ?>/logo/logo-admin.png" alt="<?php echo SITE_NAME; ?> Admin">
                </a>
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="<?php echo SITE_URL; ?>/admin/index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/admin/carousels.php" <?php echo basename($_SERVER['PHP_SELF']) == 'carousels.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-sliders-h"></i> Carruseles
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/admin/banners.php" <?php echo basename($_SERVER['PHP_SELF']) == 'banners.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-image"></i> Banners
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/admin/products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-box"></i> Productos
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/admin/categories.php" <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-folder"></i> Categorías
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/admin/offers.php" <?php echo basename($_SERVER['PHP_SELF']) == 'offers.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-tags"></i> Ofertas
                    </a>
                </li>
                <li class="sidebar-divider"></li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/admin/settings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-cog"></i> Configuración
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/admin/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <p>© <?php echo date('Y'); ?> <?php echo SITE_NAME; ?></p>
            </div>
        </nav>
        
        <!-- Contenido principal -->
        <div class="admin-content">
            <header class="admin-header">
                <div class="header-left">
                    <h1><?php echo $page_title; ?></h1>
                </div>
                <div class="header-right">
                    <div class="admin-user">
                        <span>Admin</span>
                        <img src="<?php echo SITE_IMG_URL; ?>/site/admin-avatar.png" alt="Admin">
                    </div>
                </div>
            </header>
            
            <main class="admin-main">