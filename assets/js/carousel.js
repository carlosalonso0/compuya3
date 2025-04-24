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
    
    // Para carrusel 6, no hacer nada
    if (carouselId === 'carousel-6') return;
    
    // Hacer que el scroll sea suave
    productContainer.style.scrollBehavior = 'smooth';
    
    // Obtener el ancho del contenedor
    const containerWidth = productContainer.clientWidth;
    const cardWidth = 220; // Ancho fijo de 220px
    
    // Establecer anchos exactos a cada tarjeta de producto
    const productCards = productContainer.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.style.width = `${cardWidth}px`;
        card.style.flex = `0 0 ${cardWidth}px`;
        card.style.maxWidth = `${cardWidth}px`;
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
    
    // Verificar si es un carrusel de grid o de scroll
    const isGridCarousel = productContainer.classList.contains('few-products');
    if (isGridCarousel) return; // No hay nada que mover en carruseles de grid
    
    // Para carruseles con scroll horizontal
    // Verificar la cantidad de productos
    const productCards = productContainer.querySelectorAll('.product-card-wrapper');
    const productCount = productCards.length;
    
    // Si hay menos de 5 productos, no hacemos nada
    if (productCount <= 4) return;
    
    // Calcular el ancho de un solo producto (25% del contenedor)
    const containerWidth = productContainer.clientWidth;
    const cardWidth = containerWidth / 4;
    
    // Calcular el desplazamiento actual
    const currentScroll = productContainer.scrollLeft;
    
    // Calcular nuevo desplazamiento (un producto a la vez)
    let newScroll;
    if (direction === 'next') {
        newScroll = currentScroll + cardWidth;
        
        // Si estamos llegando al final, volver al inicio
        if (newScroll + containerWidth >= productContainer.scrollWidth - 5) {
            newScroll = 0;
        }
    } else {
        newScroll = currentScroll - cardWidth;
        
        // Si estamos al inicio y vamos hacia atrás, ir al final
        if (newScroll < 5) {
            // Ir al último grupo de 4 productos
            const lastGroupPosition = Math.max(0, productContainer.scrollWidth - containerWidth);
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

// Función para hacer que los carruseles de productos se muevan
document.addEventListener('DOMContentLoaded', function() {
    // Configurar carruseles 8 y 9
    setupProductCarouselControls('carousel-8');
    setupProductCarouselControls('carousel-9');
});

function setupProductCarouselControls(carouselId) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const items = carousel.querySelectorAll('.product-card-wrapper');
    if (items.length <= 4) return; // Si hay 4 o menos elementos, no necesitamos controles
    
    const prevBtn = carousel.querySelector('.carousel-control.prev');
    const nextBtn = carousel.querySelector('.carousel-control.next');
    
    if (!prevBtn || !nextBtn) return;
    
    // Variables para seguimiento
    let currentPosition = 0;
    const totalItems = items.length;
    const visibleItems = 4;
    
    // Ocultar todos los productos excepto los primeros 4
    for (let i = visibleItems; i < totalItems; i++) {
        items[i].style.display = 'none';
    }
    
    // Función para mostrar los elementos actuales
    function updateVisibleItems() {
        // Ocultar todos primero
        items.forEach(item => {
            item.style.display = 'none';
        });
        
        // Mostrar solo los 4 elementos actuales
        for (let i = 0; i < visibleItems; i++) {
            const index = (currentPosition + i) % totalItems;
            items[index].style.display = 'block';
        }
    }
    
    // Configurar botón siguiente
    nextBtn.addEventListener('click', function() {
        currentPosition = (currentPosition + 1) % totalItems;
        updateVisibleItems();
    });
    
    // Configurar botón anterior
    prevBtn.addEventListener('click', function() {
        currentPosition = (currentPosition - 1 + totalItems) % totalItems;
        updateVisibleItems();
    });
    
    // Inicializar
    updateVisibleItems();
}

// Forzar repintado del banner al cargar
window.addEventListener('resize', function() {
    const banner1 = document.getElementById('carousel-1');
    if (banner1) {
        banner1.style.display = 'none';
        setTimeout(() => {
            banner1.style.display = '';
        }, 0);
    }
});

// Forzar repintado del banner principal en caso de renderizado inestable
function stabilizeBannerRendering() {
    const banner1 = document.getElementById('carousel-1');
    if (!banner1) return;

    const observer = new ResizeObserver(() => {
        banner1.style.display = 'none';
        setTimeout(() => {
            banner1.style.display = '';
        }, 50);
    });

    observer.observe(banner1);
}

document.addEventListener('DOMContentLoaded', stabilizeBannerRendering);}

function fixBannerRendering() {
    const banner1 = document.getElementById('carousel-1');
    if (!banner1) return;

    // Forzar repintado
    banner1.style.display = 'none';
    requestAnimationFrame(() => {
        banner1.style.display = '';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('resize', fixBannerRendering);
});

// Al final del archivo, agrega esta función para manejar los carruseles en móviles
function adjustCarouselsForMobile() {
    if (window.innerWidth <= 768) {
        // Ajustar carruseles de productos para móviles
        const carousel8 = document.getElementById('carousel-8');
        const carousel9 = document.getElementById('carousel-9');
        
        if (carousel8 && carousel9) {
            const products8 = carousel8.querySelectorAll('.product-card-wrapper');
            const products9 = carousel9.querySelectorAll('.product-card-wrapper');
            
            // Mostrar solo 2 productos a la vez en móviles
            products8.forEach((product, index) => {
                product.style.display = index < 2 ? 'block' : 'none';
            });
            
            products9.forEach((product, index) => {
                product.style.display = index < 2 ? 'block' : 'none';
            });
        }
    }
}

// Añadir al evento DOMContentLoaded y resize
document.addEventListener('DOMContentLoaded', function() {
    // ... código existente ...
    adjustCarouselsForMobile();
});

window.addEventListener('resize', adjustCarouselsForMobile);