<?php
session_start();
require 'conexion.php'; // Incluir la conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validación básica
    if (empty($email) || empty($password)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        // Consulta para verificar el usuario
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar la contraseña
        if ($user && password_verify($password, $user['contraseña'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['rol_id'];

            // Redirigir según el rol
            if ($user['rol_id'] == 1) { // Suponiendo que 1 es el rol de administrador
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit;
        } else {
            $error = "Email o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function validateForm() {
            const email = document.forms["loginForm"]["email"].value;
            const password = document.forms["loginForm"]["password"].value;
            if (email === "" || password === "") {
                alert("Por favor, completa todos los campos.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form name="loginForm" method="POST" onsubmit="return validateForm()">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" required>
        <br>
        <input type="submit" value="Iniciar Sesión">
    </form>
</body>
</html> 