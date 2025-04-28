<?php
/**
 * Componente de banner
 * 
 * @param array $banner Datos del banner
 * @param int $carrusel_id ID del carrusel
 */

// Si no se ha pasado un banner, salir
if (empty($banner) || empty($carrusel_id)) {
    return;
}

// Definir tiempo de transición (en milisegundos)
$CAROUSEL_TRANSITION_TIME = 500;

// Preparar los datos del banner
$id = $banner['id'];
$titulo = isset($banner['titulo']) ? $banner['titulo'] : '';
$url_destino = isset($banner['url_destino']) ? $banner['url_destino'] : '';
$imagen_url = get_imagen_banner($id, $carrusel_id);

// Obtener posición de la imagen (si existe en la base de datos)
// Por defecto, centramos la imagen
$posicion_img = isset($banner['posicion_img']) ? $banner['posicion_img'] : 'center center';

// Determinar si es el banner principal (carrusel 1)
$es_principal = ($carrusel_id == 1);
$clase_principal = $es_principal ? 'carousel-banner-1' : '';

// Añadir estilos específicos para carrusel 7
if ($carrusel_id == 7) {
    $style = "
        background-image: url('{$imagen_url}');
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        width: 100%;
        height: 100%;
        transition: opacity {$CAROUSEL_TRANSITION_TIME}ms ease;
    ";
} else {
    $style = "
        background-image: url('{$imagen_url}');
        background-size: contain;
        background-position: {$posicion_img};
        background-repeat: no-repeat;
        width: 100%;
        height: 100%;
        transition: opacity {$CAROUSEL_TRANSITION_TIME}ms ease;
    ";
}
?>

<div class="carousel-banner <?php echo $clase_principal; ?>" style="<?php echo $style; ?>">
    <?php if (!empty($titulo)): ?>
        <div class="banner-content">
            <h2 class="banner-title"><?php echo htmlspecialchars($titulo); ?></h2>
            <?php if (!empty($url_destino)): ?>
                <a href="<?php echo $url_destino; ?>" class="banner-button">Ver más</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>