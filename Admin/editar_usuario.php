<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Verificar si se ha enviado un ID válido
$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID de usuario no proporcionado.');
}

// Inicializar $sede_id
$sede_id = null;

// Procesar la actualización del usuario si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $especialidad = $_POST['especialidad'] ?? '';  
    $sede_id = $_POST['sedes'] ?? null; 

    if ($nombre && $email && $direccion && $telefono && $fecha_nacimiento && $rol) {
        try {
            // Actualizar el usuario en la tabla `usuarios`
            $stmt = $pdo->prepare('UPDATE usuarios 
                                   SET nombre = :nombre, email = :email, direccion = :direccion, telefono = :telefono, fecha_nacimiento = :fecha_nacimiento, rol = :rol 
                                   WHERE id = :id');
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':direccion' => $direccion,
                ':telefono' => $telefono,
                ':fecha_nacimiento' => $fecha_nacimiento,
                ':rol' => $rol,
                ':id' => $id
            ]);

            // Limpiar las tablas específicas para el rol anterior
            $stmt = $pdo->prepare('DELETE FROM doctores WHERE usuario_id = :id');
            $stmt->execute([':id' => $id]);

            $stmt = $pdo->prepare('DELETE FROM pacientes WHERE usuario_id = :id');
            $stmt->execute([':id' => $id]);

            // Insertar o actualizar en la tabla correspondiente según el nuevo rol
            if ($rol === 'doctor') {
                // Prepara la consulta SQL
                $stmt = $pdo->prepare('
                    INSERT INTO doctores (usuario_id, sede_id, nombre, direccion, telefono, fecha_nacimiento, especialidad, email) 
                    VALUES (:id, :sede_id, :nombre, :direccion, :telefono, :fecha_nacimiento, :especialidad, :email)
                    ON DUPLICATE KEY UPDATE 
                        sede_id = VALUES(sede_id), 
                        nombre = VALUES(nombre), 
                        direccion = VALUES(direccion), 
                        telefono = VALUES(telefono), 
                        fecha_nacimiento = VALUES(fecha_nacimiento), 
                        especialidad = VALUES(especialidad), 
                        email = VALUES(email)
                ');
                $stmt->execute([
                    ':id' => $id,
                    ':sede_id' => $sede_id, 
                    ':direccion' => $direccion,
                    ':telefono' => $telefono,
                    ':fecha_nacimiento' => $fecha_nacimiento,
                    ':especialidad' => $especialidad,
                    ':email' => $email
                ]);
            } elseif ($rol === 'paciente') {
                $stmt = $pdo->prepare('INSERT INTO pacientes (usuario_id, nombre, direccion, telefono, fecha_nacimiento, email) 
                                       VALUES (:id, :nombre, :direccion, :telefono, :fecha_nacimiento, :email)
                                       ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), direccion = VALUES(direccion), telefono = VALUES(telefono), fecha_nacimiento = VALUES(fecha_nacimiento), email = VALUES(email)');
                $stmt->execute([
                    ':id' => $id,
                    ':nombre' => $nombre,
                    ':direccion' => $direccion,
                    ':telefono' => $telefono,
                    ':fecha_nacimiento' => $fecha_nacimiento,
                    ':email' => $email
                ]);
            } elseif ($rol === 'administrador') {
                $stmt = $pdo->prepare('INSERT INTO administradores (usuario_id, nombre, direccion, telefono, fecha_nacimiento, email) 
                                       VALUES (:id, :nombre, :direccion, :telefono, :fecha_nacimiento, :email)
                                       ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), direccion = VALUES(direccion), telefono = VALUES(telefono), fecha_nacimiento = VALUES(fecha_nacimiento), email = VALUES(email)');
                $stmt->execute([
                    ':id' => $id,
                    ':nombre' => $nombre,
                    ':direccion' => $direccion,
                    ':telefono' => $telefono,
                    ':fecha_nacimiento' => $fecha_nacimiento,
                    ':email' => $email
                ]);
            }

            header('Location: usuarios.php');
            exit;
        } catch (PDOException $e) {
            die('Error al actualizar el usuario: ' . $e->getMessage());
        }
    } else {
        echo 'Por favor, completa todos los campos.';
    }
}

