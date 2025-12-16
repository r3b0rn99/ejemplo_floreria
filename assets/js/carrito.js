// ===== FUNCIONES DEL CARRITO =====

class Carrito {
    constructor() {
        this.items = this.getCarritoFromStorage();
        this.updateCarritoCount();
    }
    
    // Obtener carrito del localStorage
    getCarritoFromStorage() {
        const carrito = localStorage.getItem('carrito');
        return carrito ? JSON.parse(carrito) : [];
    }
    
    // Guardar carrito en localStorage
    saveToStorage() {
        localStorage.setItem('carrito', JSON.stringify(this.items));
        this.updateCarritoCount();
    }
    
    // Actualizar contador del carrito
    updateCarritoCount() {
        const count = this.items.reduce((total, item) => total + item.cantidad, 0);
        document.querySelectorAll('.carrito-count').forEach(el => {
            el.textContent = count;
            el.style.display = count > 0 ? 'inline' : 'none';
        });
        return count;
    }
    
    // Agregar producto al carrito
    agregarProducto(productoId, nombre, precio, imagen, cantidad = 1) {
        const productoExistente = this.items.find(item => item.id === productoId);
        
        if (productoExistente) {
            productoExistente.cantidad += cantidad;
        } else {
            this.items.push({
                id: productoId,
                nombre: nombre,
                precio: precio,
                imagen: imagen,
                cantidad: cantidad
            });
        }
        
        this.saveToStorage();
        this.mostrarNotificacion('Producto agregado al carrito');
        return true;
    }
    
    // Eliminar producto del carrito
    eliminarProducto(productoId) {
        this.items = this.items.filter(item => item.id !== productoId);
        this.saveToStorage();
        this.mostrarNotificacion('Producto eliminado del carrito');
    }
    
    // Actualizar cantidad
    actualizarCantidad(productoId, cantidad) {
        const producto = this.items.find(item => item.id === productoId);
        if (producto) {
            if (cantidad <= 0) {
                this.eliminarProducto(productoId);
            } else {
                producto.cantidad = cantidad;
                this.saveToStorage();
            }
        }
    }
    
    // Vaciar carrito
    vaciarCarrito() {
        this.items = [];
        this.saveToStorage();
        this.mostrarNotificacion('Carrito vaciado');
    }
    
    // Calcular total
    calcularTotal() {
        return this.items.reduce((total, item) => total + (item.precio * item.cantidad), 0);
    }
    
