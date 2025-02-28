# 🎬 My-Netflix



**My-Netflix** es una plataforma web inspirada en servicios de streaming como Netflix. Permite a los usuarios registrarse, explorar contenido multimedia, marcar favoritos y visualizar videos de manera sencilla e intuitiva.

---

## 🚀 Características principales

✅ **Registro y autenticación de usuarios**  
✅ **Exploración de contenido multimedia**  
✅ **Reproducción de videos**  
✅ **Sistema de "Me gusta"**  
✅ **Gestión de usuarios y contenido para administradores**  
✅ **Interfaz moderna y responsiva**  

---

## 🏗️ Tecnologías utilizadas

| Tecnología | Descripción |
|------------|------------|
| **PHP** | Backend y lógica del servidor |
| **MySQL** | Base de datos para gestionar usuarios y contenido |
| **HTML5, CSS3, JavaScript** | Desarrollo frontend |
| **Bootstrap** | Diseño responsive |
| **AJAX** | Interacción asíncrona sin recargar la página |

---

## 📂 Estructura del proyecto

```
📂 DAW2_M12_SintesiA9_My-Netflix
│── 📂 admin            # Archivos de administración
│── 📂 bbdd             # Scripts de la base de datos
│── 📂 css              # Hojas de estilo (CSS)
│── 📂 img              # Imágenes del proyecto
│── 📂 js               # JavaScript para interactividad
│── 📂 vd               # Almacenamiento de videos
│── 📜 admin_page.php   # Panel de administración
│── 📜 conexion.php     # Conexión a la base de datos
│── 📜 detalles.php     # Detalles de cada video
│── 📜 funciones.php    # Funciones reutilizables
│── 📜 index.php        # Página principal
│── 📜 like.php         # Módulo de "Me gusta"
│── 📜 likes.js         # Interacciones de "Me gusta"
│── 📜 login.php        # Página de inicio de sesión
│── 📜 logout.php       # Cerrar sesión
│── 📜 perfil.php       # Perfil de usuario
│── 📜 register.php     # Registro de usuarios
│── 📜 reproducir.php   # Página de reproducción de videos
```

---

## 🔧 Instalación y configuración

Sigue estos pasos para instalar y ejecutar el proyecto en tu entorno local:

### 1️⃣ Clona el repositorio
```bash
git clone https://github.com/ainaorozcogonzalez/DAW2_M12_SintesiA9_My-Netflix.git
```

### 2️⃣ Configura la base de datos
1. Crea una base de datos en MySQL.
2. Importa el archivo SQL ubicado en `bbdd/` para crear las tablas necesarias.

### 3️⃣ Configura la conexión a la base de datos
Modifica `conexion.php` con tus credenciales de acceso a MySQL:
```php
$servername = "localhost";
$username = "tu_usuario";
$password = "tu_contraseña";
$dbname = "nombre_base_datos";
```

### 4️⃣ Inicia el servidor web
Si usas **XAMPP**, **MAMP** o **WAMP**, coloca el proyecto en `htdocs` y asegúrate de que el servidor Apache y MySQL estén activos.

### 5️⃣ Accede a la aplicación
Abre tu navegador y entra en:
```
http://localhost/DAW2_M12_SintesiA9_My-Netflix/
```

---

## 🤝 Contribuciones

¡Las contribuciones son bienvenidas! Para colaborar en el proyecto:
1. Haz un fork del repositorio.
2. Crea una nueva rama:
   ```bash
   git checkout -b feature/nueva-funcionalidad
   ```
3. Realiza cambios y súbelos:
   ```bash
   git commit -m "Añadida nueva funcionalidad"
   git push origin feature/nueva-funcionalidad
   ```
4. Crea un **Pull Request** en GitHub.

---

## 📜 Licencia

Este proyecto está bajo la licencia **MIT**. Puedes consultar más detalles en el archivo `LICENSE`.

---

📌 *Proyecto desarrollado como parte de la síntesis del módulo 12 de DAW2.*

