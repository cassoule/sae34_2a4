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

// Si le propriétaire a cliquer sur le bouton accepter ou refuser
if (isset($_POST['bouton'])) {
    $valeurBouton = $_POST["bouton"];
    
    // Si le propriétaire a cliquer sur le bouton accepter
    if ($valeurBouton == "accepter") {
        // Si $_GET n'est pas vide
        if (!empty($_GET)){
            // Récuperer le nom du logement a l'aide de son id
            $id_logement = $_GET['idLogement'];
            $id_message_demande_devis = $_GET['idMessageDevis'];

            // Récupération des données liées à la réservation
            $result = $dbh->prepare("SELECT libelle_logement, id_proprietaire FROM sae._logement WHERE id_logement = :id_logement;");
            $result->bindParam(':id_logement', $id_logement);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $nom_logement = $row['libelle_logement'];
            $id_contient_proprietaire = $row['id_proprietaire'];

            $result = $dbh->prepare("SELECT * FROM SAE._message_demande_devis WHERE id_message_demande_devis = :id_message");
            $result->bindParam(':id_message', $id_message_demande_devis);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $nb_personnes = $row['nb_personnes'];
            $date_debut = $row['date_debut'];
            $date_fin = $row['date_fin'];

            $dateDebutObj = DateTime::createFromFormat('Y-m-d', $date_debut);
            $dateFinObj = DateTime::createFromFormat('Y-m-d', $date_fin);
            $diff = date_diff($dateDebutObj, $dateFinObj);
            $joursDeDifference = $diff->days;
            $condition_annulation_reservation = 'Standard';

            $prixTotal = 0;
            $dateDebutstr ="'".date_format( $dateDebutObj, 'Y-m-d')."'";
            $dateFinstr ="'".date_format( $dateFinObj, 'Y-m-d')."'";
            foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._jour ON _logement.id_logement = _jour.id_logement where _logement.id_logement = $id_logement and _jour.date_jour BETWEEN $dateDebutstr AND $dateFinstr ") as $row) {
                $prixTotal+= $row['tarif_nuit_ht'];
            }
            /*$prixParJour = $dbh->query("SELECT tarif_nuit_ht FROM sae._logement WHERE id_logement = $id_logement");
            $prixParJour = $prixParJour->fetch(PDO::FETCH_ASSOC);
            $prixParJour = $prixParJour['tarif_nuit_ht'];

            $prixTotal = $prixParJour * $joursDeDifference;*/

            $result = $dbh->prepare("SELECT * FROM SAE._message WHERE id_message = :id_message");
            $result->bindParam(':id_message', $id_message_demande_devis);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            
            // Récupère la liste des services choisis par le client
            $texteReservation = $row['contenu'];
            $posDebutServiceComplementaire = strpos($texteReservation, '- Service complémentaire:');
            $posFinServiceComplementaire = strpos($texteReservation, '- Charge additionnelle:');
            $servicesComplementaires = substr($texteReservation, $posDebutServiceComplementaire, $posFinServiceComplementaire - $posDebutServiceComplementaire);
            $servicesComplementairesArray = explode("\n", $servicesComplementaires);
            $servicesComplementairesArray = array_filter(array_map('trim', $servicesComplementairesArray), function($line) {
                return $line !== '- Service complémentaire:';
            });

            // Récupère le prix des services choisis par le client
            $texteReservation = $servicesComplementairesArray[0];
            $pattern = '/\b(\d+(?:\.\d{1,2})?)€ HT\b/';
            $prixService = 0;
            if (preg_match_all($pattern, $texteReservation, $matches)) {
                foreach ($matches[1] as $prix) {
                    $prixService += floatval($prix);
                }
            }

            // Récupère la liste des charges choisis par le client
            $texteReservation = $row['contenu'];
            $posDebutServiceComplementaire = strpos($texteReservation, '- Charge additionnelle:');
            $posFinServiceComplementaire = strpos($texteReservation, '- Tarif pour');
            $servicesComplementaires = substr($texteReservation, $posDebutServiceComplementaire, $posFinServiceComplementaire - $posDebutServiceComplementaire);
            $servicesComplementairesArray = explode("\n", $servicesComplementaires);
            $servicesComplementairesArray = array_filter(array_map('trim', $servicesComplementairesArray), function($line) {
                return $line !== '- Charge additionnelle:';
            });

            // Récupère le prix des charges choisis par le client
            $texteReservation = $servicesComplementairesArray[0];
            $pattern = '/\b(\d+(?:\.\d{1,2})?)€ HT\b/';
            $prixCharge = 0;
            if (preg_match_all($pattern, $texteReservation, $matches)) {
                foreach ($matches[1] as $prix) {
                    $prixCharge += floatval($prix);
                }
            }

            $prixNuit = $prixTotal;
            $prixTotal = $prixTotal + $prixService + $prixCharge;
            $taxeSejour = $prixTotal * 0.17;
            $prixTotal = $prixTotal + $taxeSejour;
        }

        // Ecriture du message du devis
        $message_devis = "Cher client, je suis ravi de vous informer que votre réservation pour notre magnifique logement a été enregistrée avec succès. Voici les détails de votre réservation : <br>
        - Nom du logement : $nom_logement <br>
        - Nombre de personnes : $nb_personnes <br>
        - Date de début de votre réservation : $date_debut <br>
        - Date de fin de votre réservation : $date_fin <br>
        - Condition d'annulation de la réservation : $condition_annulation_reservation <br>
        - Nombre de jours valides : $joursDeDifference <br><br>
        -------------- Détails du paiement ---------------------- <br><br>
        - Prix des charges : $prixCharge € <br> 
        - Prix des services : $prixService € <br>
        - Tarif du logement HT : $prixNuit € <br>
        - Prix total HT : $prixTotal €";

        // Date correctement formatée entre guillemets simples
        $date = date('Y-m-d H:i:s');
        // A modifier pour recuperer les bon id
        $stmtId = $dbh->prepare("SELECT id_contient_client, id_contient_proprietaire FROM SAE._message WHERE id_message = :id_message");
        $stmtId->bindParam(':id_message', $id_message_demande_devis);
        $stmtId->execute();
        $lstId = $stmtId->fetch(PDO::FETCH_ASSOC);
        $id_contient_client = $lstId['id_contient_client'];
        $id_contient_proprietaire = $lstId['id_contient_proprietaire'];

        // Enregistrement du message  dans la base de données
        try {
            $stmt = $dbh->prepare("INSERT INTO sae._message (contenu, date_envoi, id_emetteur, id_contient_client, id_contient_proprietaire) VALUES (:message_devis, :date, :id_contient_proprietaire, :id_contient_client, :id_contient_proprietaire)"); /* ici id_emetteur =  id_contient_client car c'est forcement un client qui fait une demande de devis*/
            $stmt->bindParam(':message_devis', $message_devis, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':id_contient_client', $id_contient_client, PDO::PARAM_INT);
            $stmt->bindParam(':id_contient_proprietaire', $id_contient_proprietaire, PDO::PARAM_INT);
    
            $stmt->execute();
        }
        // Pour gérer les cas d'erreur de la base de données
        catch (PDOException $e) {
            echo "Il y a eu une erreur lors de l'enregistrement : " . $e->getMessage();
        }

        // Récupération de l'id de la demande de devis
        // jsp pourquoi ca marche pas
        $result = $dbh->prepare("SELECT id_message FROM sae._message WHERE date_envoi = :date");
        $result->bindParam(':date', $date);
        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $id_message_devis = $row['id_message'];

        // Enregistrement de la demande de devis dans la base de données
        try {
            $stmt = $dbh->prepare("INSERT INTO sae._message_devis (id_message_devis, nb_personnes, date_debut, date_fin, condition_annulation_reservation, nb_jours_valide, taxe_sejour, id_logement) VALUES (:id_message_devis, :nb_personnes, :date_debut, :date_fin, :condition_annulation_reservation, :nb_jours_valide, :taxe_sejour, :id_logement)"); /* ici id_emetteur =  id_contient_client car c'est forcement un client qui fait une demande de devis*/
            $stmt->bindParam(':id_message_devis', $id_message_devis, PDO::PARAM_INT);
            $stmt->bindParam(':nb_personnes', $nb_personnes, PDO::PARAM_INT);
            $stmt->bindParam(':date_debut', $date_debut,);
            $stmt->bindParam(':date_fin', $date_fin);
            $stmt->bindParam(':condition_annulation_reservation', $condition_annulation_reservation, PDO::PARAM_STR);
            $stmt->bindParam(':nb_jours_valide', $joursDeDifference, PDO::PARAM_INT);
            $stmt->bindParam(':taxe_sejour', $taxeSejour);
            $stmt->bindParam(':id_logement', $id_logement, PDO::PARAM_INT);

            $stmt->execute();
        }
        // Pour gérer les cas d'erreur de la base de données
        catch (PDOException $e) {
            echo "Il y a eu une erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
    else {
        try {
            $stmt = $dbh->prepare("DELETE FROM _message_demande_devis WHERE id_message_demande_devis = $id"); 
            $stmt->execute(); //supprimer la demande de devis du 
            $stmt = $dbh->prepare("DELETE FROM _message WHERE id_message = $id"); 
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Il y a eu une erreur lors de la suppression : " . $e->getMessage();
        }
        header("Location: /devis.php");
        exit();
    }

    // Renvoies vers la messagerie avec un devis et arrête le script de la page
    header("Location: /devis.php?idLogement=$id_logement&idContact=$id_contient_client");
    exit();
}

ob_end_flush();
?>