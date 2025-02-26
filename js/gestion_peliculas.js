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
            // Remover alertas existentes
            const alertasExistentes = container.querySelectorAll('.alert');
            alertasExistentes.forEach(alerta => alerta.remove());
            
            // Agregar nueva alerta
            container.insertAdjacentHTML('afterbegin', alertHtml);
            setTimeout(() => {
                const alertElement = container.querySelector('.alert');
                if (alertElement) {
                    alertElement.remove();
                }
            }, 5000);
        }
    }

    // Función para actualizar la tabla con búsqueda
    async function actualizarTabla(searchTerm = '') {
        try {
            const url = searchTerm 
                ? `admin_gestion_peliculas.php?obtener_tabla=1&search=${encodeURIComponent(searchTerm)}`
                : 'admin_gestion_peliculas.php?obtener_tabla=1';
                
            const response = await fetch(url);
            if (!response.ok) throw new Error('Error al obtener el contenido');
            const data = await response.json();
            
            if (data.status === 'success') {
                // Actualizar la tabla
                document.querySelector('tbody').innerHTML = data.html;
                
                // Eliminar modales existentes
                document.querySelectorAll('.modal.fade').forEach(modal => {
                    if (modal.id.startsWith('editarContenidoModal')) {
                        modal.remove();
                    }
                });
                
                // Agregar nuevos modales
                document.body.insertAdjacentHTML('beforeend', data.modales);
                
                // Reinicializar eventos
                inicializarEventos();
            } else {
                throw new Error(data.message || 'Error al actualizar la tabla');
            }
        } catch (error) {
            mostrarMensaje('Error al actualizar la tabla: ' + error.message, 'danger');
        }
    }

    // Crear contenido
    function inicializarCrear() {
        const formCrear = document.querySelector('.form-crear-contenido');
        if (formCrear) {
            // Remover eventos existentes
            formCrear.replaceWith(formCrear.cloneNode(true));
            
            // Obtener la nueva referencia al formulario
            const newFormCrear = document.querySelector('.form-crear-contenido');
            
            newFormCrear.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await fetch('admin_gestion_peliculas.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) throw new Error('Error al crear el contenido');
                    const data = await response.json();

                    if (data.status === 'success') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('crearContenidoModal'));
                        modal.hide();
                        this.reset();
                        await actualizarTabla();
                        mostrarMensaje('Contenido creado correctamente', 'success');
                    } else {
                        mostrarMensaje(data.message || 'Error al crear el contenido', 'danger');
                    }
                } catch (error) {
                    mostrarMensaje('Error al crear el contenido: ' + error.message, 'danger');
                }
            });
        }
    }

    // Editar contenido
    function inicializarEditar() {
        document.querySelectorAll('.form-editar-contenido').forEach(form => {
            // Remover eventos existentes
            const newForm = form.cloneNode(true);
            form.replaceWith(newForm);
            
            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await fetch('admin_gestion_peliculas.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) throw new Error('Error al actualizar el contenido');
                    const data = await response.json();

                    if (data.status === 'success') {
                        const modalId = this.closest('.modal').id;
                        const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                        modal.hide();
                        await actualizarTabla();
                        mostrarMensaje('Contenido actualizado correctamente', 'success');
                    } else {
                        mostrarMensaje(data.message || 'Error al actualizar el contenido', 'danger');
                    }
                } catch (error) {
                    mostrarMensaje(error.message, 'danger');
                }
            });
        });
    }

    // Eliminar contenido
    function inicializarEliminar() {
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            // Remover eventos existentes
            const newBtn = btn.cloneNode(true);
            btn.replaceWith(newBtn);
            
            newBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if(confirm('¿Estás seguro de eliminar este contenido?')) {
                    const contenidoId = this.dataset.id;
                    try {
                        const response = await fetch(`admin_gestion_peliculas.php?delete_id=${contenidoId}`);
                        if (!response.ok) throw new Error('Error en la respuesta del servidor');
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            mostrarMensaje('Contenido eliminado correctamente', 'success');
                            await actualizarTabla();
                        } else {
                            mostrarMensaje(data.message || 'Error al eliminar el contenido', 'danger');
                        }
                    } catch (error) {
                        mostrarMensaje('Error al eliminar el contenido: ' + error.message, 'danger');
                    }
                }
            });
        });
    }

    // Inicializar búsqueda
    function inicializarBusqueda() {
        const formBuscar = document.querySelector('.form-buscar');
        if (formBuscar) {
            formBuscar.addEventListener('submit', async function(e) {
                e.preventDefault();
                const searchTerm = this.querySelector('input[name="search"]').value;
                await actualizarTabla(searchTerm);
            });

            // Búsqueda en tiempo real (opcional)
            const searchInput = document.getElementById('searchInput');
            let timeoutId;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    actualizarTabla(this.value);
                }, 500); // Espera 500ms después de que el usuario deje de escribir
            });
        }
    }

    // Inicializar todos los eventos
    function inicializarEventos() {
        inicializarCrear();
        inicializarEditar();
        inicializarEliminar();
        inicializarBusqueda();
    }

    // Inicializar eventos al cargar la página
    actualizarTabla();
});
