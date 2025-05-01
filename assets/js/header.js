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
                    
                    // Si es un submenu-item, manejo especial
                    if (item.classList.contains('new-submenu-item')) {
                        // Evitar cerrar el menú principal
                        e.stopPropagation();
                    }
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
    
    // Manejo específico para submenu-item (Componentes)
    const submenuItems = document.querySelectorAll('.new-submenu-item.new-has-submenu');
    submenuItems.forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            // Evento separado para móviles
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation(); // Importante para que no se propague al menú padre
                    item.classList.toggle('active');
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