<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

try {
    // Obtener las solicitudes de aplazamiento y eliminación
    $stmt = $pdo->prepare('
        SELECT solicitudes.id, citas.id AS cita_id, especialidades.nombre AS especialidad, doctores.nombre AS doctor, citas.fecha, citas.hora, citas.estado, sedes.nombre AS sede, solicitudes.tipo, solicitudes.estado AS solicitud_estado
        FROM solicitudes
        JOIN citas ON solicitudes.cita_id = citas.id
        JOIN especialidades ON citas.especialidad_id = especialidades.id
        JOIN doctores ON citas.doctor_id = doctores.id
        JOIN sedes ON citas.sede_id = sedes.id
        WHERE solicitudes.estado = "pendiente"
        ORDER BY solicitudes.fecha_solicitud DESC
    ');
    $stmt->execute();
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error en la consulta: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes</title>
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
            <h2>Solicitudes de Citas</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Especialidad</th>
                        <th>Doctor</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Sede</th>
                        <th>Tipo de Solicitud</th>
                        <th>Estado de Solicitud</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($solicitudes) > 0): ?>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($solicitud['especialidad']); ?></td>
                                <td><?php echo htmlspecialchars($solicitud['doctor']); ?></td>
                                <td><?php echo htmlspecialchars($solicitud['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($solicitud['hora']); ?></td>
                                <td><?php echo htmlspecialchars($solicitud['sede']); ?></td>
                                <td><?php echo htmlspecialchars($solicitud['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($solicitud['solicitud_estado']); ?></td>
                                <td>
                                    <button onclick="procesarSolicitud(<?php echo htmlspecialchars($solicitud['id']); ?>, 'aprobar')" class="btn-aprobar">Aprobar</button>
                                    <button onclick="procesarSolicitud(<?php echo htmlspecialchars($solicitud['id']); ?>, 'rechazar')" class="btn-eliminar">Rechazar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No hay solicitudes pendientes.</td>
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
    <script>
        function procesarSolicitud(solicitudId, accion) {
            if (confirm(`¿Estás seguro de que quieres ${accion === 'aprobar' ? 'aprobar' : 'rechazar'} esta solicitud?`)) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'procesar_solicitud_ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert(`Solicitud ${accion === 'aprobar' ? 'aprobada' : 'rechazada'} exitosamente.`);
                            location.reload();
                        } else {
                            alert('Error al procesar la solicitud: ' + response.error);
                        }
                    } else {
                        alert('Error en la solicitud.');
                    }
                };
                xhr.onerror = function() {
                    alert('Error en la solicitud AJAX.');
                };
                xhr.send('solicitud_id=' + encodeURIComponent(solicitudId) + '&accion=' + encodeURIComponent(accion));
            }
        }
    </script>
</body>

</html>
