<?php
ob_start();

// Chargement de la base de données
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$id_logement = $_GET['id_logement'];

// Supprime toutes les images actuels du logement
foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
    // Si l'id du logement de l'image est celui du logement qui est modifié
    if ($image['id_logement_image'] == $id_logement) {
        // On supprime le fichier
        try {
            if (file_exists($image['lien_image'])) {
                unlink($image['lien_image']);
            }
        }
        // Pour gérer les cas d'erreur de la base de données
        catch (Exception $e) {
            echo 'Erreur lors de la suppression du fichier : ', $e->getMessage();
        }
    }
}

$stmt = $dbh->prepare("DELETE FROM SAE._image WHERE id_logement_image = :id_logement_image");
$stmt->bindParam(':id_logement_image', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._contient WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._possede WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._equipe WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._service WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._charge WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._avis WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._jour WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$reservation = $dbh->query("SELECT sae._reservation.id_message_devis from sae._message_devis inner join sae._reservation on sae._message_devis.id_message_devis = sae._reservation.id_message_devis");

foreach($reservation as $row) {
    $stmt = $dbh->prepare("DELETE FROM SAE._reservation WHERE id_message_devis = :id_message_devis");
    $stmt->bindParam(':id_message_devis', $row['id_message_devis'], PDO::PARAM_INT);
    $stmt->execute();
}

$stmt = $dbh->prepare("DELETE FROM SAE._message_devis WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._signalement WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

$stmt = $dbh->prepare("DELETE FROM SAE._logement WHERE id_logement = :id_logement");
$stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);
$stmt->execute();

header('Location: /mesLogements.php');
exit();

ob_end_flush();
?>