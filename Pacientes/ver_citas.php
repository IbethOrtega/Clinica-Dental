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
     // Obtener el ID del usuario que inició sesión
     $usuario_id = $_SESSION['usuario_id'];

     // Consulta para obtener las citas del paciente que ha iniciado sesión junto con el estado de la solicitud
     $stmt = $pdo->prepare('
     SELECT citas.id, 
            paciente.nombre AS paciente_nombre, 
            doctor.nombre AS doctor_nombre, 
            citas.fecha, 
            citas.hora, 
            citas.estado AS estado_cita, 
            citas.motivo, 
            citas.costo, 
            citas.estado,
            sede.nombre AS sede_nombre,
            especialidad.nombre AS especialidad,
            solicitud.estado AS estado_solicitud
     FROM citas
     JOIN pacientes AS paciente ON citas.paciente_id = paciente.id
     JOIN doctores AS doctor ON citas.doctor_id = doctor.id
     JOIN sedes AS sede ON citas.sede_id = sede.id
     JOIN especialidades AS especialidad ON citas.especialidad_id = especialidad.id
     LEFT JOIN solicitudes AS solicitud ON citas.id = solicitud.cita_id
     WHERE paciente.usuario_id = :usuario_id
     ORDER BY citas.fecha, citas.hora
 ');
    // Enlazar el ID del usuario actual
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

    // Ejecutar la consulta
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el estado de las solicitudes para las citas del paciente
    $cita_ids = array_column($citas, 'id');
    if (count($cita_ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($cita_ids), '?'));
        $stmt = $pdo->prepare('
            SELECT cita_id, tipo, estado 
            FROM solicitudes 
            WHERE cita_id IN (' . $placeholders . ')
        ');
        $stmt->execute($cita_ids);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Indexar solicitudes por cita_id
        $solicitud_estado = [];
        foreach ($solicitudes as $solicitud) {
            $solicitud_estado[$solicitud['cita_id']] = $solicitud;
        }
    } else {
        $solicitud_estado = [];
    }
} catch (PDOException $e) {
    die('Error en la consulta: ' . $e->getMessage());
}

// Verificar si el usuario es administrador
$is_admin = $_SESSION['rol'] === 'administrador';

// Mensaje de estado de la solicitud, si existe
$mensaje_estado = $_GET['mensaje_estado'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Citas</title>
    <link rel="stylesheet" href="/css/styles.css">
    <script>
        function solicitarEliminacion(citaId) {
            if (confirm('¿Estás seguro de que quieres eliminar esta cita?')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'eliminar_cita_ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert('Solicitud de eliminación enviada exitosamente.');
                            location.reload(); 
                        } else {
                            alert('Error al solicitar la eliminación: ' + response.error);
                        }
                    } else {
                        alert('Error en la solicitud.');
                    }
                };
                xhr.onerror = function() {
                    alert('Error en la solicitud AJAX.');
                };
                xhr.send('cita_id=' + encodeURIComponent(citaId));
            }
        }
    </script>
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
            <h2>Mis Citas</h2>
            <?php if ($mensaje_estado): ?>
                <p class="alerta"><?php echo htmlspecialchars($mensaje_estado); ?></p>
            <?php endif; ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Especialidad</th>
                        <th>Doctor</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado de la Cita</th>
                        <th>Estado de Solicitud</th>
                        <th>Sede</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($citas) > 0): ?>
                        <?php foreach ($citas as $cita): ?>
                            <tr id="cita-<?php echo htmlspecialchars($cita['id']); ?>">
                                <td><?php echo htmlspecialchars($cita['especialidad']); ?></td>
                                <td><?php echo htmlspecialchars($cita['doctor_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($cita['hora']); ?></td>
                                <td><?php echo htmlspecialchars($cita['estado']); ?></td>
                                <td><?php echo htmlspecialchars($cita['estado_solicitud']); ?></td>
                                <td><?php echo htmlspecialchars($cita['sede_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['costo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php
                                    if (isset($solicitud_estado[$cita['id']])) {
                                        $solicitud = $solicitud_estado[$cita['id']];
                        
                                        if ($solicitud['estado'] === 'aprobada') {
                                            if ($solicitud['tipo'] === 'aplazamiento') {
                                                echo '<br>Tu solicitud de aplazamiento ha sido aprobada.';
                                            } elseif ($solicitud['tipo'] === 'eliminacion') {
                                                echo '<br>Tu solicitud de eliminación ha sido aprobada. Esta cita ya no está agendada.';
                                            }
                                        }
                                    }
                                    ?>
                                     <a href="reagendar_citas.php?cita_id=<?php echo htmlspecialchars($cita['id']); ?>" class="btn-Agendar">Reagendar</a>
                                     <button onclick="solicitarEliminacion(<?php echo htmlspecialchars($cita['id']); ?>)" class="btn-eliminar">Solicitar Eliminación</button>
                                </td>
                                <td>
                                   
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No tienes citas agendadas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>

</html>
