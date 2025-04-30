<?php
/**
 * Página de error 404
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/functions_categories.php';

// Título de la página
$page_title = "Página no encontrada (Error 404)";

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<div class="error-page">
    <div class="new-container">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h1>404</h1>
            <h2>Página no encontrada</h2>
            <p>Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
            <div class="error-actions">
                <a href="<?php echo SITE_URL; ?>" class="btn-return">
                    <i class="fas fa-home"></i> Volver al inicio
                </a>
                <a href="<?php echo SITE_URL; ?>/contacto.php" class="btn-contact">
                    <i class="fas fa-envelope"></i> Contactar soporte
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Cargar el footer del sitio
include_once INCLUDES_PATH . '/footer.php';
?>