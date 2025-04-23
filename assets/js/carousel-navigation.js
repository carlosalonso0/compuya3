document.addEventListener('DOMContentLoaded', function() {
    // Configurar navegación para carruseles 8 y 9
    setupCarouselNav('carousel-8');
    setupCarouselNav('carousel-9');
    
    function setupCarouselNav(carouselId) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const prevBtn = carousel.querySelector('.carousel-control.prev');
        const nextBtn = carousel.querySelector('.carousel-control.next');
        const productContainer = carousel.querySelector('.product-carousel');
        
        if (!prevBtn || !nextBtn || !productContainer) return;
        
        const items = productContainer.querySelectorAll('.product-card-wrapper');
        if (items.length <= 4) return;
        
        // Ocultar todos menos los primeros 4
        for (let i = 4; i < items.length; i++) {
            items[i].style.display = 'none';
        }
        
        let currentIndex = 0;
        
        nextBtn.addEventListener('click', function() {
            items[currentIndex].style.display = 'none';
            const nextVisibleIndex = (currentIndex + 4) % items.length;
            items[nextVisibleIndex].style.display = '';
            currentIndex = (currentIndex + 1) % items.length;
        });
        
        prevBtn.addEventListener('click', function() {
            const prevIndex = (currentIndex - 1 + items.length) % items.length;
            const lastVisibleIndex = (currentIndex + 3) % items.length;
            items[lastVisibleIndex].style.display = 'none';
            items[prevIndex].style.display = '';
            currentIndex = prevIndex;
        });
    }
});