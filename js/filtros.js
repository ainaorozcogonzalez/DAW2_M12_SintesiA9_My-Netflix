document.addEventListener('DOMContentLoaded', function() {
    const filtros = document.querySelectorAll('.filtro-btn');
    const contenidos = document.querySelectorAll('.contenido');
    const top5Section = document.getElementById('top5');
    const contenidosSection = document.getElementById('contenidos');
    const searchInput = document.querySelector('.search-input');
    const searchButton = document.querySelector('.search-button');

    function aplicarFiltros() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const tipoFiltro = document.querySelector('.filtro-btn.active')?.dataset.filtro || 'all';

        // Ocultar Top 5 si hay un término de búsqueda o si se aplica un filtro específico
        if (searchTerm || tipoFiltro !== 'all') {
            top5Section.style.display = 'none';
            contenidosSection.style.display = 'block';
        } else {
            top5Section.style.display = 'block';
            contenidosSection.style.display = 'block';
        }

        contenidos.forEach(contenido => {
            const tipoContenido = contenido.dataset.tipo;
            const likes = parseInt(contenido.dataset.likes);
            const titulo = contenido.querySelector('.contenido-titulo')?.textContent.toLowerCase() || '';

            // Aplicar filtro de tipo
            let mostrarPorTipo = true;
            switch(tipoFiltro) {
                case 'movies':
                    mostrarPorTipo = tipoContenido === 'pelicula';
                    break;
                case 'series':
                    mostrarPorTipo = tipoContenido === 'serie';
                    break;
                case 'liked':
                    mostrarPorTipo = likes > 0;
                    break;
            }

            // Aplicar filtro de búsqueda
            const mostrarPorBusqueda = titulo.includes(searchTerm);

            // Mostrar u ocultar según ambos filtros
            contenido.style.display = (mostrarPorTipo && mostrarPorBusqueda) ? 'block' : 'none';
        });
    }

    // Eventos para los botones de filtro
    filtros.forEach(filtro => {
        filtro.addEventListener('click', function() {
            filtros.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            aplicarFiltros();
        });
    });

    // Evento para el input de búsqueda
    if (searchInput) {
        searchInput.addEventListener('input', aplicarFiltros);
    }

    // Evento para el botón de búsqueda
    if (searchButton) {
        searchButton.addEventListener('click', aplicarFiltros);
    }
});