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
    $nombre = trim($_POST['nombre']);
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
            $rol_id = 1; // Rol por defecto (usuario normal)
            
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contraseña, rol_id, activo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $hashed_password, $rol_id, 0]); // Rol 1 = usuario normal, activo = 0
            
            // Redirigir al login con mensaje de éxito
            header('Location: login.php?success=Registro completado. Un administrador activará tu cuenta pronto.');
            exit;
        } catch (PDOException $e) {
            $error = "Error al registrar el usuario: " . $e->getMessage();
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
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">

</head>
<body class="login-page">
<header class="header1">
        <div class="navbar">
            <div class="logo">
                <img src="./img/logo.webp" alt="logo">
            </div>
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="perfil.php" class="user-icon"><i class="fas fa-user"></i></a>
                    <a href="logout.php" class="logout-icon"><i class="fas fa-sign-out-alt"></i></a>

                <?php else: ?>
                    <a href="login.php">Iniciar Sesión</a>
                    <a href="register.php">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <div class="login-container">
        <h1>SIGN UP FOR NETFLIX</h1>
        
        <div id="errores" class="error-container" style="display: none;"></div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="register.php">
            <div class="form-group">
                <input type="text" id="nombre" name="nombre" placeholder="Full Name"
                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Email"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password">
                <small class="password-requirements">
                    Must be at least 6 characters, contain one uppercase letter and one number
                </small>
            </div>
            
            <div class="form-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
            </div>
            
            <button type="submit" class="login-button">Sign up</button>
        </form>
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html> 