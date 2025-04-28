<?php
/**
 * Componente de oferta destacada (carrusel 6) - Versión vertical
 * 
 * @param array $producto Datos del producto
 */

// Si no se ha pasado un producto, salir
if (empty($producto)) {
    return;
}

// Obtener la URL de la imagen
$imagen_url = get_imagen_producto($producto['id'], 'principal');

// Preparar los datos del producto
$id = $producto['id'];
$nombre = $producto['nombre'];
$precio = $producto['precio'];
$precio_oferta = $producto['precio_oferta'];
$en_oferta = $producto['en_oferta'];
$stock = $producto['stock'];
$url_producto = SITE_URL . '/producto.php?id=' . $id;

// Determinar si es una oferta válida
if (!$en_oferta || $precio_oferta <= 0 || $precio_oferta >= $precio) {
    return;
}

// Calcular porcentaje de descuento
$porcentaje_descuento = round(($precio - $precio_oferta) / $precio * 100);

// Formatear precios
$precio_formateado = formatear_precio($precio);
$precio_oferta_formateado = formatear_precio($precio_oferta);

// Calcular tiempo restante (simulado para esta demostración)
// En producción, esto vendría de la tabla ofertas_especiales
$horas_restantes = rand(1, 72);
$fecha_fin = date('Y-m-d H:i:s', strtotime("+{$horas_restantes} hours"));

// Convertir fecha a componentes para countdown
$fecha_obj = new DateTime($fecha_fin);
$now = new DateTime();
$diff = $fecha_obj->diff($now);

$dias = $diff->d;
$horas = $diff->h;
$minutos = $diff->i;
$segundos = $diff->s;
?>

<!-- Agregar estilos inline para asegurar que se vea correctamente -->


<div class="special-offer-container">
    <div class="special-offer-card">
        <div class="offer-image">
            <img src="<?php echo $imagen_url; ?>" alt="<?php echo htmlspecialchars($nombre); ?>">
        </div>
        <div class="offer-content">
            <span class="offer-badge">-<?php echo $porcentaje_descuento; ?>%</span>
            <h3 class="offer-title">SUPER OFERTA</h3>
            <p class="offer-description"><?php echo htmlspecialchars($nombre); ?></p>
            
            <div class="countdown-container">
                <p>Termina en:</p>
                <div class="countdown">
                    <div class="countdown-item">
                        <span class="countdown-number"><?php echo str_pad($dias, 2, '0', STR_PAD_LEFT); ?></span>
                        <span class="countdown-label">Días</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number"><?php echo str_pad($horas, 2, '0', STR_PAD_LEFT); ?></span>
                        <span class="countdown-label">Horas</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number"><?php echo str_pad($minutos, 2, '0', STR_PAD_LEFT); ?></span>
                        <span class="countdown-label">Min</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number"><?php echo str_pad($segundos, 2, '0', STR_PAD_LEFT); ?></span>
                        <span class="countdown-label">Seg</span>
                    </div>
                </div>
            </div>
            
            <div class="price-container">
                <span class="current-price"><?php echo $precio_oferta_formateado; ?></span>
                <span class="original-price"><?php echo $precio_formateado; ?></span>
            </div>
            
            <?php if ($stock > 0): ?>
                <a href="<?php echo SITE_URL; ?>/carrito.php?action=add&id=<?php echo $id; ?>" class="btn-add-cart">
                    <i class="fas fa-shopping-cart"></i> Añadir al carrito
                </a>
            <?php else: ?>
                <button class="btn-add-cart disabled" disabled>
                    <i class="fas fa-times"></i> Agotado
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Contador de tiempo para esta oferta
document.addEventListener('DOMContentLoaded', function() {
    // Convertir a timestamp en milisegundos
    const fechaFin = new Date('<?php echo $fecha_fin; ?>').getTime();
    
    // Actualizar cada segundo
    const countdownTimer = setInterval(function() {
        const now = new Date().getTime();
        const distance = fechaFin - now;
        
        // Calcular días, horas, minutos y segundos
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        // Mostrar resultado con formato de dos dígitos
        const containers = document.querySelectorAll('.special-offer-container');
        containers.forEach(container => {
            if (container.querySelector('.countdown-item:nth-child(1) .countdown-number')) {
                container.querySelector('.countdown-item:nth-child(1) .countdown-number').textContent = 
                    days.toString().padStart(2, '0');
                container.querySelector('.countdown-item:nth-child(2) .countdown-number').textContent = 
                    hours.toString().padStart(2, '0');
                container.querySelector('.countdown-item:nth-child(3) .countdown-number').textContent = 
                    minutes.toString().padStart(2, '0');
                container.querySelector('.countdown-item:nth-child(4) .countdown-number').textContent = 
                    seconds.toString().padStart(2, '0');
            }
        });
        
        // Si el contador llega a cero
        if (distance < 0) {
            clearInterval(countdownTimer);
            document.querySelectorAll('.countdown').forEach(el => {
                el.innerHTML = "OFERTA FINALIZADA";
            });
        }
    }, 1000);
});
</script>