<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos (ajusta la ruta de conexión si es necesario)
require '../Conexion/conexion.php';

try {
    // Obtener información del administrador desde la base de datos
    $stmt = $pdo->prepare('SELECT nombre, email FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener el número de citas agendadas
    $stmt = $pdo->query('SELECT COUNT(*) FROM citas WHERE estado = "agendada"');
    $numCitasAgendadas = $stmt->fetchColumn();

    // Obtener el número de doctores
    $stmt = $pdo->query('SELECT COUNT(*) FROM usuarios');
    $numUsuarios = $stmt->fetchColumn();

    // Obtener el número de especialidades
    $stmt = $pdo->query('SELECT COUNT(*) FROM especialidades');
    $numEspecialidades = $stmt->fetchColumn();

    // Obtener el número de sedes
    $stmt = $pdo->query('SELECT COUNT(*) FROM sedes');
    $numSedes = $stmt->fetchColumn();

} catch (PDOException $e) {
    die('Error en la consulta: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Admin</title>
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
            <section class="dashboard">
                <h2>Bienvenido, <?php echo htmlspecialchars($paciente['nombre']); ?></h2>
                <div class="dashboard-summary">
                    <div class="summary-item">
                        <h3>Citas Agendadas</h3>
                        <p><?php echo htmlspecialchars($numCitasAgendadas); ?> citas</p>
                        <a href="ver_citas.php" class="btn-view">Ver Detalles</a>
                    </div>
                    <div class="summary-item">
                        <h3>Usuarios</h3>
                        <p><?php echo htmlspecialchars($numUsuarios); ?> usuarios</p>
                        <a href="usuarios.php" class="btn-view">Gestionar Usuarios</a>
                    </div>
                    <div class="summary-item">
                        <h3>Especialidades</h3>
                        <p><?php echo htmlspecialchars($numEspecialidades); ?> especialidades</p>
                        <a href="especialidades.php" class="btn-view">Gestionar Especialidades</a>
                    </div>
                    <div class="summary-item">
                        <h3>Sedes</h3>
                        <p><?php echo htmlspecialchars($numSedes); ?> sedes</p>
                        <a href="sedes.php" class="btn-view">Gestionar Sedes</a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="footer">
            <div class="container">
                <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>
</body>
</html>
