<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de doctor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

try {
    // Obtener información del doctor desde la base de datos
    $stmt = $pdo->prepare('SELECT nombre FROM usuarios WHERE id = :id AND rol = "doctor"');
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        throw new Exception('No se encontró información del doctor.');
    }

    // Obtener el ID del usuario que inició sesión
    $usuario_id = $_SESSION['usuario_id'];

    // Consulta para obtener las citas del paciente que ha iniciado sesión
    $stmt = $pdo->prepare('
    SELECT citas.id, paciente.nombre AS paciente_nombre, doctor.nombre AS doctor_nombre, 
           citas.fecha, citas.hora, citas.estado, citas.motivo, sede.nombre AS sede_nombre,
           especialidad.nombre AS especialidad
    FROM citas
    JOIN pacientes AS paciente ON citas.paciente_id = paciente.id
    JOIN doctores AS doctor ON citas.doctor_id = doctor.id
    JOIN sedes AS sede ON citas.sede_id = sede.id
    JOIN especialidades AS especialidad ON citas.especialidad_id = especialidad.id
    WHERE doctor.usuario_id = :usuario_id
    ORDER BY citas.fecha, citas.hora
');
    // Enlazar el ID del usuario actual
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

    // Ejecutar la consulta
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);


    if ($citas === false) {
        throw new Exception('Error al obtener las citas.');
    }
} catch (PDOException $e) {
    die('Error en la consulta: ' . $e->getMessage());
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Dental - Página Principal del Doctor</title>
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
                <a href="principal_doctores.php" class="nav-link">Inicio</a>
                <a href="ver_citas.php" class="nav-link">Ver Citas</a>
                <a href="ver_paciente.php" class="nav-link">Mis pacientes</a>
                <a href="seguimiento.php"class="nav-link">Seguimiento</a>
                <a href="ver_historial.php"class="nav-link">Historiales</a>
                <a href="perfil.php" class="nav-link">Mi Perfil</a>
                <a href="../logout.php" class="nav-link">Cerrar sesión</a>
            </nav>
        </div>
    </header>

    <main class="container-principal">
        <section class="dashboard">
            <h2>Bienvenido, <?php echo htmlspecialchars($doctor['nombre']); ?></h2>

            <div class="content">
                <h3>Citas Pendientes</h3>
                <?php if (!empty($citas)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas as $cita): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($cita['fecha']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('H:i', strtotime($cita['hora']))); ?></td>
                                    <td><?php echo htmlspecialchars($cita['paciente_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($cita['estado']); ?></td>
                                    <td>
                                        <a href="ver_citas.php?id=<?php echo htmlspecialchars($cita['id']); ?>" class="btn-ver-citas">Administrar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No tienes citas pendientes.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
