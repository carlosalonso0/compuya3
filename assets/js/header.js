/**
 * Funcionalidad del nuevo header
 */
document.addEventListener('DOMContentLoaded', function() {
    // Menú móvil toggle
    const menuToggle = document.querySelector('.new-mobile-menu-toggle');
    const mainMenu = document.querySelector('.new-main-menu-items');
    
    if (menuToggle && mainMenu) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            mainMenu.classList.toggle('active');
        });
    }
    
    // Manejo de submenús en móvil
    const hasSubmenu = document.querySelectorAll('.new-has-submenu');
    hasSubmenu.forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            // Para móviles
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    item.classList.toggle('active');
                }
            });
            
            // Desplegar componentes en hover para escritorio
            item.addEventListener('mouseenter', function() {
                if (window.innerWidth > 768) {
                    this.classList.add('hover');
                }
            });
            
            item.addEventListener('mouseleave', function() {
                if (window.innerWidth > 768) {
                    this.classList.remove('hover');
                }
            });
        }
    });
    
    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (mainMenu && mainMenu.classList.contains('active') && 
            !mainMenu.contains(e.target) && 
            e.target !== menuToggle) {
            mainMenu.classList.remove('active');
        }
    });
    
    // Actualizar contador del carrito
    function updateCartCount() {
        // Aquí podrías hacer una petición AJAX para obtener el número actual
        // Por ahora solo como ejemplo usamos localStorage
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        const count = cartItems.length;
        
        const cartCountElement = document.querySelector('.new-cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
        }
    }
    
    // Inicializar contador
    updateCartCount();
});
