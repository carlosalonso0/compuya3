<?php
/**
 * Función para depurar problemas con imágenes de banner
 * Añade esto al principio de tu archivo includes/content/banner-carousel.php 
 * o inclúyelo en algún script temporal para diagnóstico
 */
function debug_banner_images($carrusel_id) {
    // Obtener los banners asociados al carrusel
    $banners = obtener_banners_carrusel($carrusel_id);
    
    echo "<div style='background-color: #f5f5f5; border: 1px solid #ddd; padding: 15px; margin: 15px 0; font-family: monospace;'>";
    echo "<h3>Debug de imágenes para carrusel #$carrusel_id</h3>";
    
    if (empty($banners)) {
        echo "<p>No hay banners asociados a este carrusel.</p>";
    } else {
        echo "<p>Banners encontrados: " . count($banners) . "</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>
                <th style='padding: 8px;'>ID Banner</th>
                <th style='padding: 8px;'>Título</th>
                <th style='padding: 8px;'>Nombre de archivo en DB</th>
                <th style='padding: 8px;'>Ruta generada</th>
                <th style='padding: 8px;'>¿Archivo existe?</th>
                <th style='padding: 8px;'>Vista previa</th>
              </tr>";
        
        foreach ($banners as $banner) {
            $id = $banner['id'];
            $titulo = $banner['titulo'] ?: '(Sin título)';
            $img_db = $banner['imagen'] ?: '(No especificado)';
            
            // Obtener ruta según función actual
            $ruta_generada = get_imagen_banner($id, $carrusel_id);
            
            // Verificar si el archivo existe físicamente
            $ruta_fisica = str_replace(SITE_URL, BASE_PATH, $ruta_generada);
            $existe = file_exists($ruta_fisica) ? 'SÍ' : 'NO';
            $existe_class = $existe === 'SÍ' ? 'color: green;' : 'color: red; font-weight: bold;';
            
            echo "<tr>";
            echo "<td style='padding: 8px;'>$id</td>";
            echo "<td style='padding: 8px;'>$titulo</td>";
            echo "<td style='padding: 8px;'>$img_db</td>";
            echo "<td style='padding: 8px;'>$ruta_generada</td>";
            echo "<td style='padding: 8px; $existe_class'>$existe</td>";
            echo "<td style='padding: 8px;'><img src='$ruta_generada' style='max-width: 100px; max-height: 60px;'></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "</div>";
}

// Comentar o eliminar después de resolver el problema
// debug_banner_images(1); // Para carrusel 1
// debug_banner_images(2); // Para carrusel 2
?>

<?php
/**
 * Renderiza un carrusel de banners
 * 
 * @param int $carrusel_id ID del carrusel
 * @param string $titulo Título opcional del carrusel
 */
function renderizar_carrusel_banners($carrusel_id, $titulo = '') {
    // Obtener información del carrusel
    $carrusel = obtener_carrusel($carrusel_id);
    
    // Si el carrusel no existe o no está activo, salir
    if (empty($carrusel) || $carrusel['tipo'] != CAROUSEL_BANNERS) {
        return;
    }
    
    // Obtener los banners asociados al carrusel
    $banners = obtener_banners_carrusel($carrusel_id);
    
    // Si no hay banners, salir
    if (empty($banners)) {
        return;
    }
    
    // Usar el título personalizado si está establecido, sino usar el del carrusel
    $titulo_mostrar = !empty($titulo) ? $titulo : $carrusel['nombre'];
    
    // Renderizar el carrusel
    $tipo = CAROUSEL_BANNERS;
    $items = $banners;
    include INCLUDES_PATH . '/components/carousel.php';
}