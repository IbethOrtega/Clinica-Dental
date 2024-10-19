<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

require '../Conexion/conexion.php';

$citas = [];
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare('
    SELECT citas.id, paciente.nombre AS paciente_nombre, doctor.nombre AS doctor_nombre, 
           citas.fecha, citas.hora, citas.estado, citas.motivo AS motivo, citas.motivo AS motivo, citas.costo, sede.direccion AS sede_direccion
    FROM citas
    JOIN pacientes AS paciente ON citas.paciente_id = paciente.id
    JOIN doctores AS doctor ON citas.doctor_id = doctor.id
    JOIN sedes AS sede ON citas.sede_id = sede.id
    ORDER BY citas.fecha, citas.hora
');
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error en la consulta: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $id_cita = $_POST['id_cita'];

    if ($accion === 'eliminar') {
        try {
            // Verificar si la cita existe
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM citas WHERE id = :id');
            $stmt->execute([':id' => $id_cita]);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                echo json_encode(['success' => false, 'error' => 'La cita no existe.']);
                exit;
            }

            // Eliminar la cita directamente
            $stmt = $pdo->prepare('DELETE FROM citas WHERE id = :id');
            $stmt->execute([':id' => $id_cita]);
            echo json_encode(['success' => true, 'message' => 'Cita eliminada con éxito.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error al procesar la acción: ' . $e->getMessage()]);
        }
    } elseif ($accion === 'reagendar') {
        $nueva_fecha = $_POST['nueva_fecha'];
        $nueva_hora = $_POST['nueva_hora'];

        try {
            // Verificar si la cita existe
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM citas WHERE id = :id');
            $stmt->execute([':id' => $id_cita]);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                echo json_encode(['success' => false, 'error' => 'La cita no existe.']);
                exit;
            }

            // Actualizar la cita con la nueva fecha y hora
            $stmt = $pdo->prepare('UPDATE citas SET fecha = :nueva_fecha, hora = :nueva_hora WHERE id = :id');
            $stmt->execute([':nueva_fecha' => $nueva_fecha, ':nueva_hora' => $nueva_hora, ':id' => $id_cita]);

            // Insertar la solicitud de aplazamiento
            $stmt = $pdo->prepare('INSERT INTO solicitudes (cita_id, tipo, fecha_solicitud) VALUES (:cita_id, :tipo, NOW())');
            $stmt->execute([':cita_id' => $id_cita, ':tipo' => 'aplazamiento']);

            echo json_encode(['success' => true, 'message' => 'Cita reagendada con éxito.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error al procesar la acción: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Acción no reconocida.']);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Dental - Ver Citas</title>
    <link rel="stylesheet" href="/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <h2>Ver Citas</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Doctor</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Descripción</th>
                        <th>Sede</th>
                        <th>Dirección</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($citas)): ?>
                        <tr>
                            <td colspan="10">No hay citas programadas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($citas as $cita): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cita['id']); ?></td>
                                <td><?php echo htmlspecialchars($cita['paciente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['doctor_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cita['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($cita['hora']); ?></td>
                                <td><?php echo htmlspecialchars($cita['estado']); ?></td>
                                <td><?php echo htmlspecialchars($cita['motivo']); ?></td>
                                <td><?php echo htmlspecialchars($cita['sede_direccion']); ?></td>
                                <td><?php echo htmlspecialchars($cita['sede_direccion']); ?></td>
                                <td><?php echo htmlspecialchars($cita['costo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <button class="btn-eliminar" data-id="<?php echo htmlspecialchars($cita['id']); ?>" data-accion="eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta cita?');">Eliminar</button>
                                    <button class="btn-Reagendar" data-id="<?php echo htmlspecialchars($cita['id']); ?>" data-accion="reagendar" onclick="return confirm('¿Estás seguro de que deseas reagendar esta cita?');">Reagendar</button>
                                </td>
                                
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <a href="agendar_citas.php?cita_id=<?php echo htmlspecialchars($cita['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-Agendar">Crear Cita</a>
            </table>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        $(document).ready(function() {
            $('.btn-eliminar').click(function() {
                var idCita = $(this).data('id');
                var accion = 'eliminar';

                $.ajax({
                    type: 'POST',
                    url: 'ver_citas.php',
                    data: {
                        accion: accion,
                        id_cita: idCita
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.error);
                        }
                    }
                });

                return false;
            });

            $('.btn-Reagendar').click(function() {
                var idCita = $(this).data('id');
                var nuevaFecha = prompt('Introduce la nueva fecha (YYYY-MM-DD):');
                var nuevaHora = prompt('Introduce la nueva hora (HH:MM):');

                if (nuevaFecha && nuevaHora) {
                    $.ajax({
                        type: 'POST',
                        url: 'ver_citas.php',
                        data: {
                            accion: 'reagendar',
                            id_cita: idCita,
                            nueva_fecha: nuevaFecha,
                            nueva_hora: nuevaHora
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                location.reload();
                            } else {
                                alert(response.error);
                            }
                        }
                    });
                }

                return false;
            });
        });
    </script>
</body>

</html>
