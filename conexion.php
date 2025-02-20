<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'MyNetflix';
$username = 'root';
$password = 'qweQWE123'; 

try {
    // Crear una nueva conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar el modo de error de PDO para que lance excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Manejo de errores
    echo "Error de conexión: " . $e->getMessage();
    exit; // Termina el script si hay un error
}
?>
