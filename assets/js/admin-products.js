/**
 * Script para la gestión de productos en el panel de administración
 */
document.addEventListener('DOMContentLoaded', function() {
    // ======= GESTIÓN DE PESTAÑAS =======
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remover clase activa de todos los botones y contenidos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Agregar clase activa al botón y contenido seleccionado
            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.add('active');
        });
    });
    
    // ======= GESTIÓN DE ESPECIFICACIONES =======
    
    // Función para añadir una nueva especificación
    function addNewSpecification(nombre = '', valor = '') {
        const specsList = document.getElementById('specs-list');
        const specCount = specsList.children.length;
        
        const specRow = document.createElement('div');
        specRow.className = 'spec-row';
        
        // Generar un ID único para evitar colisiones en nombres de especificaciones
        const specId = 'spec_' + Date.now() + '_' + specCount;
        
        specRow.innerHTML = `
            <div class="spec-input-group">
                <input type="text" name="especificaciones[${nombre}]" placeholder="Nombre de la especificación" value="${nombre}">
                <input type="text" name="especificaciones[${nombre}]" placeholder="Valor de la especificación" value="${valor}">
                <button type="button" class="btn-remove-spec">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Agregar al listado de especificaciones
        specsList.appendChild(specRow);
        
        // Configurar evento para eliminar especificación
        specRow.querySelector('.btn-remove-spec').addEventListener('click', function() {
            specRow.remove();
        });
        
        // Configurar eventos para actualizar el nombre en ambos campos
        const nombreInput = specRow.querySelector('input:first-child');
        const valorInput = specRow.querySelector('input:last-of-type');
        
        nombreInput.addEventListener('input', function() {
            valorInput.name = `especificaciones[${this.value}]`;
        });
    }
    
    // Botón para añadir una nueva especificación manual
    const btnAddSpec = document.querySelector('.btn-add-spec');
    if (btnAddSpec) {
        btnAddSpec.addEventListener('click', function() {
            addNewSpecification();
        });
    }
    
    // Toggle para mostrar/ocultar las especificaciones recomendadas
    const categoriaSelect = document.getElementById('categoria_id');
    const specSuggestionsContainer = document.querySelector('.spec-suggestions-container');
    const specSuggestions = document.querySelector('.spec-suggestions');
    
    if (categoriaSelect && specSuggestionsContainer && specSuggestions) {
        categoriaSelect.addEventListener('change', function() {
            const categoriaId = this.value;
            
            // Si hay una categoría seleccionada, mostrar sugerencias
            if (categoriaId) {
                // Limpiar sugerencias anteriores
                specSuggestions.innerHTML = '';
                
                // Obtener especificaciones recomendadas para la categoría desde el objeto en PHP
                // Este objeto se debe definir en la página PHP que incluye este script
                if (typeof especificacionesRecomendadas !== 'undefined' && 
                    especificacionesRecomendadas[categoriaId]) {
                    
                    // Verificar especificaciones existentes para no mostrarlas como sugerencias
                    const especsExistentes = {};
                    document.querySelectorAll('#specs-list input:first-child').forEach(input => {
                        if (input.value.trim() !== '') {
                            especsExistentes[input.value.trim()] = true;
                        }
                    });
                    
                    // Crear elementos para cada especificación recomendada
                    let countSuggestions = 0;
                    Object.entries(especificacionesRecomendadas[categoriaId]).forEach(([nombre, valor]) => {
                        // Omitir si ya existe esta especificación
                        if (especsExistentes[nombre]) return;
                        
                        countSuggestions++;
                        const suggestionItem = document.createElement('div');
                        suggestionItem.className = 'spec-suggestion-item';
                        suggestionItem.innerHTML = `
                            <span class="spec-name">${nombre}</span>
                            <span class="spec-value">${valor}</span>
                            <button type="button" class="btn-add-suggestion" data-name="${nombre}" data-value="${valor}">
                                <i class="fas fa-plus"></i>
                            </button>
                        `;
                        specSuggestions.appendChild(suggestionItem);
                    });
                    
                    // Solo mostrar si hay sugerencias disponibles
                    if (countSuggestions > 0) {
                        // Mostrar el contenedor de sugerencias
                        specSuggestionsContainer.style.display = 'block';
                        
                        // Configurar eventos para añadir sugerencias
                        const addButtons = specSuggestions.querySelectorAll('.btn-add-suggestion');
                        addButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                const nombreEspec = this.dataset.name;
                                const valorEspec = this.dataset.value;
                                
                                addNewSpecification(nombreEspec, valorEspec);
                                
                                // Marcar la sugerencia como añadida
                                this.closest('.spec-suggestion-item').classList.add('added');
                                this.disabled = true;
                            });
                        });
                        
                        // Botón para añadir todas las recomendadas
                        const addAllButton = document.querySelector('.btn-add-all-specs');
                        if (addAllButton) {
                            addAllButton.addEventListener('click', function() {
                                specSuggestions.querySelectorAll('.btn-add-suggestion:not([disabled])').forEach(button => {
                                    button.click();
                                });
                            });
                        }
                    } else {
                        // Ocultar contenedor si no hay sugerencias disponibles
                        specSuggestionsContainer.style.display = 'none';
                    }
                } else {
                    // Ocultar contenedor si no hay recomendaciones para esta categoría
                    specSuggestionsContainer.style.display = 'none';
                }
            } else {
                // Ocultar contenedor si no hay categoría seleccionada
                specSuggestionsContainer.style.display = 'none';
            }
        });
    }
    
    // ======= GESTIÓN DE IMÁGENES =======
    
    // Previsualización de imágenes
    const imagenPrincipal = document.getElementById('imagen_principal');
    const principalPreview = document.getElementById('imagen-principal-preview');
    
    if (imagenPrincipal && principalPreview) {
        imagenPrincipal.addEventListener('change', function() {
            previewImage(this, principalPreview);
        });
    }
    
    const imagenesAdicionales = document.getElementById('imagenes_adicionales');
    const adicionalesPreview = document.getElementById('imagenes-adicionales-preview');
    
    if (imagenesAdicionales && adicionalesPreview) {
        imagenesAdicionales.addEventListener('change', function() {
            previewMultipleImages(this, adicionalesPreview);
        });
    }
    
    // Función para previsualizar una imagen
    function previewImage(input, container) {
        // Mantener imágenes existentes si es el caso de edición
        if (!container.querySelector('.preview-new-image')) {
            // Crear un contenedor específico para la nueva imagen
            const newImageContainer = document.createElement('div');
            newImageContainer.className = 'preview-new-image';
            container.appendChild(newImageContainer);
            container = newImageContainer;
        } else {
            container = container.querySelector('.preview-new-image');
            container.innerHTML = '';
        }
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-img';
                container.innerHTML = '';
                container.appendChild(img);
                
                // Mostrar el contenedor de previsualización
                container.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Función para previsualizar múltiples imágenes
    function previewMultipleImages(input, container) {
        if (input.files) {
            const fileNum = Math.min(input.files.length, 4); // Máximo 4 imágenes
            
            // Crear un contenedor específico para nuevas imágenes
            let newImagesContainer = container.querySelector('.preview-new-images');
            if (!newImagesContainer) {
                newImagesContainer = document.createElement('div');
                newImagesContainer.className = 'preview-new-images';
                container.appendChild(newImagesContainer);
            } else {
                newImagesContainer.innerHTML = '';
            }
            
            for (let i = 0; i < fileNum; i++) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'gallery-img-container';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    imgContainer.appendChild(img);
                    newImagesContainer.appendChild(imgContainer);
                }
                
                reader.readAsDataURL(input.files[i]);
            }
            
            // Mostrar el contenedor de previsualización
            newImagesContainer.style.display = 'flex';
        }
    }
    
    // Drag and drop para imágenes
    const uploadBoxes = document.querySelectorAll('.image-upload-box');
    
    uploadBoxes.forEach(box => {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            box.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            box.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            box.addEventListener(eventName, unhighlight, false);
        });
        
        box.addEventListener('drop', handleDrop, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        this.classList.add('highlight');
    }
    
    function unhighlight() {
        this.classList.remove('highlight');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (this.closest('.form-group').querySelector('input[type="file"]').multiple) {
            // Para imágenes adicionales (múltiples)
            const fileInput = this.closest('.form-group').querySelector('input[type="file"]');
            fileInput.files = files;
            
            // Actualizar la previsualización
            previewMultipleImages(fileInput, adicionalesPreview);
        } else {
            // Para imagen principal (única)
            const fileInput = this.closest('.form-group').querySelector('input[type="file"]');
            
            // Crear un nuevo objeto DataTransfer
            const dataTransfer = new DataTransfer();
            
            // Añadir solo el primer archivo
            if (files.length > 0) {
                dataTransfer.items.add(files[0]);
            }
            
            // Asignar el archivo al input
            fileInput.files = dataTransfer.files;
            
            // Actualizar la previsualización
            previewImage(fileInput, principalPreview);
        }
    }
    
    // ======= VALIDACIONES =======
    
    // Validación de precio oferta
    const precioInput = document.getElementById('precio');
    const precioOfertaInput = document.getElementById('precio_oferta');
    const enOfertaCheckbox = document.getElementById('en_oferta');
    
    if (precioInput && precioOfertaInput && enOfertaCheckbox) {
        function validarPrecioOferta() {
            if (enOfertaCheckbox.checked) {
                const precio = parseFloat(precioInput.value);
                const precioOferta = parseFloat(precioOfertaInput.value);
                
                if (precioOferta >= precio) {
                    precioOfertaInput.setCustomValidity('El precio de oferta debe ser menor que el precio regular');
                } else {
                    precioOfertaInput.setCustomValidity('');
                }
            } else {
                precioOfertaInput.setCustomValidity('');
            }
        }
        
        precioInput.addEventListener('change', validarPrecioOferta);
        precioOfertaInput.addEventListener('change', validarPrecioOferta);
        enOfertaCheckbox.addEventListener('change', validarPrecioOferta);
    }
    
    // Inicializar validación del formulario
    const form = document.getElementById('product-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Se podría añadir validación adicional aquí
            
            // Verificar precio oferta si está en oferta
            if (enOfertaCheckbox && enOfertaCheckbox.checked) {
                validarPrecioOferta();
                if (precioOfertaInput.validity.customError) {
                    e.preventDefault();
                    alert('El precio de oferta debe ser menor que el precio regular.');
                    return false;
                }
            }
        });
    }
    
    // Inicializar sugerencias de especificaciones si estamos en edición
    if (categoriaSelect && categoriaSelect.value) {
        // Disparar el evento change para mostrar sugerencias
        const changeEvent = new Event('change');
        categoriaSelect.dispatchEvent(changeEvent);
    }
    
    // ======= ELIMINACIÓN DE IMÁGENES (SOLO EN EDICIÓN) =======
    
    // Botones para eliminar imágenes existentes
    const btnRemoveImages = document.querySelectorAll('.btn-remove-image');
    
    btnRemoveImages.forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            const container = this.closest('.gallery-img-container');
            
            if (confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
                // Aquí podríamos hacer una petición AJAX para eliminar la imagen
                // O simplemente marcarla para eliminar al enviar el formulario
                
                // Por ahora, simplemente ocultamos la imagen
                container.style.display = 'none';
                
                // Crear un campo hidden para indicar que se debe eliminar esta imagen
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'eliminar_imagen[]';
                hiddenInput.value = imageId;
                form.appendChild(hiddenInput);
            }
        });
    });
});