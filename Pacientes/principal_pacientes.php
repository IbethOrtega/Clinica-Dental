<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'paciente') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

try {
    // Obtener información del paciente desde la base de datos
    $stmt = $pdo->prepare('SELECT nombre, email FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    // Obtener el ID del usuario que inició sesión
    $usuario_id = $_SESSION['usuario_id'];

    // Consulta para obtener las citas del paciente que ha iniciado sesión
    $stmt = $pdo->prepare('
    SELECT citas.id, paciente.nombre AS paciente_nombre, doctor.nombre AS doctor_nombre, 
        citas.fecha, citas.hora, citas.estado, citas.motivo, sede.nombre AS sede_nombre, sede.direccion AS direccion,
        especialidad.nombre AS especialidad
    FROM citas
    JOIN pacientes AS paciente ON citas.paciente_id = paciente.id
    JOIN doctores AS doctor ON citas.doctor_id = doctor.id
    JOIN sedes AS sede ON citas.sede_id = sede.id
    JOIN especialidades AS especialidad ON citas.especialidad_id = especialidad.id
    WHERE paciente.usuario_id = :usuario_id
    ORDER BY citas.fecha, citas.hora
    ');
// Enlazar el ID del usuario actual
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

// Ejecutar la consulta
$stmt->execute();
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die('Error en la consulta: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Dental - Página Principal del Paciente</title>
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
                <a href="principal_pacientes.php" class="nav-link">Inicio</a>
                <a href="ver_citas.php" class="nav-link">Ver Citas</a>
                <a href="ver_historial.php" class="nav-link">Historial Clínico</a>
                <a href="perfil.php" class="nav-link">Mi Perfil</a>
                <a href="../logout.php" class="nav-link">Cerrar sesión</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="container-principal">
            <section class="dashboard">
                <h2>Bienvenido, <?php echo htmlspecialchars($paciente['nombre']); ?></h2>
                <div class="content">
                    <h3>Mis Citas Pendientes</h3>
                    <?php if (count($citas) > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Sede</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th> 
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citas as $cita): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cita['fecha']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['hora']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['sede_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['direccion']); ?></td>
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
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>

</html>
