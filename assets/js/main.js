// ===== FUNCIONES GENERALES =====

// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Función para formatear moneda
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2
    }).format(amount);
}

// Función para mostrar mensajes
function showMessage(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar al inicio del contenedor principal
    const container = document.querySelector('.container') || document.querySelector('main');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

// Validar formularios
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Cargar categorías (ejemplo para filtros)
async function loadCategories() {
    try {
        const response = await fetch('api/get_categories.php');
        const categories = await response.json();
        
        const select = document.getElementById('category-filter');
        if (select) {
            select.innerHTML = '<option value="">Todas las categorías</option>';
            categories.forEach(cat => {
                select.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
            });
        }
    } catch (error) {
        console.error('Error cargando categorías:', error);
    }
}

// Manejar favoritos
function toggleFavorite(productId) {
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    
    if (favorites.includes(productId)) {
        favorites = favorites.filter(id => id !== productId);
        showMessage('info', 'Producto removido de favoritos');
    } else {
        favorites.push(productId);
        showMessage('success', 'Producto agregado a favoritos');
    }
    
    localStorage.setItem('favorites', JSON.stringify(favorites));
    updateFavoriteButton(productId);
}

function updateFavoriteButton(productId) {
    const btn = document.querySelector(`.favorite-btn[data-id="${productId}"]`);
    if (btn) {
        const favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        const isFavorite = favorites.includes(productId.toString());
        
        btn.innerHTML = isFavorite ? 
            '<i class="bi bi-heart-fill text-danger"></i>' : 
            '<i class="bi bi-heart"></i>';
    }
}

// Función para confirmar eliminación
function confirmDelete(message = '¿Está seguro de eliminar este elemento?') {
    return confirm(message);
}

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    // Cargar categorías si existe el filtro
    if (document.getElementById('category-filter')) {
        loadCategories();
    }
    
    // Actualizar botones de favoritos
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        const productId = btn.getAttribute('data-id');
        updateFavoriteButton(productId);
        
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleFavorite(productId);
        });
    });
    
    // Validar formularios al enviar
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const formId = this.id || 'form-' + Math.random().toString(36).substr(2, 9);
            this.id = formId;
            
            if (!validateForm(formId)) {
                e.preventDefault();
                showMessage('danger', 'Por favor complete todos los campos requeridos');
            }
        });
    });
});