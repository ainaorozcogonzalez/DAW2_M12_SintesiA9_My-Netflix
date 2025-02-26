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

    // Función para actualizar la tabla
    async function actualizarTabla() {
        try {
            const response = await fetch('admin_gestion_usuarios.php?obtener_tabla=1');
            if (!response.ok) throw new Error('Error al obtener los usuarios');
            const data = await response.text();
            document.querySelector('tbody').innerHTML = data;
            inicializarEventos();
        } catch (error) {
            mostrarMensaje('Error al mostrar los usuarios: ' + error.message, 'danger');
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
                        if (!response.ok) throw new Error('Error al eliminar el usuario');
                        const data = await response.json();
                        if (data.status === 'success') {
                            await actualizarTabla();
                            mostrarMensaje('Usuario eliminado correctamente', 'success');
                        } else {
                            mostrarMensaje(data.message, 'danger');
                        }
                    } catch (error) {
                        mostrarMensaje(error.message, 'danger');
                    }
                }
            });
        });
    }

    // Editar usuario
    function inicializarEditar() {
        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', async function (e) {
                e.preventDefault();
                const userId = this.getAttribute('data-id');
                
                try {
                    const response = await fetch(`admin_gestion_usuarios.php?obtener_usuario=${userId}`);
                    const data = await response.json();
                    
                    if (data.status === 'error') {
                        throw new Error(data.message);
                    }
                    
                    // Llenar el formulario
                    document.getElementById('editarUsuarioId').value = data.id;
                    document.getElementById('editarNombre').value = data.nombre;
                    document.getElementById('editarEmail').value = data.email;
                    document.getElementById('editarRol').value = data.rol_id;
                    document.getElementById('editarActivo').checked = data.activo == 1;
                    
                    // Mostrar el modal
                    const modal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
                    modal.show();
                } catch (error) {
                    mostrarMensaje('Error al cargar los datos del usuario: ' + error.message, 'danger');
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
                        if (modal) {
                            modal.hide();
                            // Eliminar el backdrop manualmente
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.remove();
                            }
                            // Eliminar la clase modal-open del body
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }
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
                        if (modal) {
                            modal.hide();
                            // Eliminar el backdrop manualmente
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.remove();
                            }
                            // Eliminar la clase modal-open del body
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }
                        await actualizarTabla();
                        mostrarMensaje('Usuario creado correctamente', 'success');
                        formCrear.reset();
                    } else {
                        mostrarMensaje(data.message || 'Error al crear el usuario', 'danger');
                    }
                } catch (error) {
                    mostrarMensaje(error.message, 'danger');
                }
            });
        }
    }

    // Inicializar todos los eventos
    function inicializarEventos() {
        inicializarEliminar();
        inicializarEditar();
        inicializarCrear();
    }

    // Actualizar la tabla al cargar la página
    actualizarTabla();
});
