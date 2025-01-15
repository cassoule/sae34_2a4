<?php

session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$idLogement = $_GET['idLogement'];
//print_r($idLogement);

$result = $dbh->prepare("UPDATE SAE._logement  SET est_actif = false WHERE id_logement = :id_logement; ");
$result->bindParam(':id_logement', $idLogement);
$result->execute();

header('Location: /mesLogements.php');


ob_end_flush();
?>