/**
 * Scripts globales del sitio
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar menú móvil
    setupMobileMenu();
    
    // Inicializar botones de añadir al carrito
    setupAddToCartButtons();
    
    // Inicializar contador de tiempo para ofertas estáticas
    setupOfferCountdowns();
});

/**
 * Configurar menú móvil
 */
function setupMobileMenu() {
    // Crear botón de hamburguesa para móvil si no existe
    if (!document.querySelector('.mobile-menu-toggle')) {
        const nav = document.querySelector('.main-nav');
        if (nav) {
            const toggle = document.createElement('button');
            toggle.className = 'mobile-menu-toggle';
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
            nav.insertBefore(toggle, nav.firstChild);
            
            // Agregar evento de click
            toggle.addEventListener('click', function() {
                document.querySelector('.nav-menu').classList.toggle('active');
            });
        }
    }
}

/**
 * Configurar botones de añadir al carrito
 */
function setupAddToCartButtons() {
    const addToCartButtons = document.querySelectorAll('.btn-add-cart:not(.disabled)');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!button.hasAttribute('href')) return;
            
            e.preventDefault();
            const productId = new URL(button.getAttribute('href')).searchParams.get('id');
            
            // Animar el botón
            button.innerHTML = '<i class="fas fa-check"></i> Añadido';
            button.classList.add('added');
            
            // Simular añadir al carrito (en producción aquí iría una llamada AJAX)
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-shopping-cart"></i> Añadir al carrito';
                button.classList.remove('added');
            }, 2000);
            
            // Aquí iría la lógica real para añadir al carrito
            console.log('Producto añadido al carrito:', productId);
        });
    });
}

/**
 * Configurar contadores de tiempo para ofertas estáticas
 */
function setupOfferCountdowns() {
    const offerTimers = document.querySelectorAll('.static-offer-timer');
    
    offerTimers.forEach(timer => {
        // En este ejemplo, no actualizamos el tiempo real, solo se muestra lo que viene del servidor
        // En una implementación real, se necesitaría obtener la fecha de fin de la oferta
        
        // Para simular una cuenta regresiva, podríamos hacer algo como:
        /*
        const endTime = timer.dataset.endTime; // Vendría del servidor
        if (!endTime) return;
        
        const updateTimer = setInterval(() => {
            const now = new Date().getTime();
            const end = new Date(endTime).getTime();
            const distance = end - now;
            
            if (distance < 0) {
                clearInterval(updateTimer);
                timer.textContent = "Oferta finalizada";
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            
            timer.textContent = `Termina en: ${days}d ${hours}h ${minutes}m`;
        }, 60000); // Actualizar cada minuto
        */
    });
}

/**
 * Funciones utilitarias
 */

// Formato de número con comas para miles
function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Obtener parámetro de URL
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

// Manejar errores de carga de imágenes
document.addEventListener('error', function(e) {
    if (e.target.tagName.toLowerCase() === 'img') {
        e.target.src = '/assets/images/site/no-image.webp';
    }
}, true);