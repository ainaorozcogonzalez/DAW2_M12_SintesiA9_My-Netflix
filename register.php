<?php
session_start();
require 'conexion.php'; // Incluir la conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validación básica
    if (empty($nombre) || empty($apellido) || empty($username) || empty($email) || empty($password)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        // Verificar si el email ya está registrado
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $error = "El email ya está registrado.";
        } else {
            // Insertar nuevo usuario
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, username, email, contraseña, rol_id) VALUES (:nombre, :apellido, :username, :email, :contraseña, 2)"); // 2 para rol de cliente
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':contraseña', $hashedPassword);
            $stmt->execute();

            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function validateForm() {
            const nombre = document.forms["registerForm"]["nombre"].value;
            const apellido = document.forms["registerForm"]["apellido"].value;
            const username = document.forms["registerForm"]["username"].value;
            const email = document.forms["registerForm"]["email"].value;
            const password = document.forms["registerForm"]["password"].value;
            if (nombre === "" || apellido === "" || username === "" || email === "" || password === "") {
                alert("Por favor, completa todos los campos.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h2>Registrarse</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form name="registerForm" method="POST" onsubmit="return validateForm()">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required>
        <br>
        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" required>
        <br>
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" required>
        <br>
        <input type="submit" value="Registrarse">
    </form>
</body>
</html> 