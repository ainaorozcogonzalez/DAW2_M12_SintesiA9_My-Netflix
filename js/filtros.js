document.addEventListener('DOMContentLoaded', function() {
    const filtros = document.querySelectorAll('.filtro-btn');
    const contenidos = document.querySelectorAll('.contenido');

    filtros.forEach(filtro => {
        filtro.addEventListener('click', function() {
            const tipo = this.dataset.filtro;

            // Remover la clase active de todos los botones
            filtros.forEach(f => f.classList.remove('active'));
            // Agregar la clase active al botón seleccionado
            this.classList.add('active');

            contenidos.forEach(contenido => {
                const tipoContenido = contenido.dataset.tipo;
                const likes = parseInt(contenido.dataset.likes);

                switch(tipo) {
                    case 'all':
                        contenido.style.display = 'block';
                        break;
                    case 'movies':
                        contenido.style.display = tipoContenido === 'pelicula' ? 'block' : 'none';
                        break;
                    case 'series':
                        contenido.style.display = tipoContenido === 'serie' ? 'block' : 'none';
                        break;
                    case 'most-liked':
                        contenido.style.display = likes >= 10 ? 'block' : 'none';
                        break;
                }
            });
        });
    });
}); 