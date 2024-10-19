<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos (ajusta la ruta de conexión si es necesario)
require '../Conexion/conexion.php';

// Procesar la acción si se envía una solicitud POST para eliminar un historial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $stmt = $pdo->prepare('DELETE FROM historial_clinico WHERE id = :id');
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error al procesar la acción: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID no válido']);
    }
    exit;
}

try {
    // Obtener todos los historiales clínicos con el nombre del paciente
    $stmt = $pdo->prepare('
        SELECT historial_clinico.id, historial_clinico.fecha, historial_clinico.descripcion, historial_clinico.tratamiento, 
               doctores.nombre AS doctor, usuarios.nombre AS paciente
        FROM historial_clinico
        JOIN doctores ON historial_clinico.doctor_id = doctores.id
        JOIN pacientes ON historial_clinico.paciente_id = pacientes.id
        JOIN usuarios ON pacientes.usuario_id = usuarios.id
        ORDER BY historial_clinico.fecha DESC
    ');
    $stmt->execute();
    $historiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error en la consulta: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Clínico</title>
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
            <h2>Historiales Clínicos</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Tratamiento</th>
                        <th>Doctor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($historiales) > 0): ?>
                        <?php foreach ($historiales as $historial): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($historial['paciente']); ?></td>
                                <td><?php echo htmlspecialchars($historial['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($historial['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($historial['tratamiento']); ?></td>
                                <td><?php echo htmlspecialchars($historial['doctor']); ?></td>
                                <td>
                                    <a href="editar_historial.php?id=<?php echo htmlspecialchars($historial['id']); ?>" class="btn-editar">Editar</a>
                                    <button class="btn-eliminar" data-id="<?php echo htmlspecialchars($historial['id']); ?>" class="btn-eliminar" data-accion="eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este historial?');">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No hay historiales clínicos disponibles.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>



    <script>
        $(document).ready(function() {
            $('.btn-eliminar').click(function() {
                var idHistorial = $(this).data('id');
                var accion = $(this).data('accion');

                $.ajax({
                    type: 'POST',
                    url: 'ver_historial.php',
                    data: {
                        accion: accion,
                        id: idHistorial
                    },
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + result.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error en la solicitud: ' + error);
                    }
                });
            });
        });
    </script>
</body>
<footer class="footer">
    <div class="container">
        <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
    </div>
</footer>

</html>