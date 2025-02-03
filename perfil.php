<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener información del usuario
$stmt = $conn->prepare("SELECT nombre, email, fecha_registro FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener los likes del usuario
$stmt = $conn->prepare("
    SELECT c.* 
    FROM contenidos c 
    INNER JOIN likes l ON c.id = l.contenido_id 
    WHERE l.usuario_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$contenidos_liked = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar actualización de perfil
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['actualizar_perfil'])) {
        $nombre = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        
        $errors = [];
        
        // Validar nombre
        if (empty($nombre)) {
            $errors[] = "El nombre es requerido";
        }
        
        // Validar email
        if (empty($email)) {
            $errors[] = "El email es requerido";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido";
        }
        
        // Si se quiere cambiar la contraseña
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $errors[] = "La nueva contraseña debe tener al menos 6 caracteres";
            } elseif (empty($password)) {
                $errors[] = "Debes introducir tu contraseña actual para cambiarla";
            }
        }
        
        if (empty($errors)) {
            try {
                // Verificar contraseña actual si se quiere cambiar
                if (!empty($new_password)) {
                    $stmt = $conn->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $current_user = $stmt->fetch();
                    
                    if (!password_verify($password, $current_user['contraseña'])) {
                        $mensaje = "La contraseña actual es incorrecta";
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, contraseña = ? WHERE id = ?");
                        $stmt->execute([$nombre, $email, $hashed_password, $_SESSION['user_id']]);
                        $mensaje = "Perfil actualizado correctamente";
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $_SESSION['user_id']]);
                    $mensaje = "Perfil actualizado correctamente";
                }
                
                // Actualizar información en la sesión
                $_SESSION['user_email'] = $email;
                
                // Recargar información del usuario
                $stmt = $conn->prepare("SELECT nombre, email, fecha_registro FROM usuarios WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                error_log("Error actualizando perfil: " . $e->getMessage());
                $mensaje = "Error al actualizar el perfil";
            }
        } else {
            $mensaje = implode("<br>", $errors);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - MyNetflix</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="perfil-page">
    <nav class="nav-reproductor">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
        <div class="nav-title">Mi Perfil</div>
    </nav>

    <div class="perfil-container">
        <div class="perfil-content">
            <div class="perfil-info">
                <h1>Mi Perfil</h1>
                
                <?php if ($mensaje): ?>
                    <div class="<?php echo strpos($mensaje, 'Error') !== false ? 'error-message' : 'success-message'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="perfil.php" class="perfil-form">
                    <input type="hidden" name="actualizar_perfil" value="1">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña actual (solo si deseas cambiarla):</label>
                        <input type="password" id="password" name="password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nueva contraseña (opcional):</label>
                        <input type="password" id="new_password" name="new_password">
                        <small class="password-requirements">
                            La contraseña debe tener al menos 6 caracteres
                        </small>
                    </div>
                    
                    <button type="submit">Actualizar Perfil</button>
                </form>
            </div>

            <div class="perfil-stats">
                <h2>Información de la cuenta</h2>
                <p>Miembro desde: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></p>
                <?php if (isset($usuario['ultimo_acceso']) && $usuario['ultimo_acceso']): ?>
                    <p>Último acceso: <?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])); ?></p>
                <?php endif; ?>
            </div>

            <div class="contenidos-liked">
                <h2>Mis Favoritos</h2>
                <div class="liked-grid">
                    <?php foreach ($contenidos_liked as $contenido): ?>
                        <div class="contenido-card">
                            <img src="./img/<?php echo htmlspecialchars($contenido['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($contenido['titulo']); ?>">
                            <div class="contenido-info">
                                <h3><?php echo htmlspecialchars($contenido['titulo']); ?></h3>
                                <a href="reproducir.php?id=<?php echo $contenido['id']; ?>" class="play-button">
                                    <i class="fas fa-play"></i> Reproducir
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 