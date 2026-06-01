<?php


function addItem($pdo, $vendeur_id, $categorie_id, $titre, $description, $prix, $coordonnees, $ville_nom, $code_postal) {
    // On sépare la chaîne "47.639,6.853" en deux variables
    list($lat, $long) = explode(',', $coordonnees);

    // On prépare la requête avec la fonction ST_PointFromText
    // Note : On utilise POINT($long $lat) sans virgule entre les deux à l'intérieur du point
    $sql = "INSERT INTO articles (vendeur_id, categorie_id, titre, description, prix, statut, coordonnees, ville_nom, code_postal) 
            VALUES (?, ?, ?, ?, ?, 'en_ligne', ST_PointFromText(?), ?, ?)";
            
    $point = "POINT($long $lat)"; // Format WKT : Longitude Latitude

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $vendeur_id, 
        $categorie_id, 
        $titre, 
        $description, 
        $prix, 
        $point, // On envoie le point formaté
        $ville_nom, 
        $code_postal
    ]);
    
    return $pdo->lastInsertId();
}

function addImage($pdo, $article_id, $nom_image, $ordre) {
    $stmt = $pdo->prepare("INSERT INTO article_images (article_id, url_image, est_principale, ordre) VALUES (?, ?, ?, ?)");
    if ($ordre == 1) {
        $resultat = $stmt->execute([$article_id, $nom_image, 1, $ordre]);
    } else {
        $resultat = $stmt->execute([$article_id, $nom_image, 0, $ordre]);
    }
    return $resultat;
}

function getCoordinates($adresse, $ville, $cp) {
    $query = urlencode($adresse . " " . $cp . " " . $ville);
    $url = "https://api-adresse.data.gouv.fr/search/?q=$query&limit=1";
    
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if (!empty($data['features'])) {
            // L'API renvoie [Longitude, Latitude]
            $coords = $data['features'][0]['geometry']['coordinates'];
            return $coords[1] . ',' . $coords[0]; // On stocke "Lat,Long"
        }
    }
    return null; // Retourne null si rien n'est trouvé
}

/**
 * Récupère les coordonnées d'une ville
 */
function getCoordinatesFromVille($ville) {
    $query = urlencode($ville);
    $url = "https://api-adresse.data.gouv.fr/search/?q=$query&limit=1&type=municipality";
    
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if (!empty($data['features'])) {
            $coords = $data['features'][0]['geometry']['coordinates'];
            return $coords[1] . ',' . $coords[0]; // On stocke "Lat,Long"
        }
    }
    return null;
}

/**
 * Calcule la distance en km entre deux coordonnées
 * @param string $coords1 Format "lat,long" ex: "47.639,6.853"
 * @param string $coords2 Format "lat,long"
 * @return float Distance en kilomètres
 */
function calculateDistance($coords1, $coords2) {
    if (!$coords1 || !$coords2) {
        return null;
    }
    
    // Extraire les coordonnées
    $coords1 = extractLatLongFromWKT($coords1);
    $coords2 = extractLatLongFromWKT($coords2);
    
    if (!$coords1 || !$coords2) {
        return null;
    }
    
    list($lat1, $lon1) = explode(',', $coords1);
    list($lat2, $lon2) = explode(',', $coords2);
    
    $lat1 = (float)$lat1;
    $lon1 = (float)$lon1;
    $lat2 = (float)$lat2;
    $lon2 = (float)$lon2;
    
    // Rayon terrestre en km
    $earthRadius = 6371;
    
    // Convertir en radians
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);
    
    // Formule de Haversine
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    
    return round($angle * $earthRadius, 2); // Distance en km
}

/**
 * Extrait les coordonnées au format "lat,long" à partir d'une chaîne
 */
function extractLatLongFromWKT($coords) {
    if (empty($coords)) {
        return null;
    }
    
    $coords = trim($coords);
    
    if (strpos($coords, 'POINT') !== false) {
        // Format WKT : "POINT(lat lon)" - récupération directe
        if (preg_match('/POINT\s*\(\s*([\d\.\-]+)\s+([\d\.\-]+)\s*\)/', $coords, $matches)) {
            if (isset($matches[1]) && isset($matches[2])) {
                return trim($matches[1]) . ',' . trim($matches[2]); // Retourner "lat,long"
            }
        }
        return null;
    } else if (strpos($coords, ',') !== false) {
        // Format déjà "lat,long"
        return $coords;
    }
    return null;
}
