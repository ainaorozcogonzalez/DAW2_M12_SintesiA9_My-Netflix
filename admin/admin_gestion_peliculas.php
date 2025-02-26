<?php
session_start();
require_once '../conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: login.php');
    exit;
}

// Función para procesar la subida de archivos
function procesarArchivo($archivo, $carpeta, $formatosPermitidos, $nombreBase) {
    if ($archivo['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (in_array($extension, $formatosPermitidos)) {
            $nombreArchivo = str_replace(' ', '_', strtolower($nombreBase)) . '.' . $extension;
            $rutaDestino = $carpeta . $nombreArchivo;
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                return $nombreArchivo;
            }
        }
    }
    return null;
}

// MANEJO DE SOLICITUDES AJAX
if (isset($_GET['ajax']) || isset($_POST['ajax']) || 
    isset($_GET['obtener_tabla']) || isset($_GET['delete_id']) || 
    isset($_POST['crear_contenido']) || isset($_POST['editar_contenido'])) {
    
    header('Content-Type: application/json');

    // Procesar edición
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_contenido'])) {
        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $fecha_lanzamiento = $_POST['fecha_lanzamiento'];
        $tipo = $_POST['tipo'];
        $activo = isset($_POST['activo']) ? 1 : 0;

        // Procesar imagen
        $imagen = $_POST['imagen_actual'];
        if (!empty($_FILES['imagen']['name'])) {
            $imagenSubida = procesarArchivo($_FILES['imagen'], '../img/', ['jpg', 'jpeg', 'png', 'webp'], $titulo);
            if ($imagenSubida) {
                $imagen = $imagenSubida;
            }
        }

        // Procesar video
        $video_url = $_POST['video_url_actual'];
        if (!empty($_FILES['video_url']['name'])) {
            $videoSubido = procesarArchivo($_FILES['video_url'], '../vd/', ['mp4'], $titulo);
            if ($videoSubido) {
                $video_url = $videoSubido;
            }
        }

        try {
            $stmt = $conn->prepare("UPDATE contenidos SET titulo = ?, descripcion = ?, fecha_lanzamiento = ?, tipo = ?, imagen = ?, video_url = ?, activo = ? WHERE id = ?");
            $stmt->execute([$titulo, $descripcion, $fecha_lanzamiento, $tipo, $imagen, $video_url, $activo, $id]);
            echo json_encode(['status' => 'success']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Procesar creación
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_contenido'])) {
        header('Content-Type: application/json');
        
        try {
            $titulo = $_POST['titulo'];
            $descripcion = $_POST['descripcion'];
            $fecha_lanzamiento = $_POST['fecha_lanzamiento'];
            $tipo = $_POST['tipo'];
            $activo = isset($_POST['activo']) ? 1 : 0;

            // Procesar imagen
            $imagen = null;
            if (!empty($_FILES['imagen']['name'])) {
                $imagen = procesarArchivo($_FILES['imagen'], '../img/', ['jpg', 'jpeg', 'png', 'webp'], $titulo);
                if (!$imagen) {
                    throw new Exception('Error al procesar la imagen');
                }
            }

            // Procesar video
            $video_url = null;
            if (!empty($_FILES['video_url']['name'])) {
                $video_url = procesarArchivo($_FILES['video_url'], '../vd/', ['mp4'], $titulo);
                if (!$video_url) {
                    throw new Exception('Error al procesar el video');
                }
            }

            $stmt = $conn->prepare("INSERT INTO contenidos (titulo, descripcion, fecha_lanzamiento, tipo, imagen, video_url, activo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $descripcion, $fecha_lanzamiento, $tipo, $imagen, $video_url, $activo]);
            
            echo json_encode(['status' => 'success']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Devolver la tabla
    if (isset($_GET['obtener_tabla'])) {
        $query = "SELECT * FROM contenidos";
        $params = [];
        
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $query .= " WHERE titulo LIKE :search";
            $params['search'] = $search;
        }
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        ob_start();
        // Primero generamos las filas de la tabla
        foreach ($contenidos as $contenido): ?>
            <tr>
                <td><?php echo htmlspecialchars($contenido['titulo'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($contenido['tipo'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($contenido['fecha_lanzamiento'] ?? ''); ?></td>
                <td><?php echo ($contenido['activo'] == 1) ? 'Habilitado' : 'Deshabilitado'; ?></td>
                <td>
                    <button class="btn btn-warning btn-sm btn-editar" data-bs-toggle="modal" data-bs-target="#editarContenidoModal<?php echo $contenido['id']; ?>">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?php echo $contenido['id']; ?>">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </td>
            </tr>
        <?php endforeach;
        $tablaHtml = ob_get_clean();

        // Luego generamos los modales
        ob_start();
        foreach ($contenidos as $contenido): ?>
            <div class="modal fade" id="editarContenidoModal<?php echo $contenido['id']; ?>" tabindex="-1" aria-labelledby="editarContenidoModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editarContenidoModalLabel">Editar Contenido</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="" enctype="multipart/form-data" class="form-editar-contenido">
                                <input type="hidden" name="editar_contenido" value="1">
                                <input type="hidden" name="id" value="<?php echo $contenido['id']; ?>">
                                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($contenido['imagen'] ?? ''); ?>">
                                <input type="hidden" name="video_url_actual" value="<?php echo htmlspecialchars($contenido['video_url'] ?? ''); ?>">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($contenido['titulo'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($contenido['descripcion'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="fecha_lanzamiento" class="form-label">Fecha de Lanzamiento</label>
                                    <input type="date" class="form-control" id="fecha_lanzamiento" name="fecha_lanzamiento" value="<?php echo htmlspecialchars($contenido['fecha_lanzamiento'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo</label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="pelicula" <?php echo ($contenido['tipo'] == 'pelicula') ? 'selected' : ''; ?>>Película</option>
                                        <option value="serie" <?php echo ($contenido['tipo'] == 'serie') ? 'selected' : ''; ?>>Serie</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="imagen" class="form-label">Imagen (JPG, PNG, WEBP)</label>
                                    <input type="file" class="form-control" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp">
                                    <?php if (!empty($contenido['imagen'])): ?>
                                        <small>Imagen actual: <?php echo htmlspecialchars($contenido['imagen']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="video_url" class="form-label">Video (MP4)</label>
                                    <input type="file" class="form-control" id="video_url" name="video_url" accept=".mp4">
                                    <?php if (!empty($contenido['video_url'])): ?>
                                        <small>Video actual: <?php echo htmlspecialchars($contenido['video_url']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="activo" name="activo" <?php echo ($contenido['activo'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="activo">Habilitado</label>
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
        $modalesHtml = ob_get_clean();

        // Enviamos tanto la tabla como los modales
        echo json_encode([
            'status' => 'success',
            'html' => $tablaHtml,
            'modales' => $modalesHtml
        ]);
        exit;
    }

    // Manejar eliminación
    if (isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        try {
            $conn->beginTransaction();
            
            // Eliminar likes asociados
            $stmt = $conn->prepare("DELETE FROM likes WHERE contenido_id = ?");
            $stmt->execute([$delete_id]);
            
            // Eliminar el contenido
            $stmt = $conn->prepare("DELETE FROM contenidos WHERE id = ?");
            $stmt->execute([$delete_id]);
            
            $conn->commit();
            echo json_encode(['status' => 'success']);
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    exit;
}

// CARGA INICIAL DE DATOS
$query = "SELECT * FROM contenidos";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $query .= " WHERE titulo LIKE :search";
    $stmt = $conn->prepare($query);
    $stmt->execute(['search' => $search]);
} else {
    $stmt = $conn->prepare($query);
    $stmt->execute();
}
$contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Contenido - MyNetflix</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="admin-page">
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../img/logo.webp" alt="logo">
            </div>
            <nav>
                <a href="../admin_page.php" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="../logout.php" class="logout-icon">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <h1 style="color: white;">Gestionar Contenido</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                if ($_GET['success'] == 1) {
                    echo 'Contenido eliminado correctamente';
                } elseif ($_GET['success'] == 2) {
                    echo 'Contenido actualizado correctamente';
                } elseif ($_GET['success'] == 3) {
                    echo 'Contenido creado correctamente';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Buscador rápido -->
        <form class="mb-3 form-buscar">
            <div class="input-group">
                <input type="text" class="form-control" name="search" id="searchInput" placeholder="Buscar películas o series..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </form>

        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#crearContenidoModal">
            <i class="fas fa-plus"></i> Crear Contenido
        </button>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Fecha de Lanzamiento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($contenidos) > 0): ?>
                        <?php foreach ($contenidos as $contenido): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($contenido['titulo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($contenido['tipo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($contenido['fecha_lanzamiento'] ?? ''); ?></td>
                                <td><?php echo ($contenido['activo'] == 1) ? 'Habilitado' : 'Deshabilitado'; ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm btn-editar" data-bs-toggle="modal" data-bs-target="#editarContenidoModal<?php echo $contenido['id']; ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?php echo $contenido['id']; ?>">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal para editar contenido -->
                            <div class="modal fade" id="editarContenidoModal<?php echo $contenido['id']; ?>" tabindex="-1" aria-labelledby="editarContenidoModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editarContenidoModalLabel">Editar Contenido</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="" enctype="multipart/form-data" class="form-editar-contenido">
                                                <input type="hidden" name="id" value="<?php echo $contenido['id']; ?>">
                                                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($contenido['imagen'] ?? ''); ?>">
                                                <input type="hidden" name="video_url_actual" value="<?php echo htmlspecialchars($contenido['video_url'] ?? ''); ?>">
                                                <div class="mb-3">
                                                    <label for="titulo" class="form-label">Título</label>
                                                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($contenido['titulo'] ?? ''); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="descripcion" class="form-label">Descripción</label>
                                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($contenido['descripcion'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="fecha_lanzamiento" class="form-label">Fecha de Lanzamiento</label>
                                                    <input type="date" class="form-control" id="fecha_lanzamiento" name="fecha_lanzamiento" value="<?php echo htmlspecialchars($contenido['fecha_lanzamiento'] ?? ''); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="tipo" class="form-label">Tipo</label>
                                                    <select class="form-select" id="tipo" name="tipo" required>
                                                        <option value="pelicula" <?php echo ($contenido['tipo'] == 'pelicula') ? 'selected' : ''; ?>>Película</option>
                                                        <option value="serie" <?php echo ($contenido['tipo'] == 'serie') ? 'selected' : ''; ?>>Serie</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="imagen" class="form-label">Imagen (JPG, PNG, WEBP)</label>
                                                    <input type="file" class="form-control" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp">
                                                    <?php if (!empty($contenido['imagen'])): ?>
                                                        <small>Imagen actual: <?php echo htmlspecialchars($contenido['imagen']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="video_url" class="form-label">Video (MP4)</label>
                                                    <input type="file" class="form-control" id="video_url" name="video_url" accept=".mp4">
                                                    <?php if (!empty($contenido['video_url'])): ?>
                                                        <small>Video actual: <?php echo htmlspecialchars($contenido['video_url']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mb-3 form-check">
                                                    <input type="checkbox" class="form-check-input" id="activo" name="activo" <?php echo ($contenido['activo'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="activo">Habilitado</label>
                                                </div>
                                                <button type="submit" name="editar_contenido" class="btn btn-primary">Guardar Cambios</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay contenido disponible</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para crear contenido -->
    <div class="modal fade" id="crearContenidoModal" tabindex="-1" aria-labelledby="crearContenidoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearContenidoModalLabel">Crear Contenido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data" class="form-crear-contenido">
                        <input type="hidden" name="crear_contenido" value="1">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_lanzamiento" class="form-label">Fecha de Lanzamiento</label>
                            <input type="date" class="form-control" id="fecha_lanzamiento" name="fecha_lanzamiento" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="pelicula">Película</option>
                                <option value="serie">Serie</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen (JPG, PNG, WEBP)</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp" required>
                        </div>
                        <div class="mb-3">
                            <label for="video_url" class="form-label">Video (MP4)</label>
                            <input type="file" class="form-control" id="video_url" name="video_url" accept=".mp4">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
                            <label class="form-check-label" for="activo">Habilitado</label>
                        </div>
                        <button type="submit" name="crear_contenido" class="btn btn-primary">Crear Contenido</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/gestion_peliculas.js"></script>
</body>
</html>