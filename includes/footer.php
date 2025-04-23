</main>
    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-columns">
                    <div class="footer-column">
                        <h3>Sobre Nosotros</h3>
                        <p>Somos una tienda especializada en productos tecnológicos con los mejores precios del mercado.</p>
                    </div>
                    <div class="footer-column">
                        <h3>Enlaces Rápidos</h3>
                        <ul>
                            <li><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/categorias.php">Categorías</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/ofertas.php">Ofertas</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/contacto.php">Contacto</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h3>Categorías Populares</h3>
                        <ul>
                            <li><a href="<?php echo SITE_URL; ?>/categoria.php?id=1">Tarjetas Gráficas</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/categoria.php?id=2">Procesadores</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/categoria.php?id=3">Laptops</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/categoria.php?id=4">PC's Gamer</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h3>Contáctanos</h3>
                        <p>
                            <i class="fas fa-map-marker-alt"></i> Av. Tecnológica 123, Lima<br>
                            <i class="fas fa-phone"></i> +51 999 999 999<br>
                            <i class="fas fa-envelope"></i> info@miecommerce.com
                        </p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
                <div class="payment-methods">
                    <img src="<?php echo SITE_IMG_URL; ?>/site/payment-methods.png" alt="Métodos de pago">
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/carousel.js"></script>
    
    <!-- Script para controlar carruseles de productos -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const carruseles = ['carousel-8', 'carousel-9'];
        
        carruseles.forEach(function(carouselId) {
            const carousel = document.getElementById(carouselId);
            if (!carousel) return;
            
            const container = carousel.querySelector('.product-carousel');
            if (!container) return;
            
            const productos = container.querySelectorAll('.product-card-wrapper');
            const totalProductos = productos.length;
            
            // Si hay 4 o menos productos, ocultar controles
            if (totalProductos <= 4) {
                const controles = carousel.querySelector('.carousel-controls');
                if (controles) controles.style.display = 'none';
                return;
            }
            
            // Ocultar productos después del 4to
            for (let i = 4; i < totalProductos; i++) {
                productos[i].style.display = 'none';
            }
            
            // Configurar botones
            const btnPrev = carousel.querySelector('.carousel-control.prev');
            const btnNext = carousel.querySelector('.carousel-control.next');
            
            if (!btnPrev || !btnNext) return;
            
            let posicion = 0;
            
            btnNext.addEventListener('click', function() {
                // Ocultar el producto actual
                productos[posicion].style.display = 'none';
                
                // Mostrar el siguiente producto
                const siguientePos = (posicion + 4) % totalProductos;
                productos[siguientePos].style.display = '';
                
                // Actualizar posición
                posicion = (posicion + 1) % totalProductos;
            });
            
            btnPrev.addEventListener('click', function() {
                // Calcular posición anterior
                const posAnterior = (posicion - 1 + totalProductos) % totalProductos;
                
                // Ocultar el último visible
                const ultimoVisible = (posicion + 3) % totalProductos;
                productos[ultimoVisible].style.display = 'none';
                
                // Mostrar el anterior
                productos[posAnterior].style.display = '';
                
                // Actualizar posición
                posicion = posAnterior;
            });
        });
    });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Forzar alineación de carruseles 7 y 9
    const carousel7 = document.querySelector('.carousel-7');
    const carousel9 = document.querySelector('.carousel-9');
    
    if (carousel7 && carousel9) {
        // Obtener la posición del carrusel 9
        const rect9 = carousel9.getBoundingClientRect();
        const rect7 = carousel7.getBoundingClientRect();
        
        // Calcular la diferencia
        const difference = rect9.top - rect7.top;
        
        // Ajustar la posición del carrusel 7
        if (difference !== 0) {
            carousel7.style.marginTop = difference + 'px';
        }
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Diagnóstico
    const carousel7 = document.querySelector('.carousel-7');
    const carousel9 = document.querySelector('.carousel-9');
    
    if (carousel7 && carousel9) {
        console.log('Carrusel 7:', carousel7.getBoundingClientRect());
        console.log('Carrusel 9:', carousel9.getBoundingClientRect());
        
        // Verificar estilos computados
        const style7 = window.getComputedStyle(carousel7);
        const style9 = window.getComputedStyle(carousel9);
        
        console.log('Margin-top carrusel 7:', style7.marginTop);
        console.log('Margin-top carrusel 9:', style9.marginTop);
        console.log('Padding-top carrusel 7:', style7.paddingTop);
        console.log('Padding-top carrusel 9:', style9.paddingTop);
    }
});
</script>
</body>
</html>