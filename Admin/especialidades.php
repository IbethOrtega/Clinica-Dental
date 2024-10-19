<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Manejar la eliminación de una especialidad
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare('DELETE FROM especialidades WHERE id = :id');
        $stmt->execute([':id' => $delete_id]);
        header('Location: especialidades.php');
        exit;
    } catch (PDOException $e) {
        die('Error al eliminar especialidad: ' . $e->getMessage());
    }
}

// Obtener la lista de especialidades con sus doctores y sedes
try {
    $stmt = $pdo->query('
        SELECT e.id, e.nombre AS especialidad, d.nombre AS doctor, s.nombre AS sede
        FROM especialidades e
        LEFT JOIN doctores d ON e.doctor_id = d.id
        LEFT JOIN sedes s ON e.sede_id = s.id
    ');
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error al obtener especialidades: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Especialidades</title>
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
            <h2>Especialidades</h2>

            <table class="table">
            <a href="agregar_especialidad.php" class="btn-crear">Agregar Especialidad</a>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Especialidad</th>
                        <th>Doctor</th>
                        <th>Sede</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($especialidades as $especialidad): ?>
                        <tr>
                            <td><?= htmlspecialchars($especialidad['id']) ?></td>
                            <td><?= htmlspecialchars($especialidad['especialidad']) ?></td>
                            <td><?= htmlspecialchars($especialidad['doctor']) ?></td>
                            <td><?= htmlspecialchars($especialidad['sede']) ?></td>
                            <td>
                                <a href="editar_especialidad.php?id=<?= $especialidad['id'] ?>" class="btn-editar">Editar</a>
                                <a href="especialidades.php?delete_id=<?= $especialidad['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta especialidad?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            
      
        </section>
    </main>
</body>
</html>
