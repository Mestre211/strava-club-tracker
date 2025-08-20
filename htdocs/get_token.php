<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED);

echo "<h1>🔑 Obtener Access Token de Strava</h1>";

echo "<div style='background: #e8f4fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📋 Pasos para obtener un Access Token válido:</h2>";

echo "<h3>1. 🌐 Accede a Strava API</h3>";
echo "<p>Ve a: <a href='https://www.strava.com/settings/api' target='_blank'>https://www.strava.com/settings/api</a></p>";

echo "<h3>2. 📝 Crea o selecciona tu aplicación</h3>";
echo "<ul>";
echo "<li>Si no tienes una aplicación, haz clic en 'Create App'</li>";
echo "<li>Completa los campos requeridos:</li>";
echo "<ul>";
echo "<li><strong>Application Name:</strong> Mi Club Tracker</li>";
echo "<li><strong>Category:</strong> Data Importer</li>";
echo "<li><strong>Club:</strong> (opcional)</li>";
echo "<li><strong>Website:</strong> http://localhost</li>";
echo "<li><strong>Authorization Callback Domain:</strong> localhost</li>";
echo "</ul>";
echo "</ul>";

echo "<h3>3. 🔑 Obtén tu Access Token</h3>";
echo "<p>En la página de tu aplicación, encontrarás:</p>";
echo "<ul>";
echo "<li><strong>Client ID:</strong> (número)</li>";
echo "<li><strong>Client Secret:</strong> (código largo)</li>";
echo "<li><strong>Your Access Token:</strong> ← <strong>Este es el que necesitas</strong></li>";
echo "</ul>";

echo "<h3>4. ✅ Actualiza tu configuración</h3>";
echo "<p>Copia el 'Your Access Token' y pégalo aquí:</p>";

echo "<form method='post' style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<label for='token'><strong>Nuevo Access Token:</strong></label><br>";
echo "<input type='text' id='token' name='token' style='width: 500px; padding: 8px; margin: 5px 0;' placeholder='Pega tu access token aquí'><br>";
echo "<input type='submit' value='💾 Actualizar Token' style='background: #FC4C02; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0;'>";
echo "</form>";

if ($_POST['token'] ?? false) {
    $newToken = trim($_POST['token']);
    
    if (strlen($newToken) > 20) {
        // Update the token in the downloader file
        $downloaderFile = __DIR__ . '/strava_downloader.php';
        $content = file_get_contents($downloaderFile);
        
        // Replace the token
        $content = preg_replace(
            "/\$accessToken = '[^']*';/", 
            "\$accessToken = '{$newToken}';", 
            $content
        );
        
        file_put_contents($downloaderFile, $content);
        
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ ¡Token actualizado correctamente!</h3>";
        echo "<p>Tu nuevo access token ha sido configurado.</p>";
        echo "<p><a href='strava_downloader.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🚀 Probar descarga de datos</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>❌ Token inválido</h3>";
        echo "<p>El token debe ser más largo. Asegúrate de copiar el token completo.</p>";
        echo "</div>";
    }
}

echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>⚠️ Importante:</h3>";
echo "<ul>";
echo "<li>El access token debe tener permisos de <strong>'read'</strong></li>";
echo "<li>Si el token sigue fallando, verifica que tu aplicación esté aprobada</li>";
echo "<li>Los tokens pueden expirar, especialmente si no se usan</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='index.php'>← Volver al inicio</a></p>";
?>

