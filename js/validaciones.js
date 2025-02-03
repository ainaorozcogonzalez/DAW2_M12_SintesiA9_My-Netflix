document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        let isValid = true;
        let errors = [];

        // Validación del email
        if (!email) {
            errors.push('El email es requerido');
            isValid = false;
        } else if (!isValidEmail(email)) {
            errors.push('El email no es válido');
            isValid = false;
        }

        // Validación de la contraseña
        if (!password) {
            errors.push('La contraseña es requerida');
            isValid = false;
        } else if (password.length < 6) {
            errors.push('La contraseña debe tener al menos 6 caracteres');
            isValid = false;
        }

        // Mostrar errores o enviar el formulario
        const errorDiv = document.getElementById('errores');
        if (!isValid) {
            errorDiv.innerHTML = errors.map(error => `<div class="error-item">${error}</div>`).join('');
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
            this.submit();
        }
    });

    // Función para validar email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
}); 