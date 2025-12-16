<?php
function subirImagen($file_input, $destino, $max_size = 5242880) {
    // 5MB por defecto
    
    if($file_input['error'] != 0) {
        return ['success' => false, 'error' => 'Error en la subida del archivo'];
    }
    
    // Validar tipo
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $file_input['type'];
    
    if(!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Formato no válido. Use JPG, PNG o GIF'];
    }
    
    // Validar tamaño
    if($file_input['size'] > $max_size) {
        return ['success' => false, 'error' => 'Archivo demasiado grande. Máx: ' . ($max_size/1024/1024) . 'MB'];
    }
    
    // Generar nombre único
    $ext = strtolower(pathinfo($file_input['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $full_path = $destino . $filename;
    
    // Crear directorio si no existe
    if(!is_dir($destino)) {
        mkdir($destino, 0777, true);
    }
    
    // Mover archivo
    if(move_uploaded_file($file_input['tmp_name'], $full_path)) {
        return ['success' => true, 'filename' => $filename, 'path' => $full_path];
    } else {
        return ['success' => false, 'error' => 'Error al guardar el archivo'];
    }
}

function redimensionarImagen($origen, $destino, $ancho_max = 800, $alto_max = 600) {
    // Requiere GD library activada
    list($ancho_orig, $alto_orig, $tipo) = getimagesize($origen);
    
    // Calcular nuevas dimensiones
    $ratio_orig = $ancho_orig / $alto_orig;
    
    if ($ancho_max / $alto_max > $ratio_orig) {
        $ancho_max = $alto_max * $ratio_orig;
    } else {
        $alto_max = $ancho_max / $ratio_orig;
    }
    
    // Crear imagen según tipo
    switch($tipo) {
        case IMAGETYPE_JPEG:
            $imagen = imagecreatefromjpeg($origen);
            break;
        case IMAGETYPE_PNG:
            $imagen = imagecreatefrompng($origen);
            break;
        case IMAGETYPE_GIF:
            $imagen = imagecreatefromgif($origen);
            break;
        default:
            return false;
    }
    
    // Crear nueva imagen
    $nueva_imagen = imagecreatetruecolor($ancho_max, $alto_max);
    
    // Preservar transparencia en PNG y GIF
    if($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
        imagecolortransparent($nueva_imagen, imagecolorallocatealpha($nueva_imagen, 0, 0, 0, 127));
        imagealphablending($nueva_imagen, false);
        imagesavealpha($nueva_imagen, true);
    }
    
    // Redimensionar
    imagecopyresampled($nueva_imagen, $imagen, 0, 0, 0, 0, 
                      $ancho_max, $alto_max, $ancho_orig, $alto_orig);
    
    // Guardar imagen
    switch($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($nueva_imagen, $destino, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($nueva_imagen, $destino, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($nueva_imagen, $destino);
            break;
    }
    
    // Liberar memoria
    imagedestroy($imagen);
    imagedestroy($nueva_imagen);
    
    return true;
}



function app_log(string $message, array $context = []): void
{
    $file = __DIR__ . '/../../logs/app.log';
    $date = date('Y-m-d H:i:s');

    $line = "[$date] $message";
    if (!empty($context)) {
        $line .= " | " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    $line .= PHP_EOL;

    @file_put_contents($file, $line, FILE_APPEND);
}

?>

