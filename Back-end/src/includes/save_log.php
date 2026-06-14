<?php
// On vérifie que la connexion MongoDB est active
if (isset($logCollection) && $logCollection !== null) {
    try {
        // 1. Récupération intelligente du Payload (JSON ou FormData)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            // C'est du FormData ! On prend les données dans $_POST
            $payload = $_POST;
        } else {
            // C'est du JSON classique ! On garde la variable $inputData définie au début
            $payload = $inputData ?? [];
        }

        // 2. Nettoyage des mots de passe (Sécurité)
        if (isset($payload['password'])) {
            $payload['password'] = '********';
        }
        if (isset($payload['confirm_password'])) {
            $payload['confirm_password'] = '********';
        }

        // 3. Inclusion des infos sur les fichiers (si présence d'images/FormData)
        if (!empty($_FILES)) {
            $payload['_uploaded_files'] = [];
            foreach ($_FILES as $key => $file) {
                // On ne stocke que les métadonnées de l'image (nom, type, taille), pas le binaire !
                if (is_array($file['name'])) {
                    // Gestion si tu envoies un tableau d'images (ex: images[])
                    foreach ($file['name'] as $index => $name) {
                        if (!empty($name)) {
                            $payload['_uploaded_files'][] = [
                                'field' => $key . '[' . $index . ']',
                                'filename' => $name,
                                'mime_type' => $file['type'][$index] ?? 'unknown',
                                'size_bytes' => $file['size'][$index] ?? 0
                            ];
                        }
                    }
                } else {
                    // Gestion d'un fichier unique
                    if (!empty($file['name'])) {
                        $payload['_uploaded_files'][] = [
                            'field' => $key,
                            'filename' => $file['name'],
                            'mime_type' => $file['type'],
                            'size_bytes' => $file['size']
                        ];
                    }
                }
            }
        }

        // 4. Construction et envoi du document à MongoDB
        $logData = [
            'timestamp'   => new MongoDB\BSON\UTCDateTime(),
            'method'      => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'action'      => $_GET['action'] ?? 'none',
            'uri'         => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            'status_code' => http_response_code(),
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_id'     => $_SESSION['user_id'] ?? null,
            'payload'     => $payload, // Contiendra le texte du FormData + les infos des images !
            'headers'     => function_exists('getallheaders') ? (getallheaders() ?: []) : []
        ];

        $logCollection->insertOne($logData);

    } catch (\Exception $e) {
        error_log("Erreur lors de l'enregistrement du log Mongo : " . $e->getMessage());
    }
}