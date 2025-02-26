$(document).ready(function() {
    // Validar usuario con AJAX
    $('.btn-validar').click(function(e) {
        e.preventDefault();
        var userId = $(this).data('id');
        $.ajax({
            url: 'ajax/validar_usuario.php',
            type: 'GET',
            data: { id: userId },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error al validar el usuario');
            }
        });
    });

    // Eliminar usuario con AJAX
    $('.btn-eliminar').click(function(e) {
        e.preventDefault();
        if(confirm('¿Estás seguro de eliminar este usuario?')) {
            var userId = $(this).data('id');
            $.ajax({
                url: 'ajax/eliminar_usuario.php',
                type: 'GET',
                data: { id: userId },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error al eliminar el usuario');
                }
            });
        }
    });
});
