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
        
        <h2>Características:</h2>
        <ul class="features">
            <li>✅ Rastrea uno o múltiples clubes</li>
            <li>✅ Totales por actividad y generales</li>
            <li>✅ Destaca mejores esfuerzos individuales</li>
            <li>✅ Listas de líderes por categoría</li>
            <li>✅ Exportación a CSV</li>
            <li>✅ Páginas HTML personalizables</li>
        </ul>
        
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
        
        <div style="text-align: center; margin-top: 20px;">
            <p><strong>Estado del servidor:</strong> ✅ Funcionando</p>
            <p><strong>PHP:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Directorio:</strong> <?php echo __DIR__; ?></p>
        </div>
    </div>
</body>
</html>
