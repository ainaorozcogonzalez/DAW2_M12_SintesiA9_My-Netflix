$(document).ready(function() {
    // Eliminar contenido con AJAX
    $('.btn-eliminar-contenido').click(function(e) {
        e.preventDefault();
        if(confirm('¿Estás seguro de eliminar este contenido?')) {
            var contenidoId = $(this).data('id');
            $.ajax({
                url: 'ajax/eliminar_contenido.php',
                type: 'GET',
                data: { id: contenidoId },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error al eliminar el contenido');
                }
            });
        }
    });
});
