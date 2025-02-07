<?php
session_start();
require_once 'conexion.php';

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] == 2 ? 'admin_page.php' : 'index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Por favor, complete todos los campos.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, contraseña, rol_id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['contraseña'])) {
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_role'] = $usuario['rol_id'];
                $_SESSION['user_email'] = $email;
                header('Location: ' . ($usuario['rol_id'] == 2 ? 'admin_page.php' : 'index.php'));
                exit;
            } else {
                $error = "Credenciales incorrectas.";
            }
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            $error = "Ocurrió un error. Intente más tarde.";
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
</head>
<body class="login-page">
    <div class="login-container">
        <h1>Iniciar Sesión</h1>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
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
