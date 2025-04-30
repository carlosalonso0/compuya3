/**
 * Funcionalidad del nuevo header
 */
document.addEventListener('DOMContentLoaded', function() {
    // Menú móvil toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mainMenu = document.querySelector('.main-menu-items');
    
    if (menuToggle && mainMenu) {
        menuToggle.addEventListener('click', function() {
            mainMenu.classList.toggle('active');
        });
    }
    
    // Manejo de submenús en móvil
    const hasSubmenu = document.querySelectorAll('.has-submenu');
    hasSubmenu.forEach(item => {
        const link = item.querySelector('a');
        if (link && window.innerWidth <= 768) {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
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
        
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
        }
    }
    
    // Inicializar contador
    updateCartCount();
});