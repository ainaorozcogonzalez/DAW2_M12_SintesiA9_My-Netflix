document.addEventListener('DOMContentLoaded', function() {
    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo) {
        const alertClass = `alert-${tipo}`;
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        const container = document.querySelector('.admin-container');
        if (container) {
            container.insertAdjacentHTML('afterbegin', alertHtml);
            setTimeout(() => {
                const alertElement = container.querySelector('.alert');
                if (alertElement) {
                    alertElement.remove();
                }
            }, 5000);
        }
    }

    // Función para actualizar la tabla
    async function actualizarTabla() {
        try {
            const response = await fetch('admin_validar_usuarios.php?obtener_tabla=1');
            if (!response.ok) throw new Error('Error al obtener los usuarios');
            const data = await response.json();
            if (data.status === 'success') {
                document.querySelector('tbody').innerHTML = data.html;
                inicializarEventos();
            } else {
                throw new Error(data.message || 'Error al actualizar la tabla');
            }
        } catch (error) {
            mostrarMensaje('Error al actualizar la tabla: ' + error.message, 'danger');
        }
    }

    // Validar usuario
    function inicializarValidar() {
        document.querySelectorAll('.btn-validar').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const userId = this.dataset.id;
                try {
                    const response = await fetch(`admin_validar_usuarios.php?id=${userId}`);
                    if (!response.ok) throw new Error('Error en la respuesta del servidor');
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        mostrarMensaje('Usuario validado correctamente', 'success');
                        await actualizarTabla();
                    } else {
                        mostrarMensaje(data.message || 'Error al validar el usuario', 'danger');
                    }
                } catch (error) {
                    mostrarMensaje('Error al validar el usuario: ' + error.message, 'danger');
                }
            });
        });
    }

    // Eliminar usuario
    function inicializarEliminar() {
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                if(confirm('¿Estás seguro de eliminar este usuario?')) {
                    const userId = this.dataset.id;
                    try {
                        const response = await fetch(`admin_validar_usuarios.php?delete_id=${userId}`);
                        if (!response.ok) throw new Error('Error en la respuesta del servidor');
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            mostrarMensaje('Usuario eliminado correctamente', 'success');
                            await actualizarTabla();
                        } else {
                            mostrarMensaje(data.message || 'Error al eliminar el usuario', 'danger');
                        }
                    } catch (error) {
                        mostrarMensaje('Error al eliminar el usuario: ' + error.message, 'danger');
                    }
                }
            });
        });
    }

    // Inicializar todos los eventos
    function inicializarEventos() {
        inicializarValidar();
        inicializarEliminar();
    }

    // Inicializar eventos al cargar la página
    inicializarEventos();
    actualizarTabla();
});
