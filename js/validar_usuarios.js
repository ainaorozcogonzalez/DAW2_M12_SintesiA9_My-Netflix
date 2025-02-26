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
                
                const result = await Swal.fire({
                    title: '¿Validar usuario?',
                    text: "¿Estás seguro de que quieres validar este usuario?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, validar',
                    cancelButtonText: 'Cancelar'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`admin_validar_usuarios.php?id=${userId}`);
                        if (!response.ok) throw new Error('Error en la respuesta del servidor');
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            await Swal.fire({
                                title: '¡Validado!',
                                text: 'El usuario ha sido validado correctamente.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            await actualizarTabla();
                        } else {
                            throw new Error(data.message || 'Error al validar el usuario');
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al validar el usuario: ' + error.message,
                            icon: 'error'
                        });
                    }
                }
            });
        });
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
                        const response = await fetch(`admin_validar_usuarios.php?delete_id=${userId}`);
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

    // Inicializar todos los eventos
    function inicializarEventos() {
        inicializarValidar();
        inicializarEliminar();
    }

    // Inicializar eventos al cargar la página
    inicializarEventos();
    actualizarTabla();
});
