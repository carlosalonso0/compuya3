/**
 * Funcionalidad para carruseles
 */

// Configuración global
const CAROUSEL_TRANSITION_TIME = 500; // ms

/**
 * Inicializar un carrusel
 * @param {string} carouselId - ID del carrusel
 * @param {boolean} isBanner - Indica si es un carrusel de banners
 */
function initCarousel(carouselId, isBanner = false) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    // Para carruseles de banner, configurar animación automática
    if (isBanner) {
        setupBannerCarousel(carouselId);
    } else {
        // Para carruseles de productos, configurar navegación horizontal
        setupProductCarousel(carouselId);
    }
    
    // Configurar controles (flechas)
    setupCarouselControls(carouselId);
    
    // Configurar dots si existen
    setupCarouselDots(carouselId);
}

/**
 * Configurar carrusel de banners
 * @param {string} carouselId - ID del carrusel
 */
function setupBannerCarousel(carouselId) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const banners = carousel.querySelectorAll('.carousel-banner');
    if (banners.length <= 1) return;
    
    // Crear contenedor para los banners si no existe
    let bannerContainer = carousel.querySelector('.banner-container');
    
    if (!bannerContainer) {
        bannerContainer = document.createElement('div');
        bannerContainer.className = 'banner-container';
        bannerContainer.style.position = 'relative';
        bannerContainer.style.height = '100%';
        bannerContainer.style.width = '100%';
        bannerContainer.style.overflow = 'hidden';
        
        // Mover los banners al contenedor
        banners.forEach((banner, index) => {
            const slide = document.createElement('div');
            slide.className = 'banner-slide';
            slide.dataset.index = index;
            slide.style.position = 'absolute';
            slide.style.top = '0';
            slide.style.left = '0';
            slide.style.width = '100%';
            slide.style.height = '100%';
            slide.style.transition = `opacity ${CAROUSEL_TRANSITION_TIME}ms ease`;
            
            if (index === 0) {
                slide.style.opacity = '1';
                slide.style.display = 'block';
            } else {
                slide.style.opacity = '0';
                slide.style.display = 'none';
            }
            
            // Clonar el banner y añadirlo al slide
            const clone = banner.cloneNode(true);
            slide.appendChild(clone);
            bannerContainer.appendChild(slide);
        });
        
        // Si hay un div con la clase carousel-dots, lo preservamos
        const dots = carousel.querySelector('.carousel-dots');
        
        // Reemplazar el contenido con el nuevo contenedor
        carousel.innerHTML = '';
        carousel.appendChild(bannerContainer);
        
        // Agregar de nuevo los dots o crear nuevos si no existían
        if (dots) {
            carousel.appendChild(dots);
        } else if (banners.length > 1) {
            const newDots = document.createElement('div');
            newDots.className = 'carousel-dots';
            banners.forEach((_, index) => {
                const dot = document.createElement('div');
                dot.className = 'dot' + (index === 0 ? ' active' : '');
                dot.dataset.slide = index;
                newDots.appendChild(dot);
            });
            carousel.appendChild(newDots);
        }
    }
    
    // Configurar cambio automático
    const dots = carousel.querySelectorAll('.carousel-dots .dot');
    const totalSlides = dots.length;
    
    if (totalSlides > 1) {
        // Iniciar rotación automática
        let currentIndex = 0;
        const slideInterval = setInterval(() => {
            currentIndex = (currentIndex + 1) % totalSlides;
            changeBannerSlide(carouselId, currentIndex);
        }, 5000);
        
        // Guardar referencia para poder cancelar si es necesario
        carousel.dataset.slideInterval = slideInterval;
    }
}

/**
 * Cambiar slide en carrusel de banners
/**
 * Cambiar slide en carrusel de banners - Con tiempos ajustados
 * @param {string} carouselId - ID del carrusel
 * @param {number} index - Índice del slide a mostrar
 */
