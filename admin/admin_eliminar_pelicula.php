<?php
session_start();
require_once '../conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: admin_gestion_peliculas.php');
    exit;
}

$id = $_GET['id'];

try {
    // Eliminar el contenido
    $stmt = $conn->prepare("DELETE FROM contenidos WHERE id = ?");
    $stmt->execute([$id]);

    // Redirigir con mensaje de éxito
    header('Location: admin_gestion_peliculas.php?success=1');
    exit;
} catch (PDOException $e) {
    // Redirigir con mensaje de error
    header('Location: admin_gestion_peliculas.php?error=1');
    exit;
}
?>
