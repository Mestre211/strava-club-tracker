<?php
declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED);

echo "<h1>🔑 Guía completa para obtener Access Token de Strava</h1>";

echo "<div style='background: #e8f4fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📋 Método 1: Aplicación de Strava (Recomendado)</h2>";

echo "<h3>Paso 1: Crear aplicación</h3>";
echo "<ol>";
echo "<li>Ve a: <a href='https://www.strava.com/settings/api' target='_blank' style='color: #FC4C02; font-weight: bold;'>https://www.strava.com/settings/api</a></li>";
echo "<li>Haz clic en '<strong>Create App</strong>' si no tienes una aplicación</li>";
echo "<li>Completa el formulario:</li>";
echo "<ul style='margin: 10px 0;'>";
echo "<li><strong>Application Name:</strong> Mi Club Tracker</li>";
echo "<li><strong>Category:</strong> Data Importer</li>";
echo "<li><strong>Club:</strong> (opcional)</li>";
echo "<li><strong>Website:</strong> http://localhost:8000</li>";
echo "<li><strong>Authorization Callback Domain:</strong> localhost</li>";
echo "<li><strong>Description:</strong> Tracker para analizar datos de club</li>";
echo "</ul>";
echo "</ol>";

echo "<h3>Paso 2: Obtener Access Token</h3>";
echo "<p>Después de crear la aplicación, verás:</p>";
echo "<ul>";
echo "<li><strong>Client ID:</strong> (número, ej: 123456)</li>";
echo "<li><strong>Client Secret:</strong> (código largo)</li>";
echo "<li><strong>Your Access Token:</strong> ← <strong>Este es el que necesitas copiar</strong></li>";
echo "</ul>";

echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🚀 Método 2: Generar token con OAuth</h2>";

if (isset($_GET['client_id']) && isset($_GET['client_secret'])) {
    $clientId = $_GET['client_id'];
    $clientSecret = $_GET['client_secret'];
    $redirectUri = 'http://127.0.0.1:8000/token_helper.php';
    
    if (isset($_GET['code'])) {
        // Exchange code for token
        $code = $_GET['code'];
        
        echo "<h3>🔄 Intercambiando código por token...</h3>";
        
        $tokenUrl = "https://www.strava.com/oauth/token";
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($postData)
            ]
        ]);
        
        $response = file_get_contents($tokenUrl, false, $context);
        $tokenData = json_decode($response, true);
        
        if (isset($tokenData['access_token'])) {
            $accessToken = $tokenData['access_token'];
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>✅ ¡Token obtenido exitosamente!</h3>";
            echo "<p><strong>Tu Access Token:</strong></p>";
            echo "<code style='background: #f8f9fa; padding: 10px; display: block; margin: 10px 0; font-size: 14px; word-break: break-all;'>{$accessToken}</code>";
            
            // Auto-update the token in the downloader
            $downloaderFile = __DIR__ . '/strava_downloader.php';
            if (file_exists($downloaderFile)) {
                $content = file_get_contents($downloaderFile);
                $content = preg_replace(
                    "/\$accessToken = '[^']*';/", 
                    "\$accessToken = '{$accessToken}';", 
                    $content
                );
                file_put_contents($downloaderFile, $content);
                echo "<p style='color: green;'>✅ Token configurado automáticamente en el sistema</p>";
                echo "<p><a href='strava_downloader.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🚀 Probar descarga de datos</a></p>";
            }
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
            echo "<h3>❌ Error obteniendo token</h3>";
            echo "<p>Respuesta: " . htmlspecialchars($response) . "</p>";
            echo "</div>";
        }
    } else {
        // Show authorization link
        $authUrl = "https://www.strava.com/oauth/authorize?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'read,activity:read'
        ]);
        
        echo "<p>Configuración OAuth detectada:</p>";
        echo "<ul>";
        echo "<li><strong>Client ID:</strong> {$clientId}</li>";
        echo "<li><strong>Redirect URI:</strong> {$redirectUri}</li>";
        echo "</ul>";
        
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='{$authUrl}' style='background: #FC4C02; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 18px;'>🔐 Autorizar con Strava</a>";
        echo "</div>";
    }
} else {
    echo "<p>Si tienes Client ID y Client Secret, puedes generar un token automáticamente:</p>";
    echo "<form method='get' style='background: white; padding: 15px; border-radius: 5px;'>";
    echo "<p>";
    echo "<label><strong>Client ID:</strong></label><br>";
    echo "<input type='text' name='client_id' placeholder='Ej: 123456' style='width: 300px; padding: 8px; margin: 5px 0;'>";
    echo "</p>";
    echo "<p>";
    echo "<label><strong>Client Secret:</strong></label><br>";
    echo "<input type='text' name='client_secret' placeholder='Ej: abc123def456...' style='width: 300px; padding: 8px; margin: 5px 0;'>";
    echo "</p>";
    echo "<input type='submit' value='🚀 Generar Token' style='background: #FC4C02; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "</form>";
}

echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>💡 Configuración manual</h2>";
echo "<p>Si ya tienes un access token válido, puedes configurarlo directamente:</p>";

if ($_POST['manual_token'] ?? false) {
    $newToken = trim($_POST['manual_token']);
    
    if (strlen($newToken) > 20) {
        $downloaderFile = __DIR__ . '/strava_downloader.php';
        $content = file_get_contents($downloaderFile);
        $content = preg_replace(
            "/\$accessToken = '[^']*';/", 
            "\$accessToken = '{$newToken}';", 
            $content
        );
        file_put_contents($downloaderFile, $content);
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
        echo "<h3>✅ Token configurado</h3>";
        echo "<p><a href='strava_downloader.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🚀 Probar descarga</a></p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Token inválido (muy corto)</p>";
    }
}

echo "<form method='post' style='background: white; padding: 15px; border-radius: 5px;'>";
echo "<label><strong>Access Token:</strong></label><br>";
echo "<input type='text' name='manual_token' style='width: 500px; padding: 8px; margin: 5px 0;' placeholder='Pega tu access token aquí'><br>";
echo "<input type='submit' value='💾 Configurar Token' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0;'>";
echo "</form>";
echo "</div>";

echo "<hr>";
echo "<p><a href='index.php'>← Volver al inicio</a> | <a href='strava_downloader.php'>📥 Probar descarga</a></p>";
?>
