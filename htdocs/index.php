<?php
// Redirect to example_update.php or show a simple index
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strava Club Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #FC4C02;
            text-align: center;
        }
        .btn {
            display: inline-block;
            background: #FC4C02;
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
            text-align: center;
        }
        .btn:hover {
            background: #e63900;
        }
        .features {
            margin: 20px 0;
        }
        .features li {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚴‍♂️ Strava Club Tracker</h1>
        
        <p>Bienvenido al Strava Club Tracker - Una herramienta para rastrear el progreso de clubes de Strava y generar dashboards personalizados.</p>
        
        <?php
        // Detectar si estamos en local o en GitHub Pages
        $isLocal = (isset($_SERVER['HTTP_HOST']) && 
                   (strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || 
                    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false));
        $isGitHubPages = (isset($_SERVER['HTTP_HOST']) && 
                         strpos($_SERVER['HTTP_HOST'], 'github.io') !== false);
        
        if ($isLocal) {
            echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;">';
            echo '<h3>🏠 Modo Local</h3>';
            echo '<p>Ejecutándose en: <strong>http://127.0.0.1:8000/</strong></p>';
            echo '<p>✅ Todas las funcionalidades disponibles</p>';
            echo '</div>';
        } elseif ($isGitHubPages) {
            echo '<div style="background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;">';
            echo '<h3>🌐 GitHub Pages</h3>';
            echo '<p>Sitio web: <strong>https://mestre211.github.io/strava-club-tracker/</strong></p>';
            echo '<p>📖 Documentación y código fuente disponibles</p>';
            echo '<p>⚠️ Para usar las funcionalidades, configura el proyecto localmente</p>';
            echo '</div>';
        }
        ?>
        
        <h2>Características:</h2>
        <ul class="features">
            <li>✅ Rastrea uno o múltiples clubes</li>
            <li>✅ Totales por actividad y generales</li>
            <li>✅ Destaca mejores esfuerzos individuales</li>
            <li>✅ Listas de líderes por categoría</li>
            <li>✅ Exportación a CSV</li>
            <li>✅ Páginas HTML personalizables</li>
        </ul>
        
        <?php if ($isLocal): ?>
        <div style="text-align: center; margin-top: 30px;">
            <a href="oauth_strava.php" class="btn">🔐 Configurar OAuth (Recomendado)</a>
            <a href="strava_downloader.php" class="btn">📥 Descargar datos de Strava</a>
        </div>
        
        <div style="text-align: center; margin-top: 10px;">
            <a href="generate_reports.php" class="btn">📊 Generar reportes HTML</a>
        </div>
        
        <div style="text-align: center; margin-top: 10px;">
            <a href="debug_token.php" style="color: #666; text-decoration: none; font-size: 14px; margin: 0 10px;">🔍 Diagnóstico Token</a>
            <a href="token_helper.php" style="color: #666; text-decoration: none; font-size: 14px; margin: 0 10px;">🔧 Token Manual</a>
            <a href="example_update.php" style="color: #666; text-decoration: none; font-size: 14px; margin: 0 10px;">⚙️ OAuth Original</a>
        </div>
        <?php else: ?>
        <div style="text-align: center; margin-top: 30px;">
            <a href="https://github.com/Mestre211/strava-club-tracker" class="btn" target="_blank">📦 Ver código en GitHub</a>
            <a href="https://github.com/Mestre211/strava-club-tracker/archive/refs/heads/main.zip" class="btn" target="_blank">⬇️ Descargar proyecto</a>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;">
                <h3>🚀 Para usar este proyecto:</h3>
                <ol style="text-align: left; max-width: 600px; margin: 0 auto;">
                    <li><strong>Clona el repositorio:</strong><br>
                        <code style="background: #f8f9fa; padding: 5px; display: block; margin: 5px 0;">git clone https://github.com/Mestre211/strava-club-tracker.git</code>
                    </li>
                    <li><strong>Instala las dependencias:</strong><br>
                        <code style="background: #f8f9fa; padding: 5px; display: block; margin: 5px 0;">cd strava-club-tracker/lib && composer install</code>
                    </li>
                    <li><strong>Inicia el servidor local:</strong><br>
                        <code style="background: #f8f9fa; padding: 5px; display: block; margin: 5px 0;">cd ../htdocs && php -S 127.0.0.1:8000</code>
                    </li>
                    <li><strong>Accede a:</strong> <code>http://127.0.0.1:8000/</code></li>
                </ol>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <p><strong>Estado del servidor:</strong> ✅ Funcionando</p>
            <p><strong>PHP:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Directorio:</strong> <?php echo __DIR__; ?></p>
        </div>
    </div>
</body>
</html>
