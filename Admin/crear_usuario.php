<?php
session_start();
require '../Conexion/conexion.php';

// Verificar si el usuario está autenticado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    try {
        // Insertar en la tabla usuarios
        $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, contrasena, rol, fecha_nacimiento, direccion, telefono) VALUES (:nombre, :email, :contrasena, :rol, :fecha_nacimiento, :direccion, :telefono)');
        $stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'contrasena' => ($contrasena), 
            'rol' => $rol,
            'fecha_nacimiento' => $fecha_nacimiento,
            'direccion' => $direccion,
            'telefono' => $telefono
        ]);

        // Obtener el ID del usuario recién insertado
        $usuario_id = $pdo->lastInsertId();

        // Insertar en la tabla correspondiente según el rol
        if ($rol === 'doctor') {
            $especialidad = $_POST['especialidad'];
            $sede_id = $_POST['sede_id'];

            $stmtDoctor = $pdo->prepare('INSERT INTO doctores (usuario_id, especialidad, sede_id, telefono, nombre, direccion, email, fecha_nacimiento) VALUES (:usuario_id, :especialidad, :sede_id, :telefono, :nombre, :direccion, :email, :fecha_nacimiento)');
            $stmtDoctor->execute([
                'usuario_id' => $usuario_id,
                'especialidad' => $especialidad,
                'sede_id' => $sede_id,
                'telefono' => $telefono,
                'nombre' => $nombre,
                'direccion' => $direccion,
                'email' => $email,
                'fecha_nacimiento' => $fecha_nacimiento
            ]);
        } elseif ($rol === 'paciente') {
            $stmtPaciente = $pdo->prepare('INSERT INTO pacientes (usuario_id, direccion, telefono, nombre, email, fecha_nacimiento) VALUES (:usuario_id, :direccion, :telefono, :nombre, :email, :fecha_nacimiento)');
            $stmtPaciente->execute([
                'usuario_id' => $usuario_id,
                'direccion' => $direccion,
                'telefono' => $telefono,
                'nombre' => $nombre,
                'email' => $email,
                'fecha_nacimiento' => $fecha_nacimiento
            ]);
        }

        $mensaje = 'Usuario creado exitosamente.';
    } catch (PDOException $e) {
        $mensaje = 'Error al crear el usuario: ' . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>

<body>
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
    <main>
        <section class="container-principal">
            <h2>Crear Usuario</h2>
            <?php if ($mensaje): ?>
                <p><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>
            <form action="crear_usuario.php" method="post">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="contrasena">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required>

                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol" required>
                        <option value="administrador">Administrador</option>
                        <option value="doctor">Doctor</option>
                        <option value="paciente">Paciente</option>
                    </select>

                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion">

                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono">

                    <div id="doctor-fields" style="display: none;">
                        <label for="especialidad">Especialidad:</label>
                        <input type="text" id="especialidad" name="especialidad">

                        <label for="sede_id">Sede:</label>
                        <input type="number" id="sede_id" name="sede_id">
                    </div>
                </div>
                <button type="submit" class="btn-actualizar">Crear Usuario</button>
            </form>

            <script>
                document.getElementById('rol').addEventListener('change', function() {
                    var doctorFields = document.getElementById('doctor-fields');
                    if (this.value === 'doctor') {
                        doctorFields.style.display = 'block';
                    } else {
                        doctorFields.style.display = 'none';
                    }
                });
            </script>
        </section>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>

</html>