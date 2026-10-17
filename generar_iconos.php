<?php
/**
 * Genera los íconos de la PWA
 * Ejecutar UNA VEZ: http://localhost/FinanIA/generar_iconos.php
 * Luego eliminar este archivo.
 */

function generarIcono($tamaño, $rutaDestino)
{
    $img = imagecreatetruecolor($tamaño, $tamaño);
    
    // Fondo con gradiente (azul oscuro)
    $bgColor1 = imagecolorallocate($img, 10, 25, 41);      // #0a1929
    $bgColor2 = imagecolorallocate($img, 19, 47, 76);      // #132f4c
    $dorado = imagecolorallocate($img, 212, 175, 55);      // #d4af37
    $blanco = imagecolorallocate($img, 230, 237, 243);
    
    // Gradiente vertical
    for ($y = 0; $y < $tamaño; $y++) {
        $ratio = $y / $tamaño;
        $r = intval(10 * (1 - $ratio) + 19 * $ratio);
        $g = intval(25 * (1 - $ratio) + 47 * $ratio);
        $b = intval(41 * (1 - $ratio) + 76 * $ratio);
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $tamaño, $y, $color);
    }
    
    // Círculo dorado en el centro
    $centroX = $tamaño / 2;
    $centroY = $tamaño / 2;
    $radio = $tamaño * 0.38;
    
    // Borde dorado exterior
    for ($i = 0; $i < 6; $i++) {
        imageellipse($img, $centroX, $centroY, ($radio * 2) + ($i * 2), ($radio * 2) + ($i * 2), $dorado);
    }
    
    // Símbolo $ en el centro
    $fontSize = intval($tamaño * 0.45);
    
    // Usar la fuente por defecto de GD (es pequeña, pero escalaremos)
    // Para mejor calidad, usar una fuente TrueType si está disponible
    $fuentes = [
        'C:/Windows/Fonts/arialbd.ttf',
        'C:/Windows/Fonts/arial.ttf',
        'C:/Windows/Fonts/calibrib.ttf',
        'C:/Windows/Fonts/calibri.ttf'
    ];
    
    $fuente = null;
    foreach ($fuentes as $f) {
        if (file_exists($f)) {
            $fuente = $f;
            break;
        }
    }
    
    if ($fuente) {
        // Dibujar símbolo "F" grande en dorado
        $texto = "F";
        $bbox = imagettfbbox($fontSize, 0, $fuente, $texto);
        $textoAncho = $bbox[2] - $bbox[0];
        $textoAlto = $bbox[1] - $bbox[7];
        $x = ($tamaño - $textoAncho) / 2;
        $y = ($tamaño + $textoAlto) / 2 - 5;
        imagettftext($img, $fontSize, 0, $x, $y, $dorado, $fuente, $texto);
        
        // Punto pequeño debajo
        $puntoRadio = $tamaño * 0.04;
        imagefilledellipse($img, $tamaño / 2, $tamaño * 0.75, $puntoRadio * 2, $puntoRadio * 2, $dorado);
    } else {
        // Fallback: solo un círculo relleno
        imagefilledellipse($img, $centroX, $centroY, $radio * 1.5, $radio * 1.5, $dorado);
    }
    
    // Guardar
    imagepng($img, $rutaDestino);
    imagedestroy($img);
    
    return $rutaDestino;
}

// Crear carpeta si no existe
$carpeta = __DIR__ . '/assets/img/pwa';
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0755, true);
}

// Generar los 4 tamaños necesarios
generarIcono(192, $carpeta . '/icon-192.png');
generarIcono(512, $carpeta . '/icon-512.png');
generarIcono(144, $carpeta . '/icon-144.png');
generarIcono(96, $carpeta . '/icon-96.png');
generarIcono(72, $carpeta . '/icon-72.png');

echo "✅ Íconos generados exitosamente en assets/img/pwa/<br>";
echo "<ul>";
echo "<li><a href='assets/img/pwa/icon-192.png' target='_blank'>icon-192.png</a></li>";
echo "<li><a href='assets/img/pwa/icon-512.png' target='_blank'>icon-512.png</a></li>";
echo "<li><a href='assets/img/pwa/icon-144.png' target='_blank'>icon-144.png</a></li>";
echo "<li><a href='assets/img/pwa/icon-96.png' target='_blank'>icon-96.png</a></li>";
echo "<li><a href='assets/img/pwa/icon-72.png' target='_blank'>icon-72.png</a></li>";
echo "</ul>";
echo "<p style='color:red;'><strong>⚠️ IMPORTANTE: Elimina este archivo (generar_iconos.php) después de usarlo.</strong></p>";