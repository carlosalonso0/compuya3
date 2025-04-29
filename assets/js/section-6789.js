/**
 * Script mejorado para control de carruseles en la sección 6789
 * Reemplaza el contenido de assets/js/section-6789.js
 */
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
            
            // Limpiar estilos previos que podrían estar afectando la visualización
            container.style.cssText = '';
            
            // Decidir columnas según ancho disponible
            const availableWidth = container.offsetWidth;
            let columns = 4; // Por defecto, siempre intentamos mostrar 4 columnas
            
            if (availableWidth < 700 && availableWidth > 500) {
                columns = 3;
            } else if (availableWidth <= 500) {
                columns = 2;
            }
            
            // Aplicar grid
            container.style.display = 'grid';
            container.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
            container.style.gap = '10px';
            
            // Forzar que las tarjetas sean visibles (corregir problema de invisibilidad)
            const cards = container.querySelectorAll('.product-card-wrapper');
            cards.forEach(card => {
                card.style.opacity = '1';
                card.style.visibility = 'visible';
            });
            
            return { container, columns };
        }
        
        // Configurar grids
        const grid8 = setupProductGrid(carousel8);
        const grid9 = setupProductGrid(carousel9);
        
        // Iniciar rotación solo si hay más productos que espacios visibles
        if (grid8) {
            startWindowSlideRotation('carousel-8', grid8.columns);
        }
        
        if (grid9) {
            startWindowSlideRotation('carousel-9', grid9.columns);
        }
        
        // Igualar tamaños
        equalizeCardSizes();
    }
    
        // Iniciar rotación automática de productos como una ventana deslizante
    function startWindowSlideRotation(carouselId, visibleCount) {
        // Detener cualquier rotación previa
        if (autoRotationIntervals[carouselId]) {
            clearInterval(autoRotationIntervals[carouselId]);
            delete autoRotationIntervals[carouselId];
        }
        
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const container = carousel.querySelector('.product-carousel');
        if (!container) return;
        
        // Seleccionar solo elementos reales del carrusel, excluyendo espacios vacíos
        const allItems = Array.from(container.querySelectorAll('.product-card-wrapper:not(.empty-space)'));
        const totalItems = allItems.length;
        
        console.log(`Carrusel ${carouselId}: ${totalItems} productos, ${visibleCount} espacios visibles`);
        
        // DETECCIÓN MÁS ESTRICTA: Solo rotar si hay estrictamente más productos que espacios
        // El operador > asegura que solo se activa cuando hay más productos que espacios
        if (totalItems <= visibleCount) {
            console.log(`Carrusel ${carouselId}: No hay suficientes productos para rotación (${totalItems} <= ${visibleCount})`);
            
            // Mostrar todos los productos disponibles y ocultar cualquier control
            allItems.forEach(item => {
                item.style.display = 'block';
                item.style.opacity = '1';
                item.style.visibility = 'visible';
            });
            
            // Ocultar controles de navegación
            const controls = carousel.querySelector('.carousel-controls');
            if (controls) {
                controls.style.display = 'none';
            }
            
            // Eliminar cualquier intervalo existente para este carrusel
            if (autoRotationIntervals[carouselId]) {
                clearInterval(autoRotationIntervals[carouselId]);
                delete autoRotationIntervals[carouselId];
            }
            
            return; // No iniciar rotación
        }
        
        console.log(`Carrusel ${carouselId}: Iniciando rotación con ${totalItems} productos para ${visibleCount} espacios`);
        
        // Mostrar controles de navegación
        const controls = carousel.querySelector('.carousel-controls');
        if (controls) {
            controls.style.display = 'flex';
        }
        
        // Guardar el índice inicial (primera "ventana")
        let currentIndex = 0;
        
        // Configuración inicial: hacer visibles los primeros elementos
        allItems.forEach((item, idx) => {
            // Primeros N elementos visibles, resto ocultos
            if (idx < visibleCount) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
            // Asegurar que todos tengan opacidad correcta
            item.style.opacity = '1';
            item.style.visibility = 'visible';
        });
        
        // Configurar botones de navegación si existen
        if (controls) {
            const prevButton = controls.querySelector('.prev');
            const nextButton = controls.querySelector('.next');
            
            if (prevButton) {
                // Eliminar manejadores de eventos anteriores y crear nuevo botón
                const newPrevButton = prevButton.cloneNode(true);
                prevButton.parentNode.replaceChild(newPrevButton, prevButton);
                
                newPrevButton.addEventListener('click', function() {
                    // Decrementar el índice para implementar la rotación correcta
                    currentIndex = (currentIndex - 1 + totalItems) % totalItems;
                    rotateCarouselItems(allItems, currentIndex, visibleCount, carouselId);
                });
            }
            
            if (nextButton) {
                // Eliminar manejadores de eventos anteriores y crear nuevo botón
                const newNextButton = nextButton.cloneNode(true);
                nextButton.parentNode.replaceChild(newNextButton, nextButton);
                
                newNextButton.addEventListener('click', function() {
                    // Incrementar el índice para implementar la rotación correcta
                    currentIndex = (currentIndex + 1) % totalItems;
                    rotateCarouselItems(allItems, currentIndex, visibleCount, carouselId);
                });
            }
        }
        
        // Iniciar rotación automática cada 4 segundos (solo si hay más productos que espacios)
        if (totalItems > visibleCount) {
            autoRotationIntervals[carouselId] = setInterval(() => {
                // Incrementar al siguiente ítem
                currentIndex = (currentIndex + 1) % totalItems;
                rotateCarouselItems(allItems, currentIndex, visibleCount, carouselId);
            }, 4000);
        }
    }
    
    // Función definitiva para implementar la rotación correcta
    function rotateCarouselItems(allItems, startIndex, visibleCount, carouselId) {
        const totalItems = allItems.length;
        
        // Si no hay suficientes elementos para rotar, no hacer nada
        if (totalItems <= visibleCount) {
            console.log(`Rotación cancelada: solo hay ${totalItems} productos para ${visibleCount} espacios`);
            return;
        }
        
        // Recopilar información para depuración
        const getItemName = (item) => {
            const nameEl = item.querySelector('.product-name');
            return nameEl ? nameEl.textContent.trim().substring(0, 10) : 'desconocido';
        };
        
        // Registrar qué elementos están actualmente visibles
        const currentlyVisibleItems = [];
        allItems.forEach((item, index) => {
            if (item.style.display === 'block') {
                currentlyVisibleItems.push({ index, name: getItemName(item) });
            }
        });
        
        // Determinar qué elementos deberían estar visibles
        const shouldBeVisible = [];
        for (let i = 0; i < visibleCount; i++) {
            shouldBeVisible.push((startIndex + i) % totalItems);
        }
        
        // Registrar cambios para depuración
        console.log(`Carrusel ${carouselId}: Rotando a partir del índice ${startIndex}`);
        console.log(`Actualmente visibles: ${currentlyVisibleItems.map(item => item.name).join(', ')}`);
        console.log(`Deberían ser visibles índices: ${shouldBeVisible.join(', ')}`);
        
        // Aplicar cambios de visibilidad a todos los elementos
        allItems.forEach((item, index) => {
            if (shouldBeVisible.includes(index)) {
                if (item.style.display !== 'block') {
                    console.log(`Mostrando: ${getItemName(item)}`);
                }
                item.style.display = 'block';
            } else {
                if (item.style.display === 'block') {
                    console.log(`Ocultando: ${getItemName(item)}`);
                }
                item.style.display = 'none';
            }
        });
        
        // Registrar estado final
        const finalVisibleItems = [];
        allItems.forEach((item, index) => {
            if (item.style.display === 'block') {
                finalVisibleItems.push({ index, name: getItemName(item) });
            }
        });
        console.log(`Ahora visibles: ${finalVisibleItems.map(item => item.name).join(', ')}`);
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
        // Asegurarnos de que todas las tarjetas en carruseles 8 y 9 sean visibles
        document.querySelectorAll('.carousel-8 .product-card-wrapper, .carousel-9 .product-card-wrapper').forEach(wrapper => {
            wrapper.style.opacity = '1';
            wrapper.style.visibility = 'visible';
        });
        
        // Seleccionar todas las tarjetas de producto
        const allCards = document.querySelectorAll('.carousel-8 .product-card, .carousel-9 .product-card');
        
        // Resetear para medir correctamente
        allCards.forEach(card => {
            card.style.width = '100%';
            card.style.height = '';
            card.style.minHeight = '330px'; // Asegurar altura mínima
        });
        
        // Encontrar máximo ancho y altura
        setTimeout(() => {
            let maxWidth = 0;
            let maxHeight = 0;
            
            allCards.forEach(card => {
                const width = card.offsetWidth;
                const height = card.offsetHeight;
                
                if (width > maxWidth) maxWidth = width;
                if (height > maxHeight) maxHeight = height;
            });
            
            // Aplicar mismas dimensiones a todas las tarjetas
            allCards.forEach(card => {
                card.style.width = maxWidth + 'px';
                card.style.height = Math.max(330, maxHeight) + 'px'; // Usar al menos 330px de altura
            });
            
            console.log(`Tarjetas ajustadas a: ${maxWidth}px de ancho, ${maxHeight}px de altura`);
        }, 100); // Dar un poco más de tiempo para el renderizado
    }
    
    // Eventos
    window.addEventListener('resize', function() {
        // Detener todas las rotaciones automáticas
        Object.keys(autoRotationIntervals).forEach(key => {
            clearInterval(autoRotationIntervals[key]);
            delete autoRotationIntervals[key];
        });
        
        // Reconfigurar carruseles
        setupProductCarousels();
    });
});



