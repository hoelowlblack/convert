<?php

function resize_and_convert_to_webp($source_file, $output_file, $max_size_kb = 200) {
    // Charger l'image selon le format
    $info = getimagesize($source_file);
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source_file);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source_file);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source_file);
            break;
        default:
            die("Format d'image non supporté.");
    }

    // Commencer avec une qualité élevée
    $quality = 95;
    $output_temp = $output_file . '.webp';

    // Sauvegarder l'image WebP et ajuster la qualité pour être sous 200 Ko
    imagewebp($image, $output_temp, $quality);
    while (filesize($output_temp) > $max_size_kb * 1024 && $quality > 10) {
        $quality -= 5;
        imagewebp($image, $output_temp, $quality);
    }

    imagedestroy($image);
    return $output_temp;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier si un fichier est uploadé
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
        $output_file = $upload_dir . $file_name;

        // Convertir et redimensionner
        $converted_file = resize_and_convert_to_webp($file_tmp, $output_file);

        // Télécharger le fichier une fois converti
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($converted_file) . '"');
        header('Content-Length: ' . filesize($converted_file));
        readfile($converted_file);

        // Supprimer le fichier temporaire du serveur
        unlink($converted_file);
    } else {
        echo "Erreur lors de l'upload de l'image.";
    }
}

?>
