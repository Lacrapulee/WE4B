<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/mongo.php';

$imagePath = __DIR__ . '/../public/assets/img/default.png';

if (file_exists($imagePath)) {
    if (isset($imageCollection)) {
        $existing = $imageCollection->findOne(['is_default' => true]);
        if ($existing) {
            echo "L'image par defaut existe deja avec l'ID : " . $existing['_id'] . "\n";
        } else {
            $binary = new MongoDB\BSON\Binary(file_get_contents($imagePath), MongoDB\BSON\Binary::TYPE_GENERIC);
            $insertResult = $imageCollection->insertOne([
                'filename'   => 'default.png',
                'mime_type'  => 'image/png',
                'size'       => filesize($imagePath),
                'data'       => $binary,
                'uploaded_at'=> new MongoDB\BSON\UTCDateTime(),
                'is_default' => true
            ]);
            echo "Image par defaut inseree avec succes ! ID : " . $insertResult->getInsertedId() . "\n";
        }
    } else {
        echo "Erreur : Collection MongoDB indisponible.\n";
    }
} else {
    echo "Erreur : Image introuvable au chemin $imagePath\n";
}
