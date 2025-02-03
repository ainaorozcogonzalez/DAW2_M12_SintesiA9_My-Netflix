<?php

function redirectByRole($role_id) {
    switch ($role_id) {
        case 1: // Admin
            header('Location: admin/dashboard.php');
            break;
        case 2: // Usuario normal
            header('Location: index.php');
            break;
        default:
            header('Location: index.php');
    }
    exit;
}

function logLoginAttempt($email, $success) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO login_attempts (email, success, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$email, $success ? 1 : 0, $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        error_log("Error logging login attempt: " . $e->getMessage());
    }
}

function isBlocked($email) {
    global $conn;
    try {
        // Verificar intentos fallidos en los últimos 15 minutos
        $stmt = $conn->prepare("
            SELECT COUNT(*) as intentos 
            FROM login_attempts 
            WHERE email = ? 
            AND success = 0 
            AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['intentos'] >= 5; // Bloquear después de 5 intentos fallidos
    } catch (PDOException $e) {
        error_log("Error checking blocked status: " . $e->getMessage());
        return false;
    }
} 