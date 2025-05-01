<?php
/**
 * Funciones para el carrusel de marcas
 */

/**
 * Obtener todas las marcas activas
 */
function obtener_todas_marcas() {
    $sql = "SELECT * FROM marcas WHERE activo = 1 ORDER BY nombre ASC";
    return get_rows($sql);
}

/**
 * Función para renderizar el carrusel de marcas
 */
function renderizar_carrusel_marcas() {
    // Obtener todas las marcas activas
    $marcas = obtener_todas_marcas();
    
    // Si no hay marcas, no mostrar nada
    if (empty($marcas)) {
        return;
    }
    
    // HTML para el carrusel
    ?>
    <div class="brands-section">
        <div class="container">
            <h3 class="brands-title">Nuestras Marcas</h3>
            
            <div class="brands-carousel">
                <div class="brands-container" id="brands-container">
                    <?php foreach ($marcas as $marca): ?>
                        <div class="brand-item">
                            <div class="brand-card">
                                <?php
                                // Construir la URL de la imagen de la marca
                                $imagen_url = !empty($marca['imagen']) 
                                    ? SITE_URL . '/uploads/marcas/' . $marca['imagen'] 
                                    : SITE_IMG_URL . '/brands/' . strtolower(str_replace(' ', '-', $marca['nombre'])) . '.png';
                                
                                // Verificar si la imagen existe, sino usar una imagen por defecto
                                if (!file_exists(str_replace(SITE_URL, BASE_PATH, $imagen_url))) {
                                    $imagen_url = SITE_IMG_URL . '/no-brand.png';
                                }
                                ?>
                                <img src="<?php echo $imagen_url; ?>" alt="<?php echo htmlspecialchars($marca['nombre']); ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="brands-controls">
                    <button class="brands-control prev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="brands-control next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initBrandsCarousel();
    });
    
    function initBrandsCarousel() {
        const container = document.getElementById('brands-container');
        if (!container) return;
        
        const prevBtn = container.parentElement.querySelector('.brands-control.prev');
        const nextBtn = container.parentElement.querySelector('.brands-control.next');
        
        if (!prevBtn || !nextBtn) return;
        
        // Calcular el ancho de un brand-item
        const items = container.querySelectorAll('.brand-item');
        if (!items.length) return;
        
        const itemWidth = items[0].offsetWidth;
        const visibleItems = Math.floor(container.offsetWidth / itemWidth);
        
        // Calcular el desplazamiento
        const scrollAmount = itemWidth;
        
        // Botón siguiente
        nextBtn.addEventListener('click', function() {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
        
        // Botón anterior
        prevBtn.addEventListener('click', function() {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        
        // Auto rotación
        let autoScrollInterval;
        
        function startAutoScroll() {
            autoScrollInterval = setInterval(() => {
                // Si llegamos al final, volver al inicio
                if (container.scrollLeft + container.offsetWidth >= container.scrollWidth) {
                    container.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                }
            }, 5000); // Cada 5 segundos
        }
        
        function stopAutoScroll() {
            clearInterval(autoScrollInterval);
        }
        
        // Iniciar auto rotación
        startAutoScroll();
        
        // Detener auto rotación al interactuar
        container.addEventListener('mouseenter', stopAutoScroll);
        container.addEventListener('mouseleave', startAutoScroll);
        prevBtn.addEventListener('click', () => {
            stopAutoScroll();
            setTimeout(startAutoScroll, 3000);
        });
        nextBtn.addEventListener('click', () => {
            stopAutoScroll();
            setTimeout(startAutoScroll, 3000);
        });
    }
    </script>
    <?php
}
?>