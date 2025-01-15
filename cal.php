<?php
if (ob_get_level() > 0) {
    // Vide le tampon de sortie
    ob_clean();
}

header('Content-Type: text/calendar; charset=utf-8');
//header('Content-Disposition: inline; filename="calendar.ics"');

/*$events = array(
    array(
        'summary' => 'Event 1',
        'description' => 'This is event 1',
        'location' => 'Location 1',
        'start' => '20240326T090000Z',
        'end' => '20240326T100000Z',
    ),
    array(
        'summary' => 'Event 2',
        'description' => 'This is event 2',
        'location' => 'Location 2',
        'start' => '20240328T090000Z',
        'end' => '20240328T100000Z',
    ),
);*/

$events = array();

$ical = "BEGIN:VCALENDAR\r\n";
$ical .= "VERSION:2.0\r\n";
$ical .= "PRODID:-//My Calendar//EN\r\n";
$ical .= "CALSCALE:GREGORIAN\r\n";
$ical .= "METHOD:PUBLISH\r\n";

//print_r($_GET['token']);

// Chargement de la base de données
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

//nombre de logements
$stmtCal = $dbh->prepare("SELECT * FROM sae._clecalendrier WHERE token = :token");
$stmtCal->bindParam(':token', $_GET['token']);
$stmtCal->execute();
$stmtCal = $stmtCal->fetch(PDO::FETCH_ASSOC);

$dateDebut = $stmtCal['debut'];
$dateFin = $stmtCal['fin'];
$reservation = $stmtCal['reservation'];
$indisponibilite = $stmtCal['indisponibilite'];
$demandereservation = $stmtCal['demandereservation'];
$proprietaire = $stmtCal['id_proprietaire'];
//print_r($dateDebut);
//print_r($dateFin);
//print_r($indisponibilite);
//print_r($reservation);

//print_r($stmtCal);
date_default_timezone_set('Europe/Paris');


$listelogement = explode(",", substr($stmtCal['logement'], 1, -1));
foreach ($listelogement as $key => $value) {
    //$stmt = $dbh->prepare("SELECT * FROM SAE._jour NATURAL JOIN SAE._logement NATURAL JOIN sae._messagerie NATURAL JOIN sae._message_devis where raison = 'reserve' and id_logement = :id_logement;");
    $stmt = $dbh->prepare("SELECT * FROM sae._jour WHERE id_logement = :id_logement AND disponible = false AND date_jour BETWEEN :date_debut AND :date_fin");
    $stmt->bindValue(':id_logement', $value);
    $stmt->bindValue(':date_debut', $dateDebut);
    $stmt->bindValue(':date_fin', $dateFin);
    $stmt->execute();

    foreach ($stmt as $logement) {

        //traiter les indisponibilité
        //print_r($logement);
        $libelle_logement = $dbh->prepare("SELECT libelle_logement FROM sae._logement WHERE id_logement = :id_logement");
        $libelle_logement->bindParam(':id_logement', $logement['id_logement']);
        $libelle_logement->execute();
        $libelle_logement = $libelle_logement->fetch(PDO::FETCH_ASSOC);
        $libelle_logement = $libelle_logement['libelle_logement'];

        $id_adresse = $dbh->prepare("SELECT id_adresse FROM sae._logement WHERE id_logement = :id_logement");
        $id_adresse->bindParam(':id_logement', $logement['id_logement']);
        $id_adresse->execute();
        $id_adresse = $id_adresse->fetch(PDO::FETCH_ASSOC);
        $id_adresse = $id_adresse['id_adresse'];

        $adresse = $dbh->prepare("SELECT * FROM sae._adresse WHERE id_adresse = :id_adresse");
        $adresse->bindParam(':id_adresse', $id_adresse);
        $adresse->execute();
        $adresse = $adresse->fetch(PDO::FETCH_ASSOC);
        $adresse = "" . $adresse['adresse'] . " " . $adresse['complement_adresse'] . " " . $adresse['code_postal'] . " " . $adresse['ville'];
        //print_r($adresse);
        //, vous pouvez le changer dans la ruvrique "Mes logement -> modifier mon logement -> modifier le calendrier"',

        $date_debut = $logement['date_jour'];
        $date_fin = $logement['date_jour'];

        $heure_debut = "08:00:00";
        $heure_fin = "18:00:00";

        $date_debut = date("Ymd\THis", strtotime($date_debut . " " . $heure_debut));
        $date_fin = date("Ymd\THis", strtotime($date_fin . " " . $heure_fin));
        //print_r($logement['raison']);
        if ($logement['raison'] == 'raison personnelle' && $indisponibilite) {
            //print_r("aaaaaaaaa");

            $events[] = array(
                'summary' =>  "Votre logement " . $libelle_logement . " est indisponible",
                'description' => 'Vous avez rendu votre logement indisponible',
                'location' => $adresse,
                'start' => $date_debut,
                'end' => $date_fin,
                'tzid' => 'Europe/Paris'
            );
        }
        if ($logement['raison'] == 'reserve' && $reservation) {

            //print_r("aaaaaaaaa");
            
            /*
            $client = $dbh->prepare("SELECT * from sae._message join sae._message_devis md on sae._message.id_message = md.id_message_devis;");
            $client->execute();
            //$client = $client->fetch(PDO::FETCH_ASSOC);
            print_r($client);

            $personne = "";
            $nbpersonne = "";
            foreach ($client as $detail) {
                
                if ($detail['id_logement'] == $value && $detail['id_contient_proprietaire'] == $proprietaire) {
                    $personne = $detail['id_contient_client'];
                    $nbpersonne = $detail['nb_personnes'];
                }
                
            }

            print_r($personne);
            

            $idclient = $dbh->prepare("SELECT * from sae._compte where id_compte = :id_compte;");
            $idclient->bindParam(':id_compte', $personne);
            $idclient->execute();
            $idclient = $idclient->fetch(PDO::FETCH_ASSOC);
            $idclient = $idclient['prenom'] . " " . $idclient['nom'];

            */

            

            $events[] = array(
                'summary' => "Votre logement " . $libelle_logement . " est reserve",
                //'description' => 'Votre logement est reserve par ' . $idclient ." pour ". $nbpersonne . " personne(s).",
                'description' => 'Votre logement est reserve .',
                'location' => $adresse,
                'start' => date('Ymd\THis\Z', strtotime($logement['date_jour'])),
                'end' => date('Ymd\T235959\Z', strtotime($logement['date_jour'])),
                'tzid' => 'Europe/Paris'
            );
        }
    }
}
//print_r($events);

foreach ($events as $event) {
    $ical .= "BEGIN:VEVENT\r\n";
    $ical .= "UID:" . md5(uniqid(mt_rand(), true)) . "@example.com\r\n";
    $ical .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
    $ical .= "DTSTART:" . $event['start'] . "\r\n";
    $ical .= "DTEND:" . $event['end'] . "\r\n";
    $ical .= "SUMMARY:" . $event['summary'] . "\r\n";
    $ical .= "DESCRIPTION:" . $event['description'] . "\r\n";
    $ical .= "LOCATION:" . $event['location'] . "\r\n";
    $ical .= "END:VEVENT\r\n";
}

$ical .= "END:VCALENDAR\r\n";

//print_r($events);

//ob_clean();
//echo substr($chaine, 1)
echo $ical;
?>