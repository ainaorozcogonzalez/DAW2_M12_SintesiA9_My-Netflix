document.addEventListener('DOMContentLoaded', function() {
    // Manejar clicks en botones de like
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', function() {
            if (this.classList.contains('disabled')) {
                window.location.href = 'login.php';
                return;
            }

            const contenidoId = this.dataset.id;
            
            fetch('reproducir.php?id=' + contenidoId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'toggle_like=1'
            })
            .then(response => response.json())
            .then(data => {
                // Actualizar el botón
                this.classList.toggle('liked');
                this.querySelector('i').style.color = this.classList.contains('liked') ? '#e50914' : '#fff';
                
                // Actualizar el contador solo en reproducir.php
                const likesCounter = document.querySelector('.likes-count .like-button');
                if (likesCounter && likesCounter.dataset.id === contenidoId) {
                    likesCounter.innerHTML = `<i class="fas fa-heart"></i> ${data.likes}`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
}); 