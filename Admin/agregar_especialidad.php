<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Manejar el formulario de adición de especialidad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $sede_id = $_POST['sede_id'];
    $doctor_id = $_POST['doctor_id'];

    try {
        // Iniciar una transacción
        $pdo->beginTransaction();

        // Obtener el nombre del doctor si se selecciona uno
        $doctor_nombre = '';
        if ($doctor_id) {
            $stmt = $pdo->prepare('SELECT nombre FROM doctores WHERE id = :doctor_id');
            $stmt->execute([':doctor_id' => $doctor_id]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            $doctor_nombre = $doctor['nombre'];
        }

        // Insertar en la tabla especialidades
        $stmt = $pdo->prepare('INSERT INTO especialidades (nombre, sede_id, doctor_id, doctor) VALUES (:nombre, :sede_id, :doctor_id, :doctor)');
        $stmt->execute([
            ':nombre' => $nombre,
            ':sede_id' => $sede_id,
            ':doctor_id' => $doctor_id,
            ':doctor' => $doctor_nombre 
        ]);

        // Actualizar la tabla doctores con la nueva especialidad si se selecciona un doctor
        if ($doctor_id) {
            $stmt = $pdo->prepare('UPDATE doctores SET especialidad = :especialidad_nombre WHERE id = :doctor_id');
            $stmt->execute([
                ':especialidad_nombre' => $nombre,
                ':doctor_id' => $doctor_id
            ]);
        }

        // Confirmar la transacción
        $pdo->commit();

        // Redirigir a la página de especialidades
        header('Location: especialidades.php');
        exit;
    } catch (PDOException $e) {
        // Deshacer la transacción en caso de error
        $pdo->rollBack();
        die('Error al agregar especialidad: ' . $e->getMessage());
    }
}

// Obtener sedes y doctores para los dropdowns
try {
    $sedesStmt = $pdo->query('SELECT id, nombre FROM sedes');
    $sedes = $sedesStmt->fetchAll(PDO::FETCH_ASSOC);

    $doctoresStmt = $pdo->query('SELECT id, nombre FROM doctores');
    $doctores = $doctoresStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error al obtener sedes o doctores: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Especialidad</title>
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
            <h2>Agregar Especialidad</h2>

            <form action="agregar_especialidad.php" method="post">
                <div class="form-group">
                    <label for="nombre">Nombre de Especialidad:</label>
                    <input type="text" id="nombre" name="nombre" required>

                    <label for="sede_id">Sede:</label>
                    <select id="sede_id" name="sede_id" required>
                        <?php foreach ($sedes as $sede): ?>
                            <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="doctor_id">Doctor:</label>
                    <select id="doctor_id" name="doctor_id">
                        <option value="">Ninguno</option>
                        <?php foreach ($doctores as $doctor): ?>
                            <option value="<?= $doctor['id'] ?>"><?= htmlspecialchars($doctor['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-actualizar">Agregar Especialidad</button>
            </form>
        </section>
    </main>
</body>
<footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>

</html>
