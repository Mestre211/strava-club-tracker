<?php
declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED);

echo "<h1>🔍 Diagnóstico de Access Token</h1>";

// Get current token from the downloader file
$downloaderFile = __DIR__ . '/strava_downloader.php';
$content = file_get_contents($downloaderFile);

if (preg_match("/\$accessToken = '([^']*)';/", $content, $matches)) {
    $currentToken = $matches[1];
    echo "<h2>🔧 Token actual configurado:</h2>";
    echo "<p><strong>Token:</strong> <code>" . substr($currentToken, 0, 10) . "..." . substr($currentToken, -10) . "</code></p>";
    echo "<p><strong>Longitud:</strong> " . strlen($currentToken) . " caracteres</p>";
} else {
    echo "<p style='color: red;'>❌ No se pudo encontrar el token en el archivo</p>";
    $currentToken = null;
}

echo "<h2>🧪 Pruebas de conectividad:</h2>";

if ($currentToken) {
    // Test different ways to send the token
    $testUrls = [
        "Bearer Token (header)" => function($token) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Authorization: Bearer {$token}\r\n"
                ]
            ]);
            return file_get_contents("https://www.strava.com/api/v3/athlete", false, $context);
        },
        "Query Parameter" => function($token) {
            return file_get_contents("https://www.strava.com/api/v3/athlete?access_token={$token}");
        }
    ];
    
    foreach ($testUrls as $method => $testFunc) {
        echo "<h3>📡 Método: {$method}</h3>";
        try {
            $response = $testFunc($currentToken);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['id'])) {
                    echo "<p style='color: green;'>✅ ¡Éxito! Atleta: " . ($data['firstname'] ?? '') . " " . ($data['lastname'] ?? '') . "</p>";
                    echo "<p>ID: " . $data['id'] . "</p>";
                    
                    // Update the token method if this one works
                    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<h4>🎉 ¡Token funciona con este método!</h4>";
                    echo "<p>Voy a actualizar el código para usar este método.</p>";
                    echo "</div>";
                    break;
                } else {
                    echo "<p style='color: orange;'>⚠️ Respuesta inesperada: " . substr($response, 0, 200) . "...</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<h2>💡 Obtener un nuevo token</h2>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>🚀 Método más confiable:</h3>";
echo "<ol>";
echo "<li><strong>Ve a:</strong> <a href='https://www.strava.com/settings/api' target='_blank'>https://www.strava.com/settings/api</a></li>";
echo "<li><strong>Si no tienes una app:</strong> Haz clic en 'Create App'</li>";
echo "<li><strong>Datos de la app:</strong></li>";
echo "<ul>";
echo "<li>Application Name: <code>Mi Club Tracker</code></li>";
echo "<li>Category: <code>Data Importer</code></li>";
echo "<li>Website: <code>http://localhost:8000</code></li>";
echo "<li>Authorization Callback Domain: <code>localhost</code></li>";
echo "</ul>";
echo "<li><strong>Copia el 'Your Access Token'</strong> (no el Client Secret)</li>";
echo "<li><strong>Pégalo abajo:</strong></li>";
echo "</ol>";
echo "</div>";

if ($_POST['new_token'] ?? false) {
    $newToken = trim($_POST['new_token']);
    
    echo "<h3>🧪 Probando nuevo token...</h3>";
    
    // Test the new token
    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer {$newToken}\r\n"
            ]
        ]);
        $response = file_get_contents("https://www.strava.com/api/v3/athlete", false, $context);
        $data = json_decode($response, true);
        
        if (isset($data['id'])) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
            echo "<h4>✅ ¡Nuevo token válido!</h4>";
            echo "<p><strong>Atleta:</strong> " . ($data['firstname'] ?? '') . " " . ($data['lastname'] ?? '') . "</p>";
            echo "<p><strong>ID:</strong> " . $data['id'] . "</p>";
            
            // Update the token in the downloader
            $newContent = preg_replace(
                "/\$accessToken = '[^']*';/",
                "\$accessToken = '{$newToken}';",
                $content
            );
            file_put_contents($downloaderFile, $newContent);
            
            echo "<p style='color: green;'>✅ Token actualizado en el sistema</p>";
            echo "<p><a href='strava_downloader.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🚀 Probar descarga de datos</a></p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Token no válido. Respuesta: " . substr($response, 0, 200) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error probando token: " . $e->getMessage() . "</p>";
    }
}

echo "<form method='post' style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔑 Configurar nuevo Access Token</h3>";
echo "<label for='new_token'><strong>Tu Access Token de Strava:</strong></label><br>";
echo "<input type='text' id='new_token' name='new_token' style='width: 500px; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;' placeholder='Pega aquí tu access token'><br>";
echo "<input type='submit' value='🧪 Probar y Configurar Token' style='background: #FC4C02; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
echo "</form>";

echo "<hr>";
echo "<p><a href='index.php'>← Volver al inicio</a> | <a href='strava_downloader.php'>📥 Probar descarga</a></p>";
?>