function changeBannerSlide(carouselId, index) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const bannerContainer = carousel.querySelector('.banner-container');
    if (!bannerContainer) return;
    
    const bannerSlides = bannerContainer.querySelectorAll('.banner-slide');
    if (!bannerSlides.length) return;
    
    // Encontrar el slide actualmente visible
    let currentSlide = null;
    let currentIndex = -1;
    
    bannerSlides.forEach((slide, idx) => {
        if (slide.style.opacity === '1' || window.getComputedStyle(slide).opacity === '1') {
            currentSlide = slide;
            currentIndex = idx;
        }
    });
    
    // Si el índice es el mismo o no encontramos el slide actual, validamos
    if (currentIndex === index) return;
    if (currentIndex === -1 && index > 0) currentIndex = 0;
    
    // Encontrar el nuevo slide a mostrar
    const newSlide = bannerSlides[index];
    if (!newSlide) return;
    
    // Mantener la altura del contenedor fija durante la transición
    const containerHeight = bannerContainer.offsetHeight;
    const containerWidth = bannerContainer.offsetWidth;
    bannerContainer.style.height = containerHeight + 'px';
    bannerContainer.style.width = containerWidth + 'px';
    
    // TRANSICIÓN MEJORADA: Mostrar nuevo slide y ocultar el actual simultáneamente
    
    // 1. Asegurar que el nuevo slide esté listo para aparecer
    newSlide.style.display = 'block';
    newSlide.style.zIndex = '1'; // Colocar detrás del slide actual
    newSlide.style.opacity = '0';
    
    // 2. Pequeño retraso para asegurar que el navegador procese
    setTimeout(() => {
        // 3. Colocar el nuevo slide por encima para la transición
        newSlide.style.zIndex = '2';
        
        // 4. Si existe un slide actual, asegurar que esté visible
        if (currentSlide) {
            currentSlide.style.zIndex = '1';
            currentSlide.style.display = 'block';
        }
        
        // 5. Iniciar la transición de opacidad (aparece el nuevo, desaparece el actual)
        newSlide.style.opacity = '1';
        if (currentSlide) {
            currentSlide.style.opacity = '0';
        }
        
        // 6. Después de la transición, limpiar
        setTimeout(() => {
            if (currentSlide) {
                currentSlide.style.display = 'none';
            }
            
            // Restaurar el z-index normal
            newSlide.style.zIndex = '';
            if (currentSlide) currentSlide.style.zIndex = '';
        }, CAROUSEL_TRANSITION_TIME + 50);
    }, 50);
    
    // Actualizar los dots
    const dots = carousel.querySelectorAll('.carousel-dots .dot');
    dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
    });
}
/**
 * Configurar carrusel de productos
 * @param {string} carouselId - ID del carrusel
 */
function setupProductCarousel(carouselId) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const productContainer = carousel.querySelector('.product-carousel');
    if (!productContainer) return;
    
    // Para carrusel 6 (oferta especial), no hacer nada más
    if (carouselId === 'carousel-6') return;
    
    // Verificar la cantidad de productos
    const productCards = productContainer.querySelectorAll('.product-card');
    const productCount = productCards.length;
    
    // Si hay menos de 5 productos, no necesitamos controles ni scroll
    if (productCount <= 4) {
        const controls = carousel.querySelector('.carousel-controls');
        if (controls) controls.style.display = 'none';
        
        // Ajustar el contenedor para que no permita scroll
        productContainer.style.overflowX = 'hidden';
        return;
    }
    
    // Hacer que el scroll sea suave
    productContainer.style.scrollBehavior = 'smooth';
    
    // Asegurar que solo se muestren 4 productos a la vez
    const containerWidth = productContainer.clientWidth;
    const cardWidth = containerWidth / 4;
    
    // Establecer anchos exactos a cada tarjeta de producto
    productCards.forEach(card => {
        card.style.flex = `0 0 ${cardWidth - 15}px`;
        card.style.width = `${cardWidth - 15}px`;
        card.style.maxWidth = `${cardWidth - 15}px`;
    });
}

/**
 * Mover carrusel de productos
 * @param {string} carouselId - ID del carrusel
 * @param {string} direction - Dirección ('prev' o 'next')
 */
function moveProductCarousel(carouselId, direction) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const productContainer = carousel.querySelector('.product-carousel');
    if (!productContainer) return;
    
    // Verificar la cantidad de productos
    const productCards = productContainer.querySelectorAll('.product-card');
    const productCount = productCards.length;
    
    // Si hay menos de 5 productos, no hacemos nada
    if (productCount <= 4) return;
    
    // Obtener ancho visible del contenedor (espacio para 4 productos)
    const containerWidth = productContainer.clientWidth;
    
    // Calcular el ancho de un solo producto
    const cardWidth = containerWidth / 4;
    
    // Calcular el desplazamiento actual
    const currentScroll = productContainer.scrollLeft;
    
    // Calcular nuevo desplazamiento (un producto a la vez)
    let newScroll;
    if (direction === 'next') {
        newScroll = currentScroll + cardWidth;
        
        // Si estamos llegando al final, volver al inicio
        if (newScroll + 5 >= productContainer.scrollWidth - containerWidth) {
            newScroll = 0;
        }
    } else {
        newScroll = currentScroll - cardWidth;
        
        // Si estamos al inicio y vamos hacia atrás, ir al final
        if (newScroll < 5) {
            // Ir al último grupo de 4 productos
            const lastGroupPosition = Math.max(0, (productCount - 4) * cardWidth);
            newScroll = lastGroupPosition;
        }
    }
    
    // Animar el desplazamiento
    productContainer.scrollTo({
        left: newScroll,
        behavior: 'smooth'
    });
}
/**
 * Configurar controles de carrusel
 * @param {string} carouselId - ID del carrusel
 */
