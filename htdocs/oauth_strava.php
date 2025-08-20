<?php
declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED);

echo "<h1>🔐 Configuración OAuth de Strava (Método Oficial)</h1>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>⚠️ Problema identificado</h2>";
echo "<p>Los tokens de acceso de Strava:</p>";
echo "<ul>";
echo "<li>❌ <strong>Expiran después de 6 horas</strong></li>";
echo "<li>❌ <strong>Necesitan scopes específicos</strong> (activity:read, activity:read_all)</li>";
echo "<li>❌ <strong>El token simple no es suficiente</strong> para acceder a datos de clubes</li>";
echo "</ul>";
echo "</div>";

$clientId = $_GET['client_id'] ?? '';
$clientSecret = $_GET['client_secret'] ?? '';

if (!$clientId || !$clientSecret) {
    echo "<div style='background: #e8f4fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>📋 Paso 1: Configurar credenciales OAuth</h2>";
    echo "<p>Necesitas obtener las credenciales de tu aplicación de Strava:</p>";
    
    echo "<ol>";
    echo "<li><strong>Ve a:</strong> <a href='https://www.strava.com/settings/api' target='_blank'>https://www.strava.com/settings/api</a></li>";
    echo "<li><strong>Crea una aplicación</strong> (si no la tienes):</li>";
    echo "<ul>";
    echo "<li>Application Name: <code>Mi Club Tracker</code></li>";
    echo "<li>Category: <code>Data Importer</code></li>";
    echo "<li>Website: <code>http://127.0.0.1:8000</code></li>";
    echo "<li>Authorization Callback Domain: <code>127.0.0.1</code></li>";
    echo "</ul>";
    echo "<li><strong>Copia el Client ID y Client Secret</strong></li>";
    echo "</ol>";
    
    echo "<form method='get' style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🔑 Configurar credenciales</h3>";
    echo "<p>";
    echo "<label><strong>Client ID:</strong></label><br>";
    echo "<input type='text' name='client_id' placeholder='Ej: 123456' style='width: 300px; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 5px;' required>";
    echo "</p>";
    echo "<p>";
    echo "<label><strong>Client Secret:</strong></label><br>";
    echo "<input type='text' name='client_secret' placeholder='Ej: abc123def456...' style='width: 400px; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 5px;' required>";
    echo "</p>";
    echo "<input type='submit' value='🚀 Configurar OAuth' style='background: #FC4C02; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
    echo "</form>";
    echo "</div>";
    
} else {
    $redirectUri = "http://127.0.0.1:8000/oauth_strava.php?client_id={$clientId}&client_secret={$clientSecret}";
    
    if (isset($_GET['code'])) {
        // Step 2: Exchange code for access token
        $code = $_GET['code'];
        
        echo "<h2>🔄 Intercambiando código por token...</h2>";
        
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
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>✅ ¡Token OAuth obtenido exitosamente!</h3>";
            
            $accessToken = $tokenData['access_token'];
            $refreshToken = $tokenData['refresh_token'] ?? null;
            $expiresAt = $tokenData['expires_at'] ?? null;
            
            echo "<p><strong>Access Token:</strong></p>";
            echo "<code style='background: #f8f9fa; padding: 10px; display: block; margin: 10px 0; font-size: 12px; word-break: break-all;'>{$accessToken}</code>";
            
            if ($refreshToken) {
                echo "<p><strong>Refresh Token:</strong></p>";
                echo "<code style='background: #f8f9fa; padding: 10px; display: block; margin: 10px 0; font-size: 12px; word-break: break-all;'>{$refreshToken}</code>";
            }
            
            if ($expiresAt) {
                echo "<p><strong>Expira:</strong> " . date('Y-m-d H:i:s', $expiresAt) . "</p>";
            }
            
            // Test the token
            echo "<h3>🧪 Probando token...</h3>";
            try {
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => "Authorization: Bearer {$accessToken}\r\n"
                    ]
                ]);
                $athleteResponse = file_get_contents("https://www.strava.com/api/v3/athlete", false, $context);
                $athlete = json_decode($athleteResponse, true);
                
                if (isset($athlete['id'])) {
                    echo "<p style='color: green;'>✅ Token válido para atleta: " . ($athlete['firstname'] ?? '') . " " . ($athlete['lastname'] ?? '') . "</p>";
                    
                    // Update the downloader with the new token
                    $downloaderFile = __DIR__ . '/strava_downloader.php';
                    if (file_exists($downloaderFile)) {
                        $content = file_get_contents($downloaderFile);
                        $content = preg_replace(
                            "/\$accessToken = '[^']*';/",
                            "\$accessToken = '{$accessToken}';",
                            $content
                        );
                        file_put_contents($downloaderFile, $content);
                        echo "<p style='color: green;'>✅ Token actualizado automáticamente en el sistema</p>";
                    }
                    
                    // Save refresh token for future use
                    if ($refreshToken) {
                        $tokenFile = __DIR__ . '/token_data.json';
                        $tokenInfo = [
                            'access_token' => $accessToken,
                            'refresh_token' => $refreshToken,
                            'expires_at' => $expiresAt,
                            'client_id' => $clientId,
                            'client_secret' => $clientSecret,
                            'created_at' => time()
                        ];
                        file_put_contents($tokenFile, json_encode($tokenInfo, JSON_PRETTY_PRINT));
                        echo "<p style='color: blue;'>💾 Datos de token guardados para renovación automática</p>";
                    }
                    
                    echo "<div style='margin: 20px 0;'>";
                    echo "<a href='strava_downloader.php' style='background: #28a745; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🚀 Probar descarga de datos</a>";
                    echo "<a href='generate_reports.php' style='background: #007bff; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Generar reportes</a>";
                    echo "</div>";
                } else {
                    echo "<p style='color: red;'>❌ Error obteniendo datos del atleta</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error probando token: " . $e->getMessage() . "</p>";
            }
            
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
            echo "<h3>❌ Error obteniendo token</h3>";
            echo "<p><strong>Respuesta de Strava:</strong></p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
            echo "</div>";
        }
        
    } else {
        // Step 1: Show authorization link
        $scopes = 'read,activity:read_all'; // Scopes necesarios para leer actividades de clubes
        $authUrl = "https://www.strava.com/oauth/authorize?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scopes,
            'state' => bin2hex(random_bytes(16)) // Para seguridad
        ]);
        
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>✅ Credenciales configuradas</h2>";
        echo "<p><strong>Client ID:</strong> {$clientId}</p>";
        echo "<p><strong>Redirect URI:</strong> {$redirectUri}</p>";
        echo "<p><strong>Scopes solicitados:</strong> <code>{$scopes}</code></p>";
        echo "</div>";
        
        echo "<div style='background: #e8f4fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>🔐 Paso 2: Autorizar aplicación</h2>";
        echo "<p>Ahora debes autorizar tu aplicación con los permisos correctos:</p>";
        echo "<ul>";
        echo "<li>✅ <strong>read</strong> - Leer perfil básico</li>";
        echo "<li>✅ <strong>activity:read_all</strong> - Leer todas las actividades (necesario para clubes)</li>";
        echo "</ul>";
        
        echo "<div style='text-align: center; margin: 30px 0;'>";
        echo "<a href='{$authUrl}' style='background: #FC4C02; color: white; padding: 20px 30px; text-decoration: none; border-radius: 10px; font-size: 18px; font-weight: bold;'>🔐 AUTORIZAR CON STRAVA</a>";
        echo "</div>";
        
        echo "<p style='font-size: 14px; color: #666;'>Al hacer clic, serás redirigido a Strava para autorizar los permisos. Después regresarás aquí automáticamente con el token configurado.</p>";
        echo "</div>";
    }
}

echo "<hr>";
echo "<p><a href='index.php'>← Volver al inicio</a> | <a href='debug_token.php'>🔍 Diagnóstico</a></p>";
?>
