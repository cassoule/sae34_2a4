<?php

session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$idcal = $_GET['id_clecal'];
print_r($idcalt);

$result = $dbh->prepare("DELETE FROM SAE._clecalendrier WHERE id_clecal = :id_clecal; ");
$result->bindParam(':id_clecal', $idcal);
$result->execute();

header('Location: /cleCalendrier.php');


ob_end_flush();
?>