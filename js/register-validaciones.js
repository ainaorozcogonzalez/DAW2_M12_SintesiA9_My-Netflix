document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        let isValid = true;
        let errors = [];

        // Validación del nombre
        if (!nombre) {
            errors.push('El nombre es requerido');
            isValid = false;
        } else if (nombre.length < 2) {
            errors.push('El nombre debe tener al menos 2 caracteres');
            isValid = false;
        }

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
        } else if (!/\d/.test(password)) {
            errors.push('La contraseña debe contener al menos un número');
            isValid = false;
        } else if (!/[A-Z]/.test(password)) {
            errors.push('La contraseña debe contener al menos una mayúscula');
            isValid = false;
        }

        // Validación de confirmación de contraseña
        if (password !== confirmPassword) {
            errors.push('Las contraseñas no coinciden');
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

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
}); 