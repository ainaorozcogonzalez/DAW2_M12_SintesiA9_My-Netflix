document.addEventListener('DOMContentLoaded', function () {
    let usuarios = []; // Variable para almacenar los usuarios

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

    // Función para actualizar la tabla con búsqueda
    async function actualizarTabla(searchTerm = '') {
        try {
            const url = searchTerm 
                ? `admin_gestion_usuarios.php?obtener_tabla=1&search=${encodeURIComponent(searchTerm)}`
                : 'admin_gestion_usuarios.php?obtener_tabla=1';
                
            const response = await fetch(url);
            if (!response.ok) throw new Error('Error al obtener los usuarios');
            const data = await response.text();
            document.querySelector('tbody').innerHTML = data;
            inicializarEventos();
        } catch (error) {
            mostrarMensaje('Error al actualizar la tabla: ' + error.message, 'danger');
        }
    }

    // Crear usuario
    function inicializarCrear() {
        const formCrear = document.querySelector('.form-crear-usuario');
        if (formCrear) {
            formCrear.addEventListener('submit', async function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await fetch('admin_gestion_usuarios.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) throw new Error('Error al crear el usuario');
                    const data = await response.json();

                    if (data.status === 'success') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('crearUsuarioModal'));
                        modal.hide();
                        formCrear.reset();
                        await actualizarTabla();
                        mostrarMensaje('Usuario creado correctamente', 'success');
                    } else {
                        mostrarMensaje(data.message || 'Error al crear el usuario', 'danger');
                    }
                } catch (error) {
                    mostrarMensaje(error.message, 'danger');
                }
            });
        }
    }

    // Editar usuario
    function inicializarEditar() {
        // Manejar el clic en el botón editar para cargar los datos
        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', async function () {
                const userId = this.dataset.id;
                try {
                    const response = await fetch(`admin_gestion_usuarios.php?obtener_usuario=${userId}`);
                    if (!response.ok) throw new Error('Error al obtener los datos del usuario');
                    const usuario = await response.json();
                    
                    // Rellenar el formulario
                    document.getElementById('editarUsuarioId').value = usuario.id;
                    document.getElementById('editarNombre').value = usuario.nombre;
                    document.getElementById('editarEmail').value = usuario.email;
                    document.getElementById('editarRol').value = usuario.rol_id;
                    document.getElementById('editarActivo').checked = usuario.activo == 1;
                } catch (error) {
                    mostrarMensaje(error.message, 'danger');
                }
            });
        });

        // Manejar el envío del formulario de edición
        const formEditar = document.querySelector('.form-editar-usuario');
        if (formEditar) {
            formEditar.addEventListener('submit', async function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await fetch('admin_gestion_usuarios.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) throw new Error('Error al actualizar el usuario');
                    const data = await response.json();

                    if (data.status === 'success') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal'));
                        modal.hide();
                        await actualizarTabla();
                        mostrarMensaje('Usuario actualizado correctamente', 'success');
                    } else {
                        mostrarMensaje(data.message || 'Error al actualizar el usuario', 'danger');
                    }
                } catch (error) {
                    mostrarMensaje(error.message, 'danger');
                }
            });
        }
    }

    // Eliminar usuario
    function inicializarEliminar() {
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', async function (e) {
                e.preventDefault();
                if (confirm('¿Estás seguro de eliminar este usuario?')) {
                    const userId = this.dataset.id;
                    try {
                        const response = await fetch(`admin_gestion_usuarios.php?delete_id=${userId}`);
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

    // Inicializar búsqueda
    function inicializarBusqueda() {
        const formBuscar = document.querySelector('.form-buscar');
        if (formBuscar) {
            formBuscar.addEventListener('submit', async function(e) {
                e.preventDefault();
                const searchTerm = this.querySelector('input[name="search"]').value;
                await actualizarTabla(searchTerm);
            });

            // Búsqueda en tiempo real
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

    // Actualizar la función inicializarEventos para incluir la búsqueda
    function inicializarEventos() {
        inicializarCrear();
        inicializarEditar();
        inicializarEliminar();
        inicializarBusqueda();
    }

    // Inicializar eventos al cargar la página
    actualizarTabla();
});
