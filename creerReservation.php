<?php
/*
DOD : 
- on doit tous relire pour voir si l'un de nous voit pas de problème
- verifier que en mode demo ça rend comme il faut cf la derniere fois
- Test ?
- Documentation ?
- Critères succès/échec
- CSS
- GIT 
- Maxime à relu ?
- Bien intégré dans le code global ?
- ORTHOGRAPHE
*/
ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Informations liées à la création de la réservation dans la base de données
$acceptation_cgv = $_GET['acceptation_cgv'];
$est_paye = $_GET['est_paye'];
$id_message_devis = $_GET['id_message_devis'];
$id_paiement = $_GET['id_paiement'];

// Récupération des conditions d'annulations de la réservation
$result = $dbh->query("SELECT condition_annulation_reservation FROM sae._message_devis WHERE id_message_devis = $id_message_devis");  
$row = $result->fetch(PDO::FETCH_ASSOC);
$type_annulation = $row['condition_annulation_reservation'];

// Ajout de la réservation dans la base de données
try{
    $stmt = $dbh->prepare("INSERT INTO sae._reservation (acceptation_cgv, type_annulation, est_paye, id_message_devis, id_paiement) VALUES (:acceptation_cgv, :type_annulation, :est_paye, :id_message_devis, :id_paiement)");
    $stmt->bindParam(':acceptation_cgv', $acceptation_cgv);
    $stmt->bindParam(':type_annulation', $type_annulation);
    $stmt->bindParam(':est_paye', $est_paye);
    $stmt->bindParam(':id_message_devis', $id_message_devis);
    $stmt->bindParam(':id_paiement', $id_paiement);

    $stmt->execute();
}
// Pour gérer les cas d'erreur de la base de données
catch(PDOException $e){
    echo "Il y a eu une erreur lors de la création de la réservation : " . $e->getMessage();
}

// Renvoies vers la page d'accueil du site et arrête le script de la page
header("Location: ./mesReservationsClients.php");
exit();

ob_end_flush();
?>