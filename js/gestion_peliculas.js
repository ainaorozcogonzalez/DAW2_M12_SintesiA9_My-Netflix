document.addEventListener('DOMContentLoaded', function() {
    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo) {
        Swal.fire({
            title: tipo.charAt(0).toUpperCase() + tipo.slice(1),
            text: mensaje,
            icon: tipo,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
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
            formCrear.addEventListener('submit', async function(e) {
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
                        formCrear.reset();
                        await actualizarTabla();
                        await Swal.fire({
                            title: '¡Éxito!',
                            text: 'Contenido creado correctamente',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || 'Error al crear el contenido');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: error.message,
                        icon: 'error'
                    });
                }
            });
        }
    }

    // Editar contenido
    function inicializarEditar() {
        document.querySelectorAll('.form-editar-contenido').forEach(form => {
            form.addEventListener('submit', async function(e) {
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
                        const modal = bootstrap.Modal.getInstance(this.closest('.modal'));
                        modal.hide();
                        await actualizarTabla();
                        await Swal.fire({
                            title: '¡Éxito!',
                            text: 'Contenido actualizado correctamente',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || 'Error al actualizar el contenido');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: error.message,
                        icon: 'error'
                    });
                }
            });
        });
    }

    // Eliminar contenido
    function inicializarEliminar() {
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                
                const result = await Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });

                if (result.isConfirmed) {
                    const contenidoId = this.dataset.id;
                    try {
                        const response = await fetch(`admin_gestion_peliculas.php?delete_id=${contenidoId}`);
                        if (!response.ok) throw new Error('Error en la respuesta del servidor');
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            await Swal.fire({
                                title: '¡Eliminado!',
                                text: 'El contenido ha sido eliminado correctamente.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            await actualizarTabla();
                        } else {
                            throw new Error(data.message || 'Error al eliminar el contenido');
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al eliminar el contenido: ' + error.message,
                            icon: 'error'
                        });
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
