<?php
session_start();
require_once '../conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: admin_gestion_usuarios.php');
    exit;
}

$id = $_GET['id'];

// Validar usuario
$stmt = $conn->prepare("UPDATE usuarios SET activo = 1 WHERE id = ?");
$stmt->execute([$id]);

header('Location: admin_gestion_usuarios.php');
exit;
