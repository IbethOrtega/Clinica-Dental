<?php
require '../Conexion/conexion.php';

$errores = [];
$mensaje = "";

// Procesar formulario de agregar/editar sede
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $id = $_POST['id'] ?? null;

    if (empty($nombre) || empty($direccion)) {
        $errores[] = 'El nombre y la dirección son obligatorios.';
    }

    if (empty($errores)) {
        if ($id) {
            // Actualizar sede
            $stmt = $pdo->prepare("UPDATE sedes SET nombre = :nombre, direccion = :direccion, telefono = :telefono WHERE id = :id");
            $stmt->execute(['nombre' => $nombre, 'direccion' => $direccion, 'telefono' => $telefono, 'id' => $id]);
            $mensaje = "Sede actualizada con éxito.";
        } else {
            // Insertar nueva sede
            $stmt = $pdo->prepare("INSERT INTO sedes (nombre, direccion, telefono) VALUES (:nombre, :direccion, :telefono)");
            $stmt->execute(['nombre' => $nombre, 'direccion' => $direccion, 'telefono' => $telefono]);
            $mensaje = "Sede agregada con éxito.";
        }
    }
}

// Obtener sedes para listar
$stmt = $pdo->query("SELECT * FROM sedes");
$sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar eliminación de sede
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM sedes WHERE id = :id");
    $stmt->execute(['id' => $id]);
    header("Location: sedes.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sedes</title>
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
        <div class="container-principal">
            <h2>Listado de Sedes</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sedes as $sede) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sede['id']); ?></td>
                            <td><?php echo htmlspecialchars($sede['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($sede['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($sede['telefono']); ?></td>
                            <td>
                                <a href="editar_sede.php?id=<?php echo htmlspecialchars($sede['id']); ?>" class="btn-editar">Editar</a>
                                <a href="sedes.php?eliminar=<?php echo htmlspecialchars($sede['id']); ?>" class="btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta sede?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <a href="crear_sede.php" class="btn-crear">Añadir Sede</a>
            </table>
    </main>
</body>
<footer class="footer">
    <div class="container">
        <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
    </div>
</footer>

</html>