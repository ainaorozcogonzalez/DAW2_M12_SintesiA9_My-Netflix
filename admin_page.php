<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: login.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - MyNetflix</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="admin-page">
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="./img/logo.webp" alt="logo">
            </div>
            <nav>
                <a href="logout.php" class="logout-icon"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <h1>Panel de Administración</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">Operación realizada con éxito</div>
        <?php endif; ?>
        
        <div class="admin-options">
            <a href="admin/admin_validar_usuarios.php" class="admin-option">
                <i class="fas fa-user-check"></i>
                <span>Validar Usuarios</span>
            </a>
            
            <a href="admin/admin_gestion_usuarios.php" class="admin-option">
                <i class="fas fa-users-cog"></i>
                <span>Gestionar Usuarios</span>
            </a>
            
            <a href="admin/admin_gestion_peliculas.php" class="admin-option">
                <i class="fas fa-film"></i>
                <span>Gestionar Contenido</span>
            </a>
        </div>
    </div>
</body>
</html> 