    // Mostrar notificación
    mostrarNotificacion(mensaje) {
        // Crear notificación
        const notification = document.createElement('div');
        notification.className = 'alert alert-success carrito-notification';
        notification.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>
            ${mensaje}
        `;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        `;
        
        document.body.appendChild(notification);
        
        // Eliminar después de 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Renderizar carrito en la página
    renderCarrito() {
        const container = document.getElementById('carrito-container');
        if (!container) return;
        
        if (this.items.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h4 class="mt-3">Tu carrito está vacío</h4>
                    <p class="text-muted">Agrega algunos productos para comenzar</p>
                    <a href="productos.php" class="btn btn-success mt-2">Ver Productos</a>
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        this.items.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            html += `
                <tr id="carrito-item-${item.id}">
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="assets/uploads/productos/${item.imagen}" 
                                 alt="${item.nombre}" 
                                 class="img-thumbnail me-3" 
                                 style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0">${item.nombre}</h6>
                            </div>
                        </div>
                    </td>
                    <td>S/. ${item.precio.toFixed(2)}</td>
                    <td>
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <button class="btn btn-outline-secondary btn-minus" 
                                    type="button" 
                                    data-id="${item.id}">-</button>
                            <input type="number" 
                                   class="form-control text-center cantidad-input" 
                                   value="${item.cantidad}" 
                                   min="1" 
                                   max="99"
                                   data-id="${item.id}">
                            <button class="btn btn-outline-secondary btn-plus" 
                                    type="button" 
                                    data-id="${item.id}">+</button>
                        </div>
                    </td>
                    <td>S/. ${subtotal.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger btn-eliminar" 
                                data-id="${item.id}"
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        const total = this.calcularTotal();
        const envio = total > 100 ? 0 : 15; // Envío gratis sobre S/. 100
        
        html += `
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <a href="productos.php" class="btn btn-outline-success">
                        <i class="bi bi-arrow-left"></i> Continuar Comprando
                    </a>
                    <button class="btn btn-danger ms-2" id="vaciar-carrito">
                        <i class="bi bi-trash"></i> Vaciar Carrito
                    </button>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Resumen del Pedido</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td>Subtotal:</td>
                                    <td class="text-end">S/. ${total.toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td>Envío:</td>
                                    <td class="text-end">${envio === 0 ? 
                                        '<span class="text-success">Gratis</span>' : 
                                        'S/. ' + envio.toFixed(2)}</td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-end"><strong>S/. ${(total + envio).toFixed(2)}</strong></td>
                                </tr>
                            </table>
                            <div class="d-grid">
                                <a href="checkout.php" class="btn btn-success btn-lg">
                                    Proceder al Pago <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
        this.setupEventListeners();
    }
    
    // Configurar eventos del carrito
    setupEventListeners() {
        // Botones de aumentar/disminuir cantidad
        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('button').getAttribute('data-id');
                const producto = this.items.find(item => item.id.toString() === id);
                if (producto) {
                    this.actualizarCantidad(producto.id, producto.cantidad + 1);
                    this.renderCarrito();
                }
            });
        });
        
        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('button').getAttribute('data-id');
                const producto = this.items.find(item => item.id.toString() === id);
                if (producto && producto.cantidad > 1) {
                    this.actualizarCantidad(producto.id, producto.cantidad - 1);
                    this.renderCarrito();
                }
            });
        });
        
        // Input de cantidad
        document.querySelectorAll('.cantidad-input').forEach(input => {
            input.addEventListener('change', (e) => {
                const id = e.target.getAttribute('data-id');
                const cantidad = parseInt(e.target.value);
                if (!isNaN(cantidad) && cantidad > 0) {
                    this.actualizarCantidad(id, cantidad);
                    this.renderCarrito();
                }
            });
        });
        
        // Botón eliminar
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('button').getAttribute('data-id');
                if (confirm('¿Está seguro de eliminar este producto del carrito?')) {
                    this.eliminarProducto(id);
                    this.renderCarrito();
                }
            });
        });
        
        // Botón vaciar carrito
        const vaciarBtn = document.getElementById('vaciar-carrito');
        if (vaciarBtn) {
            vaciarBtn.addEventListener('click', () => {
                if (confirm('¿Está seguro de vaciar todo el carrito?')) {
                    this.vaciarCarrito();
                    this.renderCarrito();
                }
            });
        }
    }
}

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    const carrito = new Carrito();
    
    // Renderizar carrito si estamos en la página del carrito
    if (document.getElementById('carrito-container')) {
        carrito.renderCarrito();
    }
    
    // Manejar agregar al carrito desde botones
    document.querySelectorAll('.btn-agregar-carrito').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productoId = this.getAttribute('data-id');
            const productoNombre = this.getAttribute('data-nombre');
            const productoPrecio = parseFloat(this.getAttribute('data-precio'));
            const productoImagen = this.getAttribute('data-imagen');
            const cantidad = parseInt(this.getAttribute('data-cantidad') || 1);
            
            carrito.agregarProducto(
                productoId,
                productoNombre,
                productoPrecio,
                productoImagen,
                cantidad
            );
            
            // Actualizar botón temporalmente
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check"></i> Agregado';
            this.classList.remove('btn-success');
            this.classList.add('btn-secondary');
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = originalHTML;
                this.classList.remove('btn-secondary');
                this.classList.add('btn-success');
                this.disabled = false;
            }, 2000);
        });
    });
});

// Estilos CSS para las animaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .carrito-notification {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
`;
document.head.appendChild(style);