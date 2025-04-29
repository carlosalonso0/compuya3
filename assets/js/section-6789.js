document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let autoRotationIntervals = {};
    let bannerIntervals = {};
    
    // Esperar a que todo esté cargado
    setTimeout(function() {
        // 1. ELIMINAR TODAS LAS FLECHAS DE NAVEGACIÓN (incluidas las laterales)
        document.querySelectorAll('.carousel-control, .slick-arrow, [class*="arrow"], .nav-arrow').forEach(arrow => {
            arrow.remove();
        });
        
        // 2. CONFIGURAR CARRUSELES DE PRODUCTOS
        setupProductCarousels();
        
        // 3. CONFIGURAR CARRUSELES DE BANNERS
        setupBannerCarousels();
    }, 500);
    
    // Configurar carruseles de productos con rotación automática
    function setupProductCarousels() {
        const carousel8 = document.getElementById('carousel-8');
        const carousel9 = document.getElementById('carousel-9');
        
        if (!carousel8 || !carousel9) return;
        
        function setupProductGrid(carousel) {
            const container = carousel.querySelector('.product-carousel');
            if (!container) return;
            
            // Decidir columnas según ancho disponible
            const availableWidth = container.offsetWidth;
            let columns = 4;
            
            if (availableWidth < 700 && availableWidth > 500) {
                columns = 3;
            } else if (availableWidth <= 500) {
                columns = 2;
            }
            
            // Aplicar grid
            container.style.display = 'grid';
            container.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
            container.style.gap = '10px';
            
            return { container, columns };
        }
        
        // Configurar grids
        const grid8 = setupProductGrid(carousel8);
        const grid9 = setupProductGrid(carousel9);
        
        // Iniciar rotación solo si hay más productos que espacios visibles
        startWindowSlideRotation('carousel-8', grid8.columns);
        startWindowSlideRotation('carousel-9', grid9.columns);
        
        // Igualar tamaños
        equalizeCardSizes();
    }
    
    // Iniciar rotación automática de productos como una ventana deslizante
    function startWindowSlideRotation(carouselId, visibleCount) {
        if (autoRotationIntervals[carouselId]) {
            clearInterval(autoRotationIntervals[carouselId]);
        }
        
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const container = carousel.querySelector('.product-carousel');
        if (!container) return;
        
        const allItems = Array.from(container.querySelectorAll('.product-card-wrapper'));
        
        // Solo rotar si hay más productos que espacios visibles
        if (allItems.length <= visibleCount) {
            // Mostrar solo el número de productos que hay
            allItems.forEach(item => {
                item.style.display = 'block';
            });
            return; // No iniciar rotación
        }
        
        // Configuración inicial: mostrar los primeros 'visibleCount' elementos
        allItems.forEach((item, i) => {
            item.style.display = i < visibleCount ? 'block' : 'none';
        });
        
        // Guardar el índice inicial (primera "ventana")
        let startIndex = 0;
        
        // Iniciar rotación cada 3 segundos
        autoRotationIntervals[carouselId] = setInterval(() => {
            // Avanzar la ventana en una posición
            startIndex = (startIndex + 1) % allItems.length;
            
            // Actualizar qué elementos están visibles
            allItems.forEach((item, i) => {
                // Calcular si este elemento debe estar en la ventana actual
                const isVisible = false;
                for (let j = 0; j < visibleCount; j++) {
                    const visibleIndex = (startIndex + j) % allItems.length;
                    if (i === visibleIndex) {
                        item.style.display = 'block';
                        return;
                    }
                }
                // Si no está en la ventana, ocultarlo
                item.style.display = 'none';
            });
        }, 3000);
    }
    
    // Configurar carruseles de banners con rotación y puntos
    function setupBannerCarousels() {
        const bannerCarousels = ['carousel-1', 'carousel-2', 'carousel-3', 'carousel-4', 'carousel-5', 'carousel-7'];
        
        bannerCarousels.forEach(carouselId => {
            const carousel = document.getElementById(carouselId);
            if (!carousel) return;
            
            const slides = carousel.querySelectorAll('.banner-slide');
            if (slides.length <= 1) return;
            
            // Crear o limpiar puntos
            let dotsContainer = carousel.querySelector('.carousel-dots');
            if (!dotsContainer) {
                dotsContainer = document.createElement('div');
                dotsContainer.className = 'carousel-dots';
                carousel.appendChild(dotsContainer);
            } else {
                dotsContainer.innerHTML = '';
            }
            
            // Crear puntos
            slides.forEach((slide, index) => {
                const dot = document.createElement('div');
                dot.className = 'dot' + (index === 0 ? ' active' : '');
                dot.dataset.slide = index;
                
                dot.addEventListener('click', function() {
                    changeBannerSlide(carouselId, index);
                });
                
                dotsContainer.appendChild(dot);
            });
            
            // Iniciar rotación automática
            startBannerRotation(carouselId);
        });
    }
    
    // Cambiar slide de banner
    function changeBannerSlide(carouselId, index) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const slides = carousel.querySelectorAll('.banner-slide');
        if (!slides.length) return;
        
        // Ocultar todos los slides
        slides.forEach(slide => {
            slide.style.display = 'none';
            slide.style.opacity = '0';
        });
        
        // Mostrar el slide seleccionado
        slides[index].style.display = 'block';
        setTimeout(() => {
            slides[index].style.opacity = '1';
        }, 10);
        
        // Actualizar puntos
        const dots = carousel.querySelectorAll('.carousel-dots .dot');
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }
    
    // Iniciar rotación automática de banners
    function startBannerRotation(carouselId) {
        if (bannerIntervals[carouselId]) {
            clearInterval(bannerIntervals[carouselId]);
        }
        
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const slides = carousel.querySelectorAll('.banner-slide');
        if (slides.length <= 1) return;
        
        let currentIndex = 0;
        bannerIntervals[carouselId] = setInterval(() => {
            currentIndex = (currentIndex + 1) % slides.length;
            changeBannerSlide(carouselId, currentIndex);
        }, 5000);
    }
    
    // Función para igualar tamaños de tarjetas
    function equalizeCardSizes() {
        const allCards = document.querySelectorAll('.carousel-8 .product-card, .carousel-9 .product-card');
        
        // Resetear para medir correctamente
        allCards.forEach(card => {
            card.style.width = '100%';
        });
        
        // Encontrar máximo ancho
        setTimeout(() => {
            let maxWidth = 0;
            allCards.forEach(card => {
                const width = card.offsetWidth;
                if (width > maxWidth) maxWidth = width;
            });
            
            // Aplicar mismo ancho
            allCards.forEach(card => {
                card.style.width = maxWidth + 'px';
            });
        }, 0);
    }
    
    // Eventos
    window.addEventListener('resize', function() {
        setupProductCarousels();
    });
});