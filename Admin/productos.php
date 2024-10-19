<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Obtener productos disponibles
$productos = [];
try {
    $stmt = $pdo->query('SELECT id, nombre, costo FROM productos');
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al obtener productos: ' . $e->getMessage());
}

// Eliminar el producto de la base de datos si se solicita
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $producto_id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare('DELETE FROM productos WHERE id = :id');
        $stmt->bindParam(':id', $producto_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Redirigir a la misma página después de la eliminación
        header('Location: productos.php?mensaje=Producto eliminado exitosamente');
        exit;
    } catch (PDOException $e) {
        error_log('Error al eliminar el producto: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Productos</title>
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
            <h2>Productos Disponibles</h2>

            <?php if (isset($_GET['mensaje'])): ?>
                <p class="success"><?php echo htmlspecialchars($_GET['mensaje'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr data-id="<?php echo htmlspecialchars($producto['id']); ?>">
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td>$<?php echo htmlspecialchars($producto['costo']); ?></td>
                            <td>
                                <button class="btn-eliminar">Eliminar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="agregar_producto.php" class="btn-editar">Agregar Producto</a>
        </section>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
    <script>
        $(document).ready(function() {
            $('.btn-eliminar').on('click', function() {
                var row = $(this).closest('tr');
                var producto_id = row.data('id');

                if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
                    window.location.href = 'productos.php?action=delete&id=' + producto_id;
                }
            });
        });
    </script>
</body>

</html>
