<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clínica Odontológica</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <header class="header">
        <div class="container">
            <h1 class="logo">
                <img src="Imagenes/odontologia.png" alt="Logo de Clínica Odontológica">
                Clínica Dental
            </h1>
            <nav class="nav">
                <a href="index.php" class="nav-link">Inicio</a>
                <a href="#servicios" class="nav-link">Servicios</a>
                <a href="#contacto" class="nav-link">Contacto</a>
                <a href="login.php" class="nav-link">Iniciar Sesión</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Bienvenido a la Clínica Odontológica</h2>
                <p>Ofrecemos atención dental de calidad en un ambiente cómodo y profesional.</p>
                <a href="#servicios" class="btn-primary">Ver Servicios</a>
            </div>
        </section>

        <section id="servicios" class="features">
            <div class="container">
                <h2>Nuestros Servicios</h2>
                <div class="feature-box">
                    <img src="Imagenes/clinic2.webp" alt="Servicios Dentales">
                    <h3>Servicios Dentales</h3>
                    <p>Desde limpiezas dentales hasta tratamientos especializados.</p>
                </div>
                <div class="feature-box">
                    <img src="Imagenes/clinic1.jpg" alt="Ortodoncia">
                    <h3>Ortodoncia</h3>
                    <p>Corrección de problemas de alineación y mordida.</p>
                </div>
                <div class="feature-box">
                    <img src="Imagenes/clinic3.jpg" alt="Blanqueamiento Dental">
                    <h3>Blanqueamiento Dental</h3>
                    <p>Recupera la blancura natural de tus dientes.</p>
                </div>
            </div>
        </section>

        <section id="contacto" class="contact">
            <div class="container">
                <h2>Contáctanos</h2>
                <p>¿Tienes preguntas? ¡Estamos aquí para ayudarte!</p>
                <form action="enviar_contacto.php" method="post">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                    
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="mensaje">Mensaje:</label>
                    <textarea id="mensaje" name="mensaje" rows="4" required></textarea>
                    
                    <button type="submit" class="btn-primary">Enviar</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Odontológica. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>

</html>
