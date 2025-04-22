/**
 * Scripts para el panel de administración
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar menú lateral
    setupSidebar();
    
    // Inicializar tablas de datos
    setupDataTables();
    
    // Inicializar carga de imágenes
    setupImageUploads();
    
    // Inicializar ordenamiento de elementos
    setupSortableItems();
});

/**
 * Configurar menú lateral
 */
function setupSidebar() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Cerrar sidebar al hacer clic fuera en móviles
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                e.target !== sidebarToggle) {
                sidebar.classList.remove('active');
            }
        });
    }
}

/**
 * Configurar tablas de datos
 */
function setupDataTables() {
    const tables = document.querySelectorAll('.admin-table');
    
    tables.forEach(table => {
        // Aquí iría la inicialización de DataTables o funcionalidad similar
        // En esta versión simplificada, solo añadimos cebrado a filas
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            if (index % 2 === 1) {
                row.style.backgroundColor = '#f9f9f9';
            }
        });
    });
}

/**
 * Configurar carga de imágenes
 */
function setupImageUploads() {
    const imageUploaders = document.querySelectorAll('.image-uploader');
    
    imageUploaders.forEach(uploader => {
        const input = uploader.querySelector('input[type="file"]');
        const preview = uploader.querySelector('.image-preview');
        
        if (input && preview) {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
}

/**
 * Configurar ordenamiento de elementos en carruseles
 */
function setupSortableItems() {
    const sortableLists = document.querySelectorAll('.carousel-items');
    
    sortableLists.forEach(list => {
        // Aquí iría la lógica para hacer los elementos arrastrables/ordenables
        // Usando una librería como SortableJS
        // Por simplicidad, no implementamos la funcionalidad completa en este ejemplo
        
        // Simulamos actualización de orden al hacer clic en flechas arriba/abajo
        const moveButtons = list.querySelectorAll('.btn-move-item');
        
        moveButtons.forEach(button => {
            button.addEventListener('click', function() {
                const direction = this.dataset.direction;
                const item = this.closest('.carousel-item');
                
                if (direction === 'up' && item.previousElementSibling) {
                    list.insertBefore(item, item.previousElementSibling);
                } else if (direction === 'down' && item.nextElementSibling) {
                    list.insertBefore(item.nextElementSibling, item);
                }
                
                // En implementación real, aquí enviaríamos el nuevo orden al servidor
            });
        });
    });
}

/**
 * Mostrar mensaje de confirmación
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Mostrar mensaje de alerta
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = message;
    
    // Insertar al principio del contenido principal
    const mainContent = document.querySelector('.admin-main');
    if (mainContent) {
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

/**
 * Función para enviar solicitudes AJAX
 */
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            const response = JSON.parse(xhr.responseText);
            if (successCallback) successCallback(response);
        } else {
            if (errorCallback) errorCallback(xhr.statusText);
        }
    };
    
    xhr.onerror = function() {
        if (errorCallback) errorCallback('Network error');
    };
    
    xhr.send(JSON.stringify(data));
}