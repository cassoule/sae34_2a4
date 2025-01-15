<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Information necessaires pour executer la requete SQL
$idLogement = $_GET['idLogement'];
$idMessage = $_GET['idMessage'];
$typeMessage = $_GET['typeMessage'];

// Si un client refuse le devis
if ($typeMessage == 'devis'){
    $stmtdate = $dbh->prepare("SELECT date_debut, date_fin FROM sae._message_devis WHERE id_logement = $idLogement");
    $stmtdate->execute();
    $date = $stmtdate->fetch(PDO::FETCH_ASSOC);
}
// Si un proprietaire refuse la demande de devis
else if ($typeMessage == 'demandeDevis'){
    $stmtdate = $dbh->prepare("SELECT date_debut, date_fin FROM sae._message_demande_devis WHERE id_logement = $idLogement");
    $stmtdate->execute();
    $date = $stmtdate->fetch(PDO::FETCH_ASSOC);
}

$dateDebut = $date['date_debut'];
$dateFin = $date['date_fin'];

// Requete SQL remettant le logement disponible sur la periode anciennement bloque
$stmtdate = $dbh->prepare("UPDATE SAE._jour SET disponible = true, raison = '' WHERE id_logement = $idLogement AND date_jour BETWEEN '$dateDebut' AND '$dateFin'");
$stmtdate->execute();

header('Location: /index.php');
exit();

ob_end_flush();
?>