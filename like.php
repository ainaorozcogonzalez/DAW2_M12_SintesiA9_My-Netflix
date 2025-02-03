<?php
session_start();
require 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no logueado']);
    exit;
}

if (isset($_POST['contenido_id'])) {
    $usuario_id = $_SESSION['user_id'];
    $contenido_id = $_POST['contenido_id'];
    
    // Verificar si ya existe el like
    $stmt = $conn->prepare("SELECT id FROM likes WHERE usuario_id = ? AND contenido_id = ?");
    $stmt->execute([$usuario_id, $contenido_id]);
    $like_existente = $stmt->fetch();
    
    if ($like_existente) {
        // Si existe, eliminar el like
        $stmt = $conn->prepare("DELETE FROM likes WHERE usuario_id = ? AND contenido_id = ?");
        $stmt->execute([$usuario_id, $contenido_id]);
        
        // Actualizar contador de likes
        $stmt = $conn->prepare("UPDATE contenidos SET likes = likes - 1 WHERE id = ?");
        $stmt->execute([$contenido_id]);
        
        echo json_encode(['success' => true, 'action' => 'unliked']);
    } else {
        // Si no existe, crear el like
        $stmt = $conn->prepare("INSERT INTO likes (usuario_id, contenido_id) VALUES (?, ?)");
        $stmt->execute([$usuario_id, $contenido_id]);
        
        // Actualizar contador de likes
        $stmt = $conn->prepare("UPDATE contenidos SET likes = likes + 1 WHERE id = ?");
        $stmt->execute([$contenido_id]);
        
        echo json_encode(['success' => true, 'action' => 'liked']);
    }
} else {
    echo json_encode(['error' => 'ID de contenido no proporcionado']);
} 