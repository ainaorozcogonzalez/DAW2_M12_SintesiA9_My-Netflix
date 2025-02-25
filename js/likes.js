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
                
                const likesCount = this.querySelector('.likes-count');
                if (data.action === 'liked') {
                    this.classList.add('liked');
                    this.querySelector('i').style.color = '#e50914';
                    if (likesCount) {
                        likesCount.textContent = parseInt(likesCount.textContent) + 1;
                    }
                } else if (data.action === 'unliked') {
                    this.classList.remove('liked');
                    this.querySelector('i').style.color = '#fff';
                    if (likesCount) {
                        likesCount.textContent = parseInt(likesCount.textContent) - 1;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
}); 