<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php'; // Crearemos este archivo para funciones de seguridad

// Si ya está logueado, redirigir según el rol
if (isset($_SESSION['user_id'])) {
    redirectByRole($_SESSION['user_role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Limpieza y validación de datos
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Todos los campos son requeridos";
    } else {
        try {
            // Consulta preparada para prevenir SQL injection
            $stmt = $conn->prepare("SELECT id, email, contraseña, rol_id, activo FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && $usuario['activo'] == 1) {
                if (password_verify($password, $usuario['contraseña'])) {
                    // Iniciar sesión y guardar datos importantes
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['user_role'] = $usuario['rol_id'];
                    $_SESSION['user_email'] = $usuario['email'];
                    
                    // Registrar el último acceso
                    $stmt = $conn->prepare("UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$usuario['id']]);
                    
                    // Redirigir según los parámetros o el rol
                    if (isset($_GET['redirect']) && $_GET['redirect'] == 'reproducir' && isset($_GET['id'])) {
                        header('Location: reproducir.php?id=' . $_GET['id']);
                    } else {
                        redirectByRole($usuario['rol_id']);
                    }
                    exit;
                } else {
                    $error = "Credenciales incorrectas";
                    // Registrar intento fallido
                    logLoginAttempt($email, false);
                }
            } else {
                $error = "Credenciales incorrectas";
                // Registrar intento fallido
                logLoginAttempt($email, false);
            }
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            $error = "Error del sistema. Por favor, intente más tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MyNetflix</title>
    <link rel="stylesheet" href="./css/styles.css">
    <script src="./js/validaciones.js" defer></script>
</head>
<body class="login-page">
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        
        <div id="errores" class="error-container" style="display: none;"></div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) . '&id=' . htmlspecialchars($_GET['id']) : ''; ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
    </div>
</body>
</html> 