// Obtener los detalles del usuario
try {
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die('Usuario no encontrado.');
    }

    // Si el usuario es doctor, obtener la especialidad y sede
    if ($usuario['rol'] === 'doctor') {
        $stmt = $pdo->prepare('SELECT especialidad, sede_id FROM doctores WHERE usuario_id = :id');
        $stmt->execute([':id' => $id]);
        $doctorData = $stmt->fetch(PDO::FETCH_ASSOC);
        $especialidad = $doctorData['especialidad'] ?? '';
        $sede_id = $doctorData['sede_id'] ?? null;
    }

    // Obtener la lista de sedes
    try {
        $stmt = $pdo->query('SELECT id, nombre FROM sedes');
        $sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Error al obtener sedes: ' . $e->getMessage());
    }
} catch (PDOException $e) {
    die('Error al obtener datos: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="/css/styles.css">
    <script>
        function toggleEspecialidad() {
            var rol = document.getElementById('rol').value;
            var especialidadSection = document.getElementById('especialidad-section');
            if (rol === 'doctor') {
                especialidadSection.style.display = 'block';
            } else {
                especialidadSection.style.display = 'none';
            }
        }

        window.onload = function() {
            toggleEspecialidad();
        };
    </script>
<header class="header">
        <div class="container">
            <h1 class="logo">
                <img src="/Imagenes/odontologia.png" alt="Logo de Clínica Dental">
                Clínica Dental
            </h1>
            <nav class="nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="principalAdmin.php" class="nav-link">Inicio</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link">Administrar</a>
                        <ul class="dropdown-menu">
                            <li><a href="ver_citas.php" class="dropdown-link">Ver Citas</a></li>
                            <li><a href="ver_historial.php" class="dropdown-link">Historial Clínico</a></li>
                            <li><a href="perfil.php" class="dropdown-link">Mi Perfil</a></li>
                            <li><a href="usuarios.php" class="dropdown-link">Usuarios</a></li>
                            <li><a href="ver_solicitudes.php" class="dropdown-link">Solicitudes</a></li>
                            <li><a href="sedes.php" class="dropdown-link">Sedes</a></li>
                            <li><a href="especialidades.php" class="dropdown-link">Especialidades</a></li>
                            <li><a href="productos.php" class="dropdown-link">Productos</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="../logout.php" class="nav-link">Cerrar sesión</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</head>

<body>
    <section class="container">
        <h2>Editar Usuario</h2>
        <form method="POST">
            <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>

            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>" required>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" required>

            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento'] ?? '') ?>" required>

            <label for="rol">Rol:</label>
            <select id="rol" name="rol" onchange="toggleEspecialidad()" required>
                <option value="">Seleccione un rol</option>
                <option value="doctor" <?= ($usuario['rol'] ?? '') === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                <option value="paciente" <?= ($usuario['rol'] ?? '') === 'paciente' ? 'selected' : '' ?>>Paciente</option>
                <option value="administrador" <?= ($usuario['rol'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
            </select>

            <div id="especialidad-section" style="display: <?= ($usuario['rol'] ?? '') === 'doctor' ? 'block' : 'none' ?>">
                <label for="especialidad">Especialidad:</label>
                <input type="text" id="especialidad" name="especialidad" value="<?= htmlspecialchars($especialidad ?? '') ?>">
            </div>

            <label for="sedes">Sede:</label>
            <select id="sedes" name="sedes">
                <?php foreach ($sedes as $sede): ?>
                    <option value="<?= $sede['id'] ?>" <?= isset($sede_id) && ($sede['id'] == $sede_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sede['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            
            </div>
            <input type="submit" class="btn-actualizar" value="Actualizar Usuario">
        </form>
    </section>
</body>
<footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</html>