/**
 * Script para corregir rotación en carruseles 8 y 9
 * Solo modifica la función de rotación para seguir el patrón correcto
 */
document.addEventListener('DOMContentLoaded', function() {
    // Ejecutar cuando todo esté cargado
    setTimeout(function() {
        console.clear();
        console.log("🔄 Script de corrección de rotación");
        
        // Buscar todos los botones de carrusel en secciones 8 y 9
        const carousel8Btns = document.querySelectorAll('#carousel-8 .carousel-control');
        const carousel9Btns = document.querySelectorAll('#carousel-9 .carousel-control');
        
        // Reemplazar los eventos de clic en los botones
        function replaceButtons(buttons, carouselId) {
            buttons.forEach(button => {
                // Clonar para eliminar eventos antiguos
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                
                // Dirección basada en la clase del botón
                const direction = newButton.classList.contains('next') ? 1 : -1;
                
                // Agregar nuevo evento
                newButton.addEventListener('click', function() {
                    rotateCorrectly(carouselId, direction);
                });
            });
        }
        
        // Reemplazar botones en ambos carruseles
        replaceButtons(carousel8Btns, 'carousel-8');
        replaceButtons(carousel9Btns, 'carousel-9');
        
        // También reemplazar intervalo automático
        const carousels = ['carousel-8', 'carousel-9'];
        carousels.forEach(carouselId => {
            // Buscar y detener intervalos existentes (por id o en window)
            if (window.carouselIntervals && window.carouselIntervals[carouselId]) {
                clearInterval(window.carouselIntervals[carouselId]);
            }
            
            // Crear nuevo intervalo
            const carousel = document.getElementById(carouselId);
            if (carousel) {
                const items = carousel.querySelectorAll('.product-card-wrapper');
                const visibleSpaces = getVisibleCount();
                
                // Solo rotar si hay más productos que espacios
                if (items.length > visibleSpaces) {
                    // Crear nuevo intervalo para rotación
                    window.carouselIntervals = window.carouselIntervals || {};
                    window.carouselIntervals[carouselId] = setInterval(function() {
                        rotateCorrectly(carouselId, 1);
                    }, 4000);
                }
            }
        });
    }, 1000);
});

