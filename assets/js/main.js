/**
 * Scripts globales del sitio
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar menú móvil
    setupMobileMenu();
    
    // Inicializar botones de añadir al carrito (unificado)
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
 * Configurar botones de añadir al carrito (unificado)
 */
function setupAddToCartButtons() {
    function updateCartCountAjax() {
        fetch('/carrito.php?action=count')
            .then(res => res.json())
            .then(data => {
                const cartCountElement = document.querySelector('.new-cart-count');
                if (cartCountElement && data.count !== undefined) {
                    cartCountElement.textContent = data.count;
                }
            });
    }

    document.body.addEventListener('click', function(e) {
        // Soporta tanto <a> como <button> con .btn-add-cart
        const btn = e.target.closest('.btn-add-cart:not(.disabled)');
        if (!btn) return;
        e.preventDefault();

        let productId = btn.getAttribute('data-product-id');
        if (!productId) {
            // Si no tiene data-product-id, intenta extraerlo del href (para <a>)
            const href = btn.getAttribute('href');
            if (href && href.includes('id=')) {
                try {
                    const url = new URL(href, window.location.origin);
                    productId = url.searchParams.get('id');
                } catch (err) {}
            }
        }
        if (!productId) return;

        // Cantidad (si hay input de cantidad cerca)
        let quantity = 1;
        let qtyInput = btn.closest('.product-card, .product-actions, .product-info')?.querySelector('input[type="number"]');
        if (!qtyInput) {
            // Busca en el documento si es página de producto
            qtyInput = document.getElementById('product-quantity');
        }
        if (qtyInput) {
            quantity = parseInt(qtyInput.value) || 1;
        }

        fetch(`/carrito.php?action=add&id=${productId}&cantidad=${quantity}`)
            .then(res => res.json())
            .then(data => {
                updateCartCountAjax();
                if (data.success) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Añadido';
                    btn.classList.add('added');
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Añadir al carrito';
                        btn.classList.remove('added');
                    }, 2000);
                } else {
                    alert(data.message || 'No se pudo añadir al carrito');
                }
            });
    });

    updateCartCountAjax();
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