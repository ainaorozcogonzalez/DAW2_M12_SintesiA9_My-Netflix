document.addEventListener('DOMContentLoaded', function () {
    let usuarios = []; // Variable para almacenar los usuarios

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
                        await Swal.fire({
                            title: '¡Éxito!',
                            text: 'Usuario creado correctamente',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || 'Error al crear el usuario');
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
                        await Swal.fire({
                            title: '¡Éxito!',
                            text: 'Usuario actualizado correctamente',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || 'Error al actualizar el usuario');
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

    // Eliminar usuario
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
                    const userId = this.dataset.id;
                    try {
                        const response = await fetch(`admin_gestion_usuarios.php?delete_id=${userId}`);
                        if (!response.ok) throw new Error('Error en la respuesta del servidor');
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            await Swal.fire({
                                title: '¡Eliminado!',
                                text: 'El usuario ha sido eliminado correctamente.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            await actualizarTabla();
                        } else {
                            throw new Error(data.message || 'Error al eliminar el usuario');
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al eliminar el usuario: ' + error.message,
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
