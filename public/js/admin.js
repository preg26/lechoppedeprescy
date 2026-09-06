// Admin JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success messages after 5 seconds
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Confirm delete actions
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });

    // Initialize drag & drop for existing images
    initImageDragDrop();
});

// Drag & Drop functionality for all images (existing and new)
function initImageDragDrop() {
    const container = document.getElementById('images-container');
    if (!container) return;

    let draggedElement = null;
    let placeholder = null;

    // Create placeholder element
    function createPlaceholder() {
        const div = document.createElement('div');
        div.className = 'image-item drag-placeholder';
        div.innerHTML = `
            <div class="image-preview" style="background: linear-gradient(135deg, #C9A961 0%, #27715A 100%); display: flex; align-items: center; justify-content: center; border: 3px dashed white;">
                <span style="color: white; font-size: 2rem; font-weight: bold;">↓</span>
            </div>
        `;
        
        // Ajouter les événements pour permettre le drop sur le placeholder
        div.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });
        
        div.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (draggedElement && this.parentNode) {
                this.parentNode.insertBefore(draggedElement, this);
            }
            
            return false;
        });
        
        return div;
    }

    function setupDragDrop(item) {
        // Éviter d'ajouter plusieurs fois les listeners
        if (item.hasAttribute('data-drag-initialized')) {
            return item;
        }
        item.setAttribute('data-drag-initialized', 'true');
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', 'drag');
            
            // Create and insert placeholder
            setTimeout(() => {
                if (!placeholder) {
                    placeholder = createPlaceholder();
                    if (this.parentNode) {
                        this.parentNode.insertBefore(placeholder, this);
                    }
                }
                this.style.opacity = '0.3';
            }, 10);
        });

        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            this.style.opacity = '';
            
            // Remove placeholder
            if (placeholder && placeholder.parentNode) {
                placeholder.parentNode.removeChild(placeholder);
                placeholder = null;
            }
            
            draggedElement = null;
            updateImageOrder();
        });

        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            if (this === placeholder || this === draggedElement || !placeholder) {
                return;
            }
            
            // Obtenir tous les éléments pour calculer les positions
            const allItems = Array.from(container.querySelectorAll('.image-item:not(.drag-placeholder)'));
            const draggedIndex = allItems.indexOf(draggedElement);
            const targetIndex = allItems.indexOf(this);
            
            // Calculate position
            const rect = this.getBoundingClientRect();
            const midpoint = rect.left + rect.width / 2;
            const insertBefore = e.clientX < midpoint;
            
            // Empêcher uniquement le placeholder d'apparaître exactement à la même position
            // (juste avant l'élément suivant ou juste après l'élément précédent)
            if (targetIndex === draggedIndex + 1 && insertBefore) {
                // On essaie de placer avant l'élément qui suit directement = même position
                return;
            }
            if (targetIndex === draggedIndex - 1 && !insertBefore) {
                // On essaie de placer après l'élément qui précède directement = même position
                return;
            }
            
            if (insertBefore) {
                if (this.previousElementSibling !== placeholder) {
                    this.parentNode.insertBefore(placeholder, this);
                }
            } else {
                if (this.nextElementSibling !== placeholder) {
                    this.parentNode.insertBefore(placeholder, this.nextElementSibling);
                }
            }
        });

        item.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (draggedElement && placeholder && placeholder.parentNode) {
                placeholder.parentNode.insertBefore(draggedElement, placeholder);
            }
            
            return false;
        });
        
        return item;
    }

    // Setup drag & drop for all existing items
    const items = Array.from(container.querySelectorAll('.image-item:not(.drag-placeholder)'));
    items.forEach(item => setupDragDrop(item));
    
    // Permettre de déposer sur le container lui-même (pour revenir à la position d'origine)
    container.addEventListener('dragover', function(e) {
        if (!draggedElement || !placeholder) return;
        
        // Si on est sur une zone vide du container, replacer le placeholder à la position d'origine
        const rect = container.getBoundingClientRect();
        if (e.target === container || e.target.classList.contains('images-sortable')) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }
    });
    
    container.addEventListener('drop', function(e) {
        if (!draggedElement || !placeholder) return;
        
        if (e.target === container || e.target.classList.contains('images-sortable')) {
            e.preventDefault();
            e.stopPropagation();
            
            // Replacer l'élément à sa position d'origine (là où est le placeholder)
            if (placeholder && placeholder.parentNode) {
                placeholder.parentNode.insertBefore(draggedElement, placeholder);
            }
            
            return false;
        }
    });
    
    // Store reference for later use
    container.setupDragDrop = setupDragDrop;
}

// Update hidden input with new image order
function updateImageOrder() {
    const container = document.getElementById('images-container');
    const orderInput = document.getElementById('image_order');
    
    if (!container || !orderInput) return;

    const items = container.querySelectorAll('.image-item:not(.drag-placeholder)');
    const order = [];
    
    items.forEach((item, index) => {
        const imageName = item.getAttribute('data-image');
        const isNew = item.getAttribute('data-type') === 'new';
        const fileIndex = item.getAttribute('data-file-index');
        
        if (imageName) {
            // Image existante
            order.push({ type: 'existing', name: imageName });
        } else if (isNew && fileIndex !== null) {
            // Nouvelle image - on stocke son index dans le FileList
            order.push({ type: 'new', index: parseInt(fileIndex) });
        }
    });

    orderInput.value = JSON.stringify(order);
}

// Add new images to the main gallery with NEW badge
function addNewImagesToGallery(event) {
    const files = event.target.files;
    const container = document.getElementById('images-container');
    
    if (!files || files.length === 0) return;

    // Remove "no images" message if present
    const noImagesMsg = document.getElementById('no-images-message');
    if (noImagesMsg) {
        noImagesMsg.remove();
    }

    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-item';
            div.setAttribute('data-type', 'new');
            div.setAttribute('data-file-index', index);
            
            div.innerHTML = `
                <div class="image-preview">
                    <img src="${e.target.result}" alt="Preview">
                    <div class="image-overlay">
                        <span class="drag-handle">⋮⋮</span>
                    </div>
                </div>
                <div class="new-badge">NEW</div>
            `;
            
            container.appendChild(div);
            
            // Setup drag & drop for this new item
            if (container.setupDragDrop) {
                container.setupDragDrop(div);
            }
        };
        
        reader.readAsDataURL(file);
    });
}
