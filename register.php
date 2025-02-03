<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Limpieza y validación de datos
    $nombre = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    $errors = [];

    // Validar nombre
    if (empty($nombre)) {
        $errors[] = "El nombre es requerido";
    } elseif (strlen($nombre) < 2) {
        $errors[] = "El nombre debe tener al menos 2 caracteres";
    }

    // Validar email
    if (empty($email)) {
        $errors[] = "El email es requerido";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido";
    } else {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Este email ya está registrado";
        }
    }

    // Validar contraseña
    if (empty($password)) {
        $errors[] = "La contraseña es requerida";
    } elseif (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres";
    } elseif (!preg_match("/\d/", $password)) {
        $errors[] = "La contraseña debe contener al menos un número";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "La contraseña debe contener al menos una mayúscula";
    }

    // Validar confirmación de contraseña
    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden";
    }

    if (empty($errors)) {
        try {
            // Insertar nuevo usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $rol_id = 2; // Rol por defecto (usuario normal)
            
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contraseña, rol_id) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nombre, $email, $hashed_password, $rol_id])) {
                $success = "Registro exitoso. Por favor, inicia sesión.";
                
                // Opcional: Iniciar sesión automáticamente
                $_SESSION['user_id'] = $conn->lastInsertId();
                $_SESSION['user_role'] = $rol_id;
                $_SESSION['user_email'] = $email;
                
                // Redirigir después de 2 segundos
                header("refresh:2;url=index.php");
            }
        } catch (PDOException $e) {
            error_log("Error en registro: " . $e->getMessage());
            $error = "Error al crear la cuenta. Por favor, intente más tarde.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - MyNetflix</title>
    <link rel="stylesheet" href="./css/styles.css">
    <script src="./js/register-validaciones.js" defer></script>
</head>
<body class="login-page">
    <div class="login-container">
        <h1>Crear Cuenta</h1>
        
        <div id="errores" class="error-container" style="display: none;"></div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="register.php">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required 
                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
                <small class="password-requirements">
                    La contraseña debe tener al menos 6 caracteres, una mayúscula y un número
                </small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia Sesión</a></p>
    </div>
</body>
</html> 