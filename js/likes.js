document.addEventListener('DOMContentLoaded', function() {
    // Manejar clicks en botones de like
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', function() {
            if (this.classList.contains('disabled')) {
                window.location.href = 'login.php';
                return;
            }

            const contenidoId = this.dataset.id;
            
            fetch('like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'contenido_id=' + contenidoId
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    window.location.href = 'login.php';
                    return;
                }
                
                if (data.action === 'liked') {
                    this.classList.add('liked');
                } else {
                    this.classList.remove('liked');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
}); 