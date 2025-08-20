<?php

declare(strict_types=1);

// Disable deprecation warnings for better user experience
error_reporting(E_ALL & ~E_DEPRECATED);

use picasticks\Strava\Club;
use picasticks\Strava\ClubTracker;

require_once '../lib/vendor/autoload.php';

echo "<h1>📊 Strava Club Tracker - Generador de Reportes</h1>";

try {
    // Set timezone
    date_default_timezone_set('America/New_York');

    $tracker = new ClubTracker(new Club(dirname(__DIR__).'/json'));

    // Set league sports and display rules
    echo "<h2>⚙️ Configurando deportes y reglas...</h2>";

    // Uncomment to use km as distance unit instead of miles
    //$tracker->distanceUnit = array('KM' => 1000);

    // Define sports (activity types) to include, and total/counting rules
    $tracker->setSport('Ride', array('distanceMultiplier' => 0.25));
    $tracker->setSport('Run',  array('maxSpeed' => 15.0));
    $tracker->setSport('Walk', array('label' => 'Walk/Hike', 'maxSpeed' => 8.0));
    $tracker->setSport('Hike', array('convertTo' => 'Walk'));

    echo "<p>✅ Deportes configurados: Ride, Run, Walk, Hike</p>";

    // Set template function
    $tracker->setTemplateFunction(function (array $vars, string $template): string {
        // Load template based on value of $template
        switch ($template) {
            case 'club':
            case 'leaders':
            case 'activities':
                $template = '{{content}}';
                break;
            default:
                $template = file_get_contents(dirname(__DIR__)."/lib/template/$template.html");
        }

        $search = [];
        $replace = [];
        foreach ($vars as $k => $v) {
            $search[] = '{{'.$k.'}}';
            $replace[] = $v;
        }

        return str_replace($search, $replace, $template);
    });

    echo "<h2>📁 Verificando datos disponibles...</h2>";
    
    // Check if JSON data directory exists and has files
    $jsonDir = dirname(__DIR__).'/json';
    if (!is_dir($jsonDir)) {
        throw new Exception("Directorio de datos no encontrado. Necesitas descargar datos primero.");
    }
    
    $jsonFiles = glob($jsonDir . '/*.json');
    if (empty($jsonFiles)) {
        throw new Exception("No se encontraron archivos de datos. Necesitas descargar datos de Strava primero.");
    }
    
    echo "<p>✅ Encontrados " . count($jsonFiles) . " archivos de datos</p>";
    
    echo "<h2>📁 Cargando datos de actividades...</h2>";

    // Load downloaded activity from disk and calculate totals
    $tracker->loadActivityData();
    
    echo "<p>✅ Datos cargados correctamente</p>";

    echo "<h2>🏗️ Generando reportes HTML...</h2>";

    // Write index.html with main summary tables
    $indexFile = dirname(__DIR__).'/htdocs/index.html';
    file_put_contents($indexFile, $tracker->getSummaryHTML());
    echo "<p>✅ Generado: <a href='index.html' target='_blank'>index.html</a> (Dashboard principal)</p>";

    // Write html files for every person in every club
    $personCount = 0;
    foreach ($tracker->getResults() as $clubId => $results) {
        foreach (array_keys($results['athletes']) as $name) {
            $filename = $tracker->getPersonHTMLFilename(dirname(__DIR__).'/htdocs', $clubId, $name);
            file_put_contents($filename, $tracker->getPersonHTML($clubId, $name));
            $personCount++;
        }
    }
    
    echo "<p>✅ Generadas {$personCount} páginas individuales de atletas</p>";

    // Generate CSV export (optional)
    $csvFile = dirname(__DIR__).'/export.csv';
    file_put_contents($csvFile, $tracker->getCSV());
    echo "<p>✅ Generado: <a href='../export.csv' target='_blank'>export.csv</a> (Exportación de datos)</p>";

    echo "<h2>🎉 ¡Reportes generados exitosamente!</h2>";
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>📈 Archivos generados:</h3>";
    echo "<ul>";
    echo "<li><strong><a href='index.html' target='_blank'>Dashboard Principal</a></strong> - Resumen y estadísticas generales</li>";
    echo "<li><strong>Páginas individuales</strong> - {$personCount} páginas de atletas</li>";
    echo "<li><strong><a href='../export.csv' target='_blank'>Exportación CSV</a></strong> - Todos los datos en formato CSV</li>";
    echo "</ul>";
    echo "</div>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error generando reportes</h2>";
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    
    // Check what data we have
    $jsonDir = dirname(__DIR__).'/json';
    echo "<h3>🔍 Diagnóstico:</h3>";
    
    if (!is_dir($jsonDir)) {
        echo "<p>❌ Directorio 'json' no existe</p>";
    } else {
        $jsonFiles = glob($jsonDir . '/*.json');
        echo "<p>📁 Directorio 'json' existe</p>";
        echo "<p>📄 Archivos encontrados: " . count($jsonFiles) . "</p>";
        
        if (!empty($jsonFiles)) {
            echo "<ul>";
            foreach ($jsonFiles as $file) {
                $size = filesize($file);
                echo "<li>" . basename($file) . " (" . $size . " bytes)</li>";
            }
            echo "</ul>";
        }
    }
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🔧 Pasos para solucionar:</h3>";
    echo "<ol>";
    echo "<li><strong>Primero:</strong> <a href='get_token.php' style='color: #FC4C02;'>Configura un access token válido</a></li>";
    echo "<li><strong>Segundo:</strong> <a href='strava_downloader.php' style='color: #FC4C02;'>Descarga los datos de Strava</a></li>";
    echo "<li><strong>Tercero:</strong> <a href='generate_reports.php' style='color: #FC4C02;'>Vuelve a generar los reportes</a></li>";
    echo "</ol>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Volver al inicio</a> | <a href='strava_downloader.php'>📥 Descargar datos</a> | <a href='generate_reports.php'>🔄 Regenerar reportes</a></p>";
?>
