// Script para corregir las tarjetas de productos
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que todos los elementos carguen completamente
    setTimeout(function() {
        // Obtener todos los carruseles
        const carousel8 = document.getElementById('carousel-8');
        const carousel9 = document.getElementById('carousel-9');
        
        if (carousel8 && carousel9) {
            // Asegurar estructura de grid
            const carousel8Container = carousel8.querySelector('.product-carousel');
            const carousel9Container = carousel9.querySelector('.product-carousel');
            
            if (carousel8Container && carousel9Container) {
                // Aplicar estilos directamente a los contenedores
                carousel8Container.style.display = 'grid';
                carousel8Container.style.gridTemplateColumns = 'repeat(4, 1fr)';
                carousel8Container.style.gap = '15px';
                carousel8Container.style.width = '100%';
                
                carousel9Container.style.display = 'grid';
                carousel9Container.style.gridTemplateColumns = 'repeat(4, 1fr)';
                carousel9Container.style.gap = '15px';
                carousel9Container.style.width = '100%';
                
                // Ajustar cada tarjeta individualmente
                const cards8 = carousel8Container.querySelectorAll('.product-card');
                const cards9 = carousel9Container.querySelectorAll('.product-card');
                
                // Función para ajustar tarjetas
                function adjustCards(cards) {
                    cards.forEach(card => {
                        // Establecer altura fija
                        card.style.height = '380px';
                        card.style.width = '100%';
                        card.style.boxSizing = 'border-box';
                        
                        // Ajustar elementos internos
                        const imageContainer = card.querySelector('.product-image-container');
                        if (imageContainer) {
                            imageContainer.style.height = '120px';
                        }
                        
                        const productName = card.querySelector('.product-name');
                        if (productName) {
                            productName.style.height = '40px';
                            productName.style.overflow = 'hidden';
                        }
                        
                        // Ajustar información
                        const productInfo = card.querySelector('.product-info');
                        if (productInfo) {
                            productInfo.style.display = 'flex';
                            productInfo.style.flexDirection = 'column';
                            productInfo.style.flex = '1';
                        }
                        
                        // Ajustar botón
                        const button = card.querySelector('.btn-add-cart');
                        if (button) {
                            button.style.marginTop = 'auto';
                        }
                    });
                }
                
                // Aplicar ajustes
                adjustCards(cards8);
                adjustCards(cards9);
            }
        }
    }, 500); // Pequeño retraso para asegurar que todo está cargado
});