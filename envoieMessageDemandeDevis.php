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
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php'); $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

if(!empty($_GET)){
    // Récupération du libelle et id_proprietaire du logement
    $id_logement = $_GET['idLogement'];
    $result = $dbh->prepare("SELECT libelle_logement, id_proprietaire FROM sae._logement WHERE id_logement = :id_logement;");
    $result->bindParam(':id_logement', $id_logement);
    $result->execute();
    $row = $result->fetch(PDO::FETCH_ASSOC);

    // Récupération des données liées à la réservation
    $nom_logement = $row['libelle_logement'];
    $id_contient_proprietaire = $row['id_proprietaire'];
    $nb_personnes = $_GET['nbPersonnes'];
    $date_debut = $_GET['dateDebut'];
    $date_fin = $_GET['dateFin'];

    // Préparation du message de demande de devis dans la messagerie
    $message_demande_devis = "Cher hôte, je suis ravi de vous informer que je souhaite réserver votre magnifique logement pour mon prochain séjour. Voici les détails de ma réservation : <br>
        - Nom du logement : $nom_logement <br>
        - Nombre de personnes : $nb_personnes <br>
        - Date de début de ma réservation : $date_debut <br>
        - Date de fin de ma réservation : $date_fin <br>
        - Service complémentaire: <br>";
    $message_services = "";

    // Récupération des options supplémentaires de la réservation si il existe
    $idLogement = $_GET['idLogement'];
    $total = 0;
    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._service ON _logement.id_logement = _service.id_logement where _logement.id_logement = $idLogement") as $row) {
        $cle = str_replace(" ", "_",[$row['nom_service']])[0];
        $prix = $row['prix_service_ht']; 
        
        if (isset($_GET[$cle])) {
            if ($_GET[$cle] == 1) {
                $message_services = $message_services ."- ". str_replace("_", " ", $cle) . " = " . $prix. "€ HT <br>";
                $total += $prix;
            }
        }
    }


          
    $message_demande_devis .= $message_services;    
    
    //ajout des charges complementaires dans le devis
    $message_charge = "- Charge additionnelle: <br>";
    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._charge ON _logement.id_logement = _charge.id_logement where _logement.id_logement = $idLogement") as $row) {
        $cle = str_replace(" ", "_",[$row['nom_charge']])[0];
        $prix = $row['prix_charge_ht'];
        if (isset($_GET[$cle])) {
            if ($_GET[$cle] == 1) {
                $message_charge = $message_charge ."- ". str_replace("_", " ", $cle) . " = " . $prix. "€ HT <br>";
                $total += $prix;
            }
        }
    }


    $message_demande_devis .= $message_charge; 
    
    $dateDebut = $_GET['dateDebut'];
    $dateFin = $_GET['dateFin'];
    $dateDebutObj = DateTime::createFromFormat('Y-m-d', $dateDebut);
    $dateFinObj = DateTime::createFromFormat('Y-m-d', $dateFin);
    $diff = date_diff($dateDebutObj, $dateFinObj);
    $joursDeDifference = $diff->days;
    $date = date('Y-m-d H:i:s');
    //pris du logement pour une journée

    $prixTTJour = 0;
    $dateDebutstr ="'".date_format( $dateDebutObj, 'Y-m-d')."'";
    $dateFinstr ="'".date_format( $dateFinObj, 'Y-m-d')."'";
    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._jour ON _logement.id_logement = _jour.id_logement where _logement.id_logement = $idLogement and _jour.date_jour BETWEEN $dateDebutstr AND $dateFinstr ") as $row) {
        $prixTTJour+= $row['tarif_nuit_ht'];
    }




    /*$prixParJour = $dbh->query("SELECT tarif_nuit_ht FROM SAE._logement INNER JOIN SAE._jour ON _logement.id_logement = _jour.id_logement where _logement.id_logement = $idLogement");
    $prixParJour = $prixParJour->fetch(PDO::FETCH_ASSOC);
    print_r($prixParJour);
    $prixParJour = $prixParJour['tarif_nuit_ht'];
    //print_r($prixParJour);
    //calcul prix jour * pris par jour
    $prixTTJour = $joursDeDifference * $prixParJour;*/
    //print_r($prixJour);
    $message_demande_devis .= "- Tarif pour $joursDeDifference nuit(s) = $prixTTJour €<br>";
    //print_r($_SESSION);
    $id_contient_client = $_SESSION['idUtilisateur'];
    //print_r($id_contient_client);
    $total += $prixTTJour;
    $message_demande_devis .= "Prix total HT = $total €<br>";
    //17%
    $taxe = $total*0.17;
    $message_demande_devis .= "- Total taxe = $taxe €<br>";
    $totaltaxe = $total + $taxe;
    $message_demande_devis .= "TOTAL = $totaltaxe €";

