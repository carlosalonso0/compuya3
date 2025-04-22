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

// Preparar los datos del banner
$id = $banner['id'];
$titulo = $banner['titulo'];
$url_destino = $banner['url_destino'];
$imagen_url = get_imagen_banner($id, $carrusel_id);

// Determinar si es el banner principal (carrusel 1)
$es_principal = ($carrusel_id == 1);
$clase_principal = $es_principal ? 'carousel-banner-1' : '';
?>

<div class="carousel-banner <?php echo $clase_principal; ?>" style="background-image: url('<?php echo $imagen_url; ?>');">
    <?php if (!empty($titulo)): ?>
        <div class="banner-content">
            <h2 class="banner-title"><?php echo htmlspecialchars($titulo); ?></h2>
            <?php if (!empty($url_destino)): ?>
                <a href="<?php echo $url_destino; ?>" class="banner-button">Ver más</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>