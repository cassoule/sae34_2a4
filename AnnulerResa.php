<?php 

session_start();






//fonction pour supprimer une reservation
function remove_res($id_res) {
    // Chargement de la base de données
    include ('connect_params.php');
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbh->query("DELETE from _reservation where id_reservation=$id_res");
    //(delete from _reservation where id_reservation=$id_res;)
    //print_r($id_res);
}

//print_r($_GET);
//print_r($_GET['id_reservation']);
remove_res($_GET['id_reservation']);






header("Location:   /mesReservationsClients.php");
exit();
?>