<?php

session_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

//suprimer le message devis de la base de donnée
function supprimerMessage($id) {
    print_r("$id");
}


supprimerMessage($_GET['id_message']);

header("Location:   /messagerie.php");