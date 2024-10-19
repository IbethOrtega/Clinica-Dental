<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Inicializar variables para manejar errores y mensajes
$error = '';
$mensaje = '';

// Procesar el formulario cuando se envía una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];

    // Validar los datos del formulario
    if (empty($nombre) || empty($direccion)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        try {
            // Insertar la nueva sede en la base de datos
            $stmt = $pdo->prepare('INSERT INTO sedes (nombre, direccion) VALUES (:nombre, :direccion)');
            $stmt->execute([
                ':nombre' => $nombre,
                ':direccion' => $direccion
            ]);

            $mensaje = 'Sede creada exitosamente.';
        } catch (PDOException $e) {
            $error = 'Error al crear la sede: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Sede</title>
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
            <h2>Crear Nueva Sede</h2>
            <?php if (!empty($mensaje)): ?>
                <p class="success"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <form method="post" action="crear_sede.php">
                <div class="form-group">
                    <label for="nombre">Nombre de la Sede:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" required>
                </div>
                <button type="submit" class="btn-Agendar">Guardar Sede</button>
                <a href="sedes.php" class="btn-cancelar">Cancelar</a>
            </form>
        </section>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
