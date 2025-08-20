<?php

declare(strict_types=1);

// Disable deprecation warnings for better user experience
error_reporting(E_ALL & ~E_DEPRECATED);

use picasticks\Strava\Club;
use picasticks\Strava\ClubException;
use picasticks\Strava\Client;
use picasticks\Strava\REST;

require_once '../lib/vendor/autoload.php';

// *** TU ACCESS TOKEN DE STRAVA ***
$accessToken = '0493ee49e4b51eca0097510f2210870e555458c0';

// Define list of Strava Club IDs to track
$clubs = array(
    325418,  // Tu club de Strava
    // Agrega más club IDs aquí si tienes varios clubes
);

// Set a TZ for date calculations
date_default_timezone_set('America/New_York');

// Set start and end date for tracking (ajusta las fechas según necesites)
$startDate = '2024-08-01';  // Fecha de inicio - último mes
$endDate   = '2024-08-14';  // Fecha de fin - hasta hoy

echo "<h1>🚴‍♂️ Strava Club Tracker - Descargador de datos</h1>";
echo "<h2>Configuración:</h2>";
echo "<p><strong>Access Token:</strong> Configurado ✅</p>";
echo "<p><strong>Clubs a rastrear:</strong> " . count($clubs) . " clubes</p>";
echo "<p><strong>Período:</strong> {$startDate} a {$endDate}</p>";

try {
    // Create HTTP adapter and REST service with your access token
    $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
    $service = new REST($accessToken, $adapter);

    $club = new Club(dirname(__DIR__).'/json');
    $club->setClient(new Client($service));

    // Set API request limit (optional)
    $club->requestLimit = 100;

    // Compute start/end timestamps from start/end dates
    $start = strtotime($startDate);
    $end = min(strtotime($endDate), strtotime('yesterday'));
    
    echo "<h2>🔍 Verificando access token...</h2>";
    
    // Test the access token first with query parameter method (more compatible)
    try {
        $testUrl = "https://www.strava.com/api/v3/athlete?access_token=" . $accessToken;
        $testResponse = $adapter->request('GET', $testUrl);
        
        $athlete = json_decode($testResponse->getBody()->getContents(), true);
        echo "<p style='color: green;'>✅ Access token válido</p>";
        echo "<p><strong>Atleta:</strong> " . ($athlete['firstname'] ?? 'N/A') . " " . ($athlete['lastname'] ?? 'N/A') . "</p>";
        echo "<p><strong>ID:</strong> " . ($athlete['id'] ?? 'N/A') . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error con el access token: " . $e->getMessage() . "</p>";
        echo "<div style='background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🔧 Posibles soluciones:</h3>";
        echo "<ul>";
        echo "<li>Verifica que el access token sea correcto</li>";
        echo "<li>Asegúrate de que el token no haya expirado</li>";
        echo "<li>Comprueba que tengas permisos de lectura ('read' scope)</li>";
        echo "<li>Genera un nuevo access token desde tu aplicación de Strava</li>";
        echo "</ul>";
        echo "</div>";
        return;
    }

    echo "<h2>🔄 Iniciando descarga de datos...</h2>";
    echo "<p>Rango de fechas: " . date('Y-m-d', $start) . " a " . date('Y-m-d', $end) . "</p>";

    // Download data from Strava
    foreach ($clubs as $clubId) {
        echo "<h3>📊 Procesando Club ID: {$clubId}</h3>";
        
        try {
            // Test club access first
            $clubTestUrl = "https://www.strava.com/api/v3/clubs/{$clubId}?access_token=" . $accessToken;
            $clubTestResponse = $adapter->request('GET', $clubTestUrl);
            
            $clubInfo = json_decode($clubTestResponse->getBody()->getContents(), true);
            echo "<p style='color: green;'>✅ Acceso al club confirmado: <strong>" . ($clubInfo['name'] ?? 'Club ' . $clubId) . "</strong></p>";
            echo "<p>Miembros: " . ($clubInfo['member_count'] ?? 'N/A') . "</p>";
            
            // Get club info
            echo "<p>• Descargando información del club...</p>";
            $club->downloadClub($clubId);
            
            // Get club activities for the date range
            echo "<p>• Descargando actividades del club...</p>";
            $club->downloadClubActivities($clubId, $start, $end);
            
            echo "<p style='color: green;'>✅ Club {$clubId} procesado correctamente</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error procesando club {$clubId}: " . $e->getMessage() . "</p>";
            
            if (strpos($e->getMessage(), '401') !== false) {
                echo "<p style='color: orange;'>💡 Posible causa: No tienes acceso a este club o el token no tiene permisos suficientes</p>";
            }
            if (strpos($e->getMessage(), '404') !== false) {
                echo "<p style='color: orange;'>💡 Posible causa: El Club ID {$clubId} no existe o es privado</p>";
            }
        }
    }
    
    echo "<h2>✅ ¡Descarga completada!</h2>";
    echo "<p><strong>Total de requests realizados:</strong> " . $club->getRequestCount() . "</p>";
    echo "<p><strong>Archivos guardados en:</strong> " . dirname(__DIR__) . "/json/</p>";
    
    echo "<hr>";
    echo "<h2>🚀 Próximos pasos:</h2>";
    echo "<p>1. Los datos se han descargado en formato JSON</p>";
    echo "<p>2. Ahora puedes generar los reportes HTML ejecutando:</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>cd " . dirname(__DIR__) . "\nphp build.php</pre>";
    echo "<p>3. O <a href='generate_reports.php'>haz clic aquí para generar reportes automáticamente</a></p>";

} catch (ClubException $e) {
    echo "<p style='color: red;'><strong>Error de límite de API:</strong> " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error general:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Volver al inicio</a> | <a href='strava_downloader.php'>🔄 Ejecutar de nuevo</a></p>";
?>
