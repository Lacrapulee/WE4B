<?php
require_once __DIR__ . '/../db.php';
$userIdToDelete = $_GET['id'] ?? null; 

if ($userIdToDelete && ((isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userIdToDelete))) {
    
    try {
        // Au lieu de DELETE, on UPDATE avec des données bidons.
        // On génère un faux email unique car ta colonne email est UNIQUE
        $fakeEmail = 'supprime_' . uniqid() . '@anonyme.fr';
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET 
                email = ?, 
                password = 'DELETED', 
                nom = 'Compte', 
                prenom = 'Supprimé', 
                telephone = NULL, 
                date_naissance = NULL, 
                adresse_postale = NULL 
            WHERE id = ?
        ");
        
        $stmt->execute([$fakeEmail, $userIdToDelete]);
        
        //Détruire la session si c'est l'utilisateur lui-même
        if ($_SESSION['user_id'] == $userIdToDelete) {
            session_destroy();
        }
        
        exit();

    } catch (PDOException $e) {
        die("Erreur lors de la suppression du compte : " . $e->getMessage());
    }

} else {
    die("Action non autorisée.");
}
?>