document.addEventListener('DOMContentLoaded', function() {
    const filtros = document.querySelectorAll('.filtro-btn');
    const contenidos = document.querySelectorAll('.contenido');
    const top5Section = document.getElementById('top5');
    const contenidosSection = document.getElementById('contenidos');

    filtros.forEach(filtro => {
        filtro.addEventListener('click', function() {
            const tipo = this.dataset.filtro;

            // Remover la clase active de todos los botones
            filtros.forEach(f => f.classList.remove('active'));
            // Agregar la clase active al botón seleccionado
            this.classList.add('active');

            // Mostrar u ocultar las secciones
            if (tipo === 'movies' || tipo === 'series') {
                top5Section.style.display = 'none';
                contenidosSection.style.display = 'block';
            } else {
                top5Section.style.display = 'block';
                contenidosSection.style.display = 'block';
            }

            contenidos.forEach(contenido => {
                const tipoContenido = contenido.dataset.tipo;

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
                }
            });
        });
    });
});