function setupCarouselControls(carouselId) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    // Si no hay controles, crearlos
    let controlsContainer = carousel.querySelector('.carousel-controls');
    
    if (!controlsContainer) {
        controlsContainer = document.createElement('div');
        controlsContainer.className = 'carousel-controls';
        
        const prevButton = document.createElement('button');
        prevButton.className = 'carousel-control prev';
        prevButton.innerHTML = '&#10094;';
        prevButton.dataset.carousel = carouselId;
        
        const nextButton = document.createElement('button');
        nextButton.className = 'carousel-control next';
        nextButton.innerHTML = '&#10095;';
        nextButton.dataset.carousel = carouselId;
        
        controlsContainer.appendChild(prevButton);
        controlsContainer.appendChild(nextButton);
        
        carousel.appendChild(controlsContainer);
    }
    
    const prevButton = carousel.querySelector('.carousel-control.prev');
    const nextButton = carousel.querySelector('.carousel-control.next');
    
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            const isBanner = Boolean(carousel.querySelector('.banner-container'));
            
            if (isBanner) {
                // Buscar índice actual
                const dots = carousel.querySelectorAll('.carousel-dots .dot');
                const currentIndex = Array.from(dots).findIndex(dot => dot.classList.contains('active'));
                
                // Calcular nuevo índice
                const newIndex = (currentIndex - 1 + dots.length) % dots.length;
                changeBannerSlide(carouselId, newIndex);
            } else {
                moveProductCarousel(carouselId, 'prev');
            }
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            const isBanner = Boolean(carousel.querySelector('.banner-container'));
            
            if (isBanner) {
                // Buscar índice actual
                const dots = carousel.querySelectorAll('.carousel-dots .dot');
                const currentIndex = Array.from(dots).findIndex(dot => dot.classList.contains('active'));
                
                // Calcular nuevo índice
                const newIndex = (currentIndex + 1) % dots.length;
                changeBannerSlide(carouselId, newIndex);
            } else {
                moveProductCarousel(carouselId, 'next');
            }
        });
    }
}

/**
 * Configurar dots de navegación
 * @param {string} carouselId - ID del carrusel
 */
function setupCarouselDots(carouselId) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const dots = carousel.querySelectorAll('.carousel-dots .dot');
    if (!dots.length) return;
    
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            const index = parseInt(dot.dataset.slide);
            changeBannerSlide(carouselId, index);
            
            // Resetear el intervalo automático para evitar cambios inmediatos
            if (carousel.dataset.slideInterval) {
                clearInterval(parseInt(carousel.dataset.slideInterval));
                
                // Reiniciar intervalo
                const slideInterval = setInterval(() => {
                    const currentIndex = Array.from(dots).findIndex(d => d.classList.contains('active'));
                    const newIndex = (currentIndex + 1) % dots.length;
                    changeBannerSlide(carouselId, newIndex);
                }, 5000);
                
                carousel.dataset.slideInterval = slideInterval;
            }
        });
    });
}

/**
 * Inicializar todos los carruseles cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrusel principal (banner 1)
    initCarousel('carousel-1', true);
    
    // Inicializar resto de carruseles de banner
    for (let i = 2; i <= 5; i++) {
        initCarousel('carousel-' + i, true);
    }
    initCarousel('carousel-7', true);
    
    // Inicializar carruseles de productos
    initCarousel('carousel-6', false);
    initCarousel('carousel-8', false);
    initCarousel('carousel-9', false);
    
    // Actualizar carruseles en cambio de tamaño de ventana
    window.addEventListener('resize', function() {
        // Reiniciar carruseles de productos
        for (let i = 6; i <= 9; i++) {
            if (i === 7) continue; // Saltar el 7 que es un banner
            const carouselId = 'carousel-' + i;
            const carousel = document.getElementById(carouselId);
            if (carousel) {
                const productContainer = carousel.querySelector('.product-carousel');
                if (productContainer) {
                    // Verificar si hay suficientes productos para scroll horizontal
                    const visibleWidth = productContainer.clientWidth;
                    const scrollWidth = productContainer.scrollWidth;
                    
                    // Si no hay suficiente contenido para scroll, ocultar controles
                    const controls = carousel.querySelector('.carousel-controls');
                    if (controls) {
                        controls.style.display = scrollWidth > visibleWidth ? 'flex' : 'none';
                    }
                }
            }
        }
    });
});