// Obtener número de productos visibles según ancho de pantalla
function getVisibleCount() {
    if (window.innerWidth < 768) return 2;
    if (window.innerWidth < 992) return 3;
    return 4;
}

// Función de rotación mejorada
function rotateCorrectly(carouselId, direction) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const items = carousel.querySelectorAll('.product-card-wrapper:not(.empty-space)');
    const visibleSpaces = getVisibleCount();
    
    // No rotar si no hay suficientes productos
    if (items.length <= visibleSpaces) {
        console.log(`Carrusel ${carouselId}: No hay suficientes productos para rotar`);
        return;
    }
    
    // 1. Determinar qué productos están actualmente visibles
    const visibleItems = [];
    const hiddenItems = [];
    
    items.forEach(item => {
        if (getComputedStyle(item).display !== 'none') {
            visibleItems.push(item);
        } else {
            hiddenItems.push(item);
        }
    });
    
    // Depuración
    function getProductName(item) {
        const nameEl = item.querySelector('.product-name');
        return nameEl ? nameEl.textContent.trim().substring(0, 10) : 'desconocido';
    }
    
    console.log(`Antes: ${visibleItems.map(getProductName).join(', ')}`);
    
    // 2. Realizar la rotación según la dirección
    if (direction > 0) {
        // Al avanzar: quitar el primero, añadir uno al final
        if (visibleItems.length > 0 && hiddenItems.length > 0) {
            visibleItems[0].style.display = 'none';
            hiddenItems[0].style.display = 'block';
        }
    } else {
        // Al retroceder: quitar el último, añadir uno al inicio
        if (visibleItems.length > 0 && hiddenItems.length > 0) {
            visibleItems[visibleItems.length - 1].style.display = 'none';
            hiddenItems[hiddenItems.length - 1].style.display = 'block';
        }
    }
    
    // Depuración para verificar el resultado
    setTimeout(() => {
        const newVisible = Array.from(items).filter(item => 
            getComputedStyle(item).display !== 'none'
        );
        console.log(`Después: ${newVisible.map(getProductName).join(', ')}`);
    }, 10);
}