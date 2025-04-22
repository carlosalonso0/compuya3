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
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar navegación para carruseles 8 y 9
    setupCarouselNav('carousel-8');
    setupCarouselNav('carousel-9');
    
    function setupCarouselNav(carouselId) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const prevBtn = carousel.querySelector('.carousel-control.prev');
        const nextBtn = carousel.querySelector('.carousel-control.next');
        const productContainer = carousel.querySelector('.product-carousel');
        
        if (!prevBtn || !nextBtn || !productContainer) return;
        
        const items = productContainer.querySelectorAll('.product-card-wrapper');
        if (items.length <= 4) return;
        
        // Ocultar todos menos los primeros 4
        for (let i = 4; i < items.length; i++) {
            items[i].style.display = 'none';
        }
        
        let currentIndex = 0;
        
        nextBtn.addEventListener('click', function() {
            // Ocultar el elemento actual
            items[currentIndex].style.display = 'none';
            
            // Calcular el próximo índice visible
            const nextVisibleIndex = (currentIndex + 4) % items.length;
            items[nextVisibleIndex].style.display = '';
            
            // Actualizar el índice actual
            currentIndex = (currentIndex + 1) % items.length;
        });
        
        prevBtn.addEventListener('click', function() {
            // Calcular el índice previo
            const prevIndex = (currentIndex - 1 + items.length) % items.length;
            
            // Ocultar el último elemento visible
            const lastVisibleIndex = (currentIndex + 3) % items.length;
            items[lastVisibleIndex].style.display = 'none';
            
            // Mostrar el elemento previo
            items[prevIndex].style.display = '';
            
            // Actualizar el índice actual
            currentIndex = prevIndex;
        });
    }
});
</script>
<script>
// Script para corregir carruseles 8 y 9
document.addEventListener('DOMContentLoaded', function() {
    // Para carruseles 8 y 9
    initFixedCarousel('carousel-8');
    initFixedCarousel('carousel-9');
    
    function initFixedCarousel(carouselId) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const prevBtn = carousel.querySelector('.carousel-control.prev');
        const nextBtn = carousel.querySelector('.carousel-control.next');
        
        if (!prevBtn || !nextBtn) return;
        
        const products = Array.from(carousel.querySelectorAll('.product-card-wrapper:not(.empty-space)'));
        if (products.length <= 4) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            return;
        }
        
        // Solo mostrar los primeros 4
        products.forEach((product, index) => {
            product.style.display = index < 4 ? 'block' : 'none';
        });
        
        let currentStart = 0;
        
        nextBtn.addEventListener('click', function() {
            // Avanzar 1 producto
            currentStart = (currentStart + 1) % products.length;
            updateVisibleProducts();
        });
        
        prevBtn.addEventListener('click', function() {
            // Retroceder 1 producto
            currentStart = (currentStart - 1 + products.length) % products.length;
            updateVisibleProducts();
        });
        
        function updateVisibleProducts() {
            products.forEach((product, index) => {
                product.style.display = 'none';
            });
            
            // Mostrar los 4 productos actuales
            for (let i = 0; i < 4; i++) {
                const index = (currentStart + i) % products.length;
                products[index].style.display = 'block';
            }
        }
    }
});
</script>
<style>
/* Forzar tamaños iguales para todos los carruseles */
.product-card {
    height: 380px !important; /* Altura fija para todas las tarjetas */
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Forzar altura fija para las tarjetas de tarjetas gráficas */
#carousel-8 .product-card {
    height: 380px !important;
}

/* Forzar altura fija para las tarjetas de procesadores */
#carousel-9 .product-card {
    height: 380px !important;
}

/* Ajustar tamaños de componentes internos */
.product-image-container {
    height: 120px !important;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image {
    max-height: 110px !important;
    object-fit: contain;
}

.product-name {
    height: 40px !important;
    overflow: hidden;
    font-size: 13px !important;
}

.price-container, .stock-info, .promo-tag {
    margin-bottom: 8px !important;
}

/* Asegurar que el botón quede al final */
.product-info {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.btn-add-cart {
    margin-top: auto;
}
</style>

<script>
// Script para igualar alturas de tarjetas
document.addEventListener('DOMContentLoaded', function() {
    // Obtener todas las tarjetas
    const allCards = document.querySelectorAll('.product-card');
    
    // Función para establecer altura fija
    function setFixedHeight() {
        // Establecer altura fija para todas las tarjetas
        allCards.forEach(card => {
            card.style.height = '380px';
        });
    }
    
    // Ejecutar ahora y al redimensionar
    setFixedHeight();
    window.addEventListener('resize', setFixedHeight);
});
</script>

</body>
</html>