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
            const clone = banner.cloneNode(true);
            clone.dataset.index = index;
            if (index > 0) {
                clone.style.display = 'none';
            }
            bannerContainer.appendChild(clone);
        });
        
        // Si hay un div con la clase carousel-dots, lo preservamos
        const dots = carousel.querySelector('.carousel-dots');
        
        // Reemplazar el contenido con el nuevo contenedor
        const carouselContent = carousel.innerHTML;
        carousel.innerHTML = '';
        carousel.appendChild(bannerContainer);
        
        // Agregar de nuevo los dots o crear nuevos si no existían
        if (dots) {
            carousel.appendChild(dots);
        } else {
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
 * @param {string} carouselId - ID del carrusel
 * @param {number} index - Índice del slide a mostrar
 */
function changeBannerSlide(carouselId, index) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const bannerContainer = carousel.querySelector('.banner-container');
    if (!bannerContainer) return;
    
    const banners = bannerContainer.querySelectorAll('.carousel-banner');
    
    // Ocultar todos los banners
    banners.forEach((banner) => {
        banner.style.opacity = '0';
        setTimeout(() => {
            banner.style.display = 'none';
        }, CAROUSEL_TRANSITION_TIME);
    });
    
    // Mostrar el banner seleccionado
    const targetBanner = banners[index];
    if (targetBanner) {
        setTimeout(() => {
            targetBanner.style.display = 'block';
            setTimeout(() => {
                targetBanner.style.opacity = '1';
            }, 50);
        }, CAROUSEL_TRANSITION_TIME);
    }
    
    // Actualizar dots
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
    
    // Hacer que el scroll sea suave
    productContainer.style.scrollBehavior = 'smooth';
    
    // Verificar si hay suficientes productos para scroll horizontal
    const visibleWidth = productContainer.clientWidth;
    const scrollWidth = productContainer.scrollWidth;
    
    // Si no hay suficiente contenido para scroll, ocultar controles
    if (scrollWidth <= visibleWidth) {
        const controls = carousel.querySelector('.carousel-controls');
        if (controls) controls.style.display = 'none';
    }
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
    
    // Obtener ancho visible del contenedor
    const containerWidth = productContainer.clientWidth;
    
    // Calcular el desplazamiento actual
    const currentScroll = productContainer.scrollLeft;
    
    // Calcular nuevo desplazamiento (un carrusel completo a la vez)
    let newScroll;
    if (direction === 'next') {
        newScroll = currentScroll + containerWidth;
    } else {
        newScroll = Math.max(0, currentScroll - containerWidth);
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