/*
    foreach($dbh->prepare("SELECT * FROM sae._messagerie") as $row){
        echo "c1" . $row['id_client'];
        echo "c2" . $id_contient_client;
        echo "p1" . $row['id_proprietaire'];
        echo "p2" . $id_contient_proprietaire;

        if($row['id_client'] == $id_contient_client && $row['id_proprietaire'] == $id_contient_proprietaire){
            try {
                $stmt = $dbh->prepare("INSERT INTO sae._messagerie (id_client, id_proprietaire) VALUES (:id_contient_client, :id_contient_proprietaire)");
                $stmt->bindParam(':id_contient_client', $id_contient_client);
                $stmt->bindParam(':id_contient_proprietaire', $id_contient_proprietaire);
        
                $stmt->execute();
            }

            // Pour gérer les cas d'erreur de la base de données
            catch (PDOException $e) {
                echo "Il y a eu une erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
        
    }
    */
    
    
    //print_r($message_demande_devis);
    // Enregistrement du message  dans la base de données
    try {
        $stmt = $dbh->prepare("INSERT INTO sae._message (contenu, date_envoi, id_emetteur, id_contient_client, id_contient_proprietaire) VALUES (:message_demande_devis, :date, :id_contient_client, :id_contient_client, :id_contient_proprietaire)");
        $stmt->bindParam(':message_demande_devis', $message_demande_devis);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':id_contient_client', $id_contient_client);
        $stmt->bindParam(':id_contient_proprietaire', $id_contient_proprietaire);

        $stmt->execute();
    }
    // Pour gérer les cas d'erreur de la base de données
    catch (PDOException $e) {
        echo "Il y a eu une erreur lors de l'enregistrement : " . $e->getMessage();
    }

    // Récupération de l'id_message lié à notre devis
    $result = $dbh->prepare("SELECT id_message FROM sae._message WHERE date_envoi = :date");
    $result->bindParam(':date', $date);
    $result->execute();
    $row = $result->fetch(PDO::FETCH_ASSOC);

    $id_message_demande_devis = $row['id_message'];    
              

    // Enregistrement de la demande de devis dans la base de données
    try {
        $stmt = $dbh->prepare("INSERT INTO sae._message_demande_devis (id_message_demande_devis, nb_personnes, date_debut, date_fin, id_logement) VALUES (:id_message_demande_devis, :nb_personnes, :date_debut, :date_fin, :id_logement)");
        $stmt->bindParam(':id_message_demande_devis', $id_message_demande_devis);
        $stmt->bindParam(':nb_personnes', $nb_personnes);
        $stmt->bindParam(':date_debut', $date_debut);
        $stmt->bindParam(':date_fin', $date_fin);
        $stmt->bindParam(':id_logement', $_GET['idLogement']);

        $stmt->execute();
    }
    // Pour gérer les cas d'erreur de la base de données
    catch (PDOException $e) {
        echo "Il y a eu une erreur lors de l'enregistrement : " . $e->getMessage();
    }

    // Renvoies vers la messagerie avec une demande de devis et arrête le script de la page
    //header("Location: /messagerie.php?idLogement=$id_logement&nbPersonne=$nb_personnes&dateDebut=$date_debut&dateFin=$date_fin&message_services=$message_services&message_charge=$message_charge");
    $message_demande_devis = str_replace("<br>", " ", $message_demande_devis);
    $message_demande_devis = str_replace("\n", " ", $message_demande_devis);

    header("Location: /message.php?idLogement=$id_logement&idContact=$id_contient_proprietaire");
    exit();

ob_end_flush();
}
?>