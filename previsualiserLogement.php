<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$id_logement = $_GET['id_logement'];

$stmtLogement = $dbh->prepare("SELECT * FROM SAE._logement WHERE id_logement = :id_logement");
$stmtLogement->bindParam(':id_logement', $id_logement);
$stmtLogement->execute();
$logement = $stmtLogement->fetch(PDO::FETCH_ASSOC);

// Si jamais l'utilisateur se connecte sur la page et qu'il ne s'est pas connecté alors on lui donne l'idUtilisateur 1 qui correspond au compte visiteur
if (!isset($_SESSION['idUtilisateur'])) {
    $_SESSION['idUtilisateur'] = 1;
}

$modifier = false;

if (isset($_GET['type_demande'])) {
    if ($_GET['type_demande'] == 'modifier') {
        $modifier = true;
    }
    else if ($_GET['type_demande'] == 'casser') {
        $id_logement_casser = $_GET['id_logement_casser'];
        $id_logement = $_GET['id_logement'];

        $stmtJour = $dbh->prepare("UPDATE SAE._jour SET id_logement = $id_logement WHERE id_logement = $id_logement_casser");
        $stmtJour->execute();

        header("Location: ./supprimerLogement.php?id_logement=$id_logement_casser");
        exit();
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Prévisualisation du logement</title>
        <link rel="stylesheet" href="style/style.css">
    </head>
    <body>
        <!-- Affichage du header du site -->
        <header>
            <!-- Affichage du logo et du nom du site -->
            <a class="logoNom" href="/index.php">
                <img src="src/logo.png" alt="Logo ALHaIZ Breizh">
                <h1>ALHaIZ Breizh</h1>
            </a>

            <!-- Affichage de différents informations en fonction d'un visiteur / client / propriétaire -->   
            <?php
            if ($_SESSION['idUtilisateur'] == 1) {
            ?>
                <div class="logInfo">
                    <a class="white-rectangle" href="/creerCompte.php">S'enregistrer</a>
                    <a class="blue-rectangle" href="/connexion.php">Se connecter</a>
                </div>
            <?php
            }
            else {
            ?>
                <!-- Affichage des informations lié au compte de la personne -->
                <div class="lien-profil" id="profil-overlay-button" onclick="open_overlay()">
                    <?php
                    foreach ($dbh->query("SELECT * FROM SAE._compte") as $compte) {
                        if ($compte['id_compte'] == $_SESSION['idUtilisateur']) {
                            $pseudo = $compte['pseudo'];
                            $id_compte = $compte['id_compte'];
                        }
                    }
                    $lien_image = "./img/photo_de_profil_neutre.png";
                    foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                        if ($image['id_compte'] == $_SESSION['idUtilisateur']) {
                            $lien_image =  $image['lien_image'];
                            break;
                        }
                    }
                    ?>

                    <!-- Affichage de différentes informations suivant le client / propriétaire -->
                    <?php
                    $proprietaire = false;
                    foreach ($dbh->query("SELECT id_proprietaire FROM sae._proprietaire") as $row) {
                        if($row['id_proprietaire'] == $id_compte){
                            $proprietaire = true;
                        }
                    }
                    if($proprietaire == true){
                        $proprietaire = "Propriétaire";
                    }else {
                        $proprietaire = "Client";
                    }
                    ?>
                    <div class="info-text">
                        <p class="pseudo"><?php echo $pseudo;?></p>
                        <p class="proprietaire"><?php echo $proprietaire;?></p>
                    </div>
                    <div class="pdp-container">
                        <img class="pdp" src="<?php echo $lien_image?>" alt="pdp">
                    </div>
                </div>
            <?php
            }
            ?>
        </header>
                          
        <!-- Overlay menu contextuel une fois connecté - impératif sur chaque page avec un header connecté -->
        <div id="overlay-exit" onclick="open_overlay()"></div>

        <div id="profil-overlay">
            <a href="/consulterSonCompte.php">Mon compte</a>
            <?php
            if ($proprietaire=="Propriétaire") {
            ?>
                <a href="/mesLogements.php">Mes logements</a>
                <a href="/mesReservationsProprietaires.php">Mes réservations</a>
            <?php
            }else{
            ?>
                <a href="/mesReservationsClients.php">Mes réservations</a>
            <?php
            }
            ?>

            <a href="/devis.php">Mes devis</a>
            <form class="deconnexion" action="/index.php?deconnexion=true" method="post" class="blue-rectangle">
                <input type="submit" value="Déconnexion">
            </form>
        </div>

        <div class="background_svg bs_top">
            <div class="back_top_svg">
                <svg class="top_svg svg-1" viewBox="0 0 1920 622" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-2" viewBox="0 0 2337 766" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path opacity="0.5" d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-3" viewBox="0 0 3253 890" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path opacity="0.3" d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-4" viewBox="0 0 3495 1056" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path opacity="0.2" d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-5" viewBox="0 0 3692 1176" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path opacity="0.1" d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z" fill="#0777DE"/>
                </svg>
            </div>
        </div>
        
        <div class="images-overlay">
            <div class="overlay-back" onclick=""></div>
            <div id="previousImg" onclick="previousOverlayImage()"><i class="fa-solid fa-caret-left"></i></div>
            <?php
            $img_count = 0;
            foreach($dbh->query("SELECT * FROM SAE._image") as $image){
                if($img_count >= 7){
                    break;
                }else if($image['id_logement_image'] == $logement['id_logement']){
                    $lien_image_logement = $image['lien_image'];
                    $img_count++;
                    ?>
                    <img id="ovr-img-<?php echo $img_count?>" class="ovr-img" src="<?php echo $lien_image_logement?>" alt="inactive">
                    <?php
                }
            }
            ?>
            <div id="nextImg" onclick="nextOverlayImage()"><i class="fa-solid fa-caret-right"></i></div>

            <div class="points-container">
                <?php
                for($i = 1; $i <= $img_count; $i++){
                    ?>
                    <div id="pt-<?php echo $i?>" class="points" role="inactive" onclick="overlayGoTo(<?php echo $i;?>)"></div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- Affichage des informations principales de la page -->
        <main class="both-svg-main">
            <div class="has-aside">
                <div class="container-logement">
                    <!-- Affiche toutes les informations du logement choisis -->
                    <div class="container white-bg">
                        <div class="img-panel">
                            <?php
                            $img_count = 0;
                            foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                                if($img_count>=7){
                                    break;
                                }else if ($image['id_logement_image'] == $logement['id_logement']) {
                                    $lien_image_logement = $image['lien_image'];
                                    $img_count++;
                                    if($img_count == 1){
                                        ?>
                                        <div class="detail-img-container detail-<?php echo $img_count?> img-l">
                                            <img id="img-<?php echo $img_count?>" src="<?php echo $lien_image_logement?>" alt="Photo du logement">
                                        </div>
                                        <?php
                                    }else if($img_count >= 2 && $img_count <= 3){
                                        ?>
                                        <div class="detail-img-container detail-<?php echo $img_count?> img-m">
                                            <img id="img-<?php echo $img_count?>" src="<?php echo $lien_image_logement?>" alt="Photo du logement">
                                        </div>
                                        <?php
                                    }
                                }
                                $other_img = $img_count;
                            }

                            while($img_count < 7){
                                $img_count++;
                                if($img_count == 1){
                                    ?>
                                    <div class="detail-img-container detail-<?php echo $img_count?> img-l"></div>
                                    <?php
                                }else if($img_count >= 2 && $img_count <= 3){
                                    ?>
                                    <div class="detail-img-container detail-<?php echo $img_count?> img-m"></div>
                                    <?php
                                }
                            }
                            $other_img = $other_img - 3;
                            if($other_img > 0){
                            ?>
                                <div class="img-counter" onclick="overlayImage4()"><?php echo $other_img . "+";?></div>
                            <?php
                            }
                            ?>
                        </div>

                        <div class="row libelle-row">
                            <h2><?php echo $logement['libelle_logement'] ?></h2>
                            <a href="#"><i class="fa-regular fa-flag"></i></a>
                        </div>
                        <div class="row adresse-row">
                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._adresse") as $adresse) {
                                if ($adresse['id_adresse'] == $logement['id_adresse']) {
                            ?>
                                    <h4><?php echo $adresse['adresse'] . ', ' . $adresse['ville'] ?></h4>
                            <?php
                                }
                            }
                            ?>
                        </div>

                        <div class="row description-row">
                            <div class="left-section">
                                <div class="top-part">
                                    <h4>Type : <?php echo $logement['type_logement'] ?></h4>
                                    <h4>Surface : <?php echo $logement['surface'] ?></h4>
                                    <h4><?php echo $logement['nature_logement'] ?></h4>
                                    <div class="row libelle-row">
                                        <?php
                                        foreach ($dbh->query("SELECT * FROM SAE._compte") as $compte) {
                                            if ($compte['id_compte'] == $logement['id_proprietaire']) {
                                        ?>
                                                <h4>Propriétaire : <?php echo $compte['prenom'] . ' ' . $compte['nom'][0] . '.'?></h4>
                                        <?php
                                            }
                                        }
                                        ?>
                                        <h4><?php echo $logement['avis_logement_total'] . '/5'?> <i class="fa-solid fa-star"></i></h4>
                                    </div>
                                </div>

                                <a href="#details" class="yellow-button">Détails</a>
                            </div>

                            <div class="vertical-separator"></div>
                            
                            <div class="right-section">
                                <div class="description-text">
                                    <h3>Description</h3>
                                    <p><?php echo $logement['description_detaille'];?></p>
                                </div>
                                
                                <!-- Bouton pour faire une réservation -->
                                <?php
                                $estPresent = false;
                                foreach ($dbh->query("SELECT id_proprietaire FROM SAE._proprietaire") as $row) {
                                    if ($row['id_proprietaire'] == $_SESSION['idUtilisateur']) {
                                        $estPresent = true;
                                    }
                                }
                                
                                if ($_SESSION['idUtilisateur'] > 1 && $estPresent == false) {
                                ?>
                                    <form action="/reservation.php?idLogement=<?php echo $id_logement ?>" method="post">
                                        <input class="button-resa" type="submit" value="réserver" style="opacity: .2; pointer-events: none;">
                                    </form>
                                <?php
                                }else{
                                ?>
                                    <form action="/reservation.php?idLogement=<?php echo $id_logement ?>" method="post">
                                        <input class="button-resa" type="submit" value="réserver" style="opacity: .2; pointer-events: none;">
                                    </form>
                                <?php
                                }
                                ?>
                            </div>
                        </div>

                        <div class="row infos-row">
                            <!-- Affiche des informations complémentaires sur le logement -->
                            <div class="container-details white-bg" id="details">
                                <h4><?php echo $logement['libelle_logement'] ?></h4>
                                <h4><?php echo $logement['accroche'] ?></h4>
                                <h4><?php echo $logement['description_detaille'] ?></h4>
                            </div>

                            <!-- Affiche des informations pratiques sur le logement -->
                            <div class="container-infos white-bg">
                                <h3>Nombre lits doubles : <?php echo $logement['nb_lits_double'] ?></h3>
                                <h3>Nombre de lits simples : <?php echo $logement['nb_lits_simple'] ?></h3>
                                <h3>Nombre salle de bain : <?php echo $logement['nb_salle_de_bain'] ?></h3>
                                <h3>Nombre de chambre : <?php echo $logement['nb_chambres'] ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="row row-avis-planning">
                        <!-- Affiche le calendrier du logement -->
                        <div class="container container-planning">
                            <?php 
                            // Récupérer le mois et l'année à afficher
                            if (isset($_GET['mois']) && isset($_GET['annee'])) {
                                $mois = $_GET['mois'];
                                $annee = $_GET['annee'];
                            } else {
                                // Si aucun mois spécifié, utilisez le mois et l'année actuels
                                $mois = date("n");
                                $annee = date("Y");
                            }

                            // Calculer le premier jour du mois
                            $premierJour = mktime(0, 0, 0, $mois, 1, $annee);

                            // Nombre de jours dans le mois
                            $joursDansMois = date("t", $premierJour);
                            // Jour de la semaine du premier jour du mois 
                            $jourSemaine = date("N", $premierJour);

                            // Créer un tableau pour les noms de jours en commençant par lundi
                            $joursSemaine = array("Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim");
                            ?>

                            <!-- Liens pour naviguer entre les mois -->
                            <div>
                                <table>
                                    <thead>
                                        <tr>
                                            <?php
                                            // Afficher les noms de jours en commençant par lundi
                                            foreach ($joursSemaine as $jour) {
                                                echo "<th>$jour</th>";
                                            }
                                            ?>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <?php
                                            if ($modifier == true) {
                                                $id_logement_date = $_GET['id_modifier'];
                                            }
                                            else {
                                                $id_logement_date = $id_logement;
                                            }

                                            // Afficher les noms de jours en commençant par lundi
                                            foreach ($dbh->query("SELECT * FROM sae._jour WHERE id_logement =  $id_logement_date ORDER BY id_jour") as $row) { // id_logement !!!!
                                                $jour = date("d", strtotime($row['date_jour']));
                                                $bdd_mois = date("m", strtotime($row['date_jour']));
                                                $bdd_annee = date("Y", strtotime($row['date_jour']));

                                                if(date("d", strtotime($row['date_jour'])) == 1 && $bdd_mois == $mois && $bdd_annee == $annee){
                                                    // Remplir les premier jours du mois avec des cellules du mois precedent
                                                    for ($a = 1; $a < $jourSemaine; $a++) {
                                                        $jourMoisPrecedent = date("t", mktime(0, 0, 0, $mois - 1, 1, $annee)) - ($jourSemaine - $a) + 1;
                                                        echo "<td class='mois-precedent'>$jourMoisPrecedent</td>";
                                                    }
                                                }

                                                if ($bdd_mois == $mois && $bdd_annee == $annee){
                                                    if ($row['disponible'] == false){
                                                        ?><td class="indisponible"><?php echo $jour?></td><?php
                                                    } else {
                                                        ?><td class="disponible"><?php echo $jour?></td><?php
                                                    }
                                                }
                                                // Passer à une nouvelle ligne après chaque septième jour ou à la fin du mois
                                                if (($jour + $jourSemaine - 1) % 7 == 0) {
                                                    echo "</tr><tr>";
                                                }

                                                // Remplir les derniers jours du mois avec des cellules du mois suivant
                                                $dernierJour = date("N", mktime(0, 0, 0, $mois, $joursDansMois, $annee));
                                                

                                                if($jour == $joursDansMois && $bdd_mois == $mois && $bdd_annee == $annee){
                                                    //echo $joursDansMois;
                                                    for ($jour = $dernierJour + 1; $jour <= 7; $jour++) {
                                                        $jourMoisSuivant = $jour - $dernierJour;
                                                        echo "<td class='mois-suivant'>$jourMoisSuivant</td>";
                                                    }
                                                }           
                                            }
                                            ?>
                                        </tr>
                                    </tbody>
                                </table>

                                <div>
                                    <?php
                                    if ($modifier == true) {
                                    ?>
                                        <!-- modifier mettre en js pour ne par recharger la page a chaque fois -->
                                        <a href="?id_logement=<?php echo $id_logement ?>&type_demande=modifier&id_modifier=<?php echo $id_logement_date ?>&mois=<?php echo ($mois == 1) ? 12 : ($mois - 1); ?>&annee=<?php echo ($mois == 1) ? ($annee - 1) : $annee; ?>">Mois précédent</a>
                                        <h2><?php echo date("F Y", $premierJour); ?></h2>
                                        <a href="?id_logement=<?php echo $id_logement ?>&type_demande=modifier&id_modifier=<?php echo $id_logement_date ?>&mois=<?php echo ($mois == 12) ? 1 : ($mois + 1); ?>&annee=<?php echo ($mois == 12) ? ($annee + 1) : $annee; ?>">Mois suivant</a>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Affiche les options possibles du logement -->
                    <div class="container container-options white-bg">
                        <div class="options-card">
                            <h3>Charges additionnelles : </h3>
                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._charge") as $charge) {
                                if ($charge['id_logement'] == $logement['id_logement']) {
                            ?>
                                    <h3><?php echo $charge['nom_charge'] . " - " . $charge['prix_charge_ht'] . "€"?></h3>
                            <?php
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="options-card">
                            <h3>Service complémentaires : </h3>
                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._service") as $service) {
                                if ($service['id_logement'] == $logement['id_logement']) {
                            ?>
                                    <h3><?php echo $service['nom_service'] . " - " . $service['prix_service_ht'] . "€"?></h3>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="container white-bg">
                            <div class="left-section">
                                <?php
                                if ($modifier == false) {
                                ?>
                                    <form action="./supprimerLogement.php?id_logement=<?php echo $id_logement ?>" method="post">
                                        <input class="button-resa" type="submit" value="Annuler logement">
                                    </form>
                                <?php
                                }
                                else {
                                ?>
                                    <form action="./supprimerLogement.php?id_logement=<?php echo $id_logement ?>" method="post">
                                        <input class="button-resa" type="submit" value="Annuler logement">
                                    </form>
                                <?php
                                }
                                ?>
                            </div>

                            <div class="vertical-separator"></div>

                            <div class="right-section">
                                <?php
                                if ($modifier == false) {
                                ?>
                                    <form action="./mesLogements.php" method="post">
                                        <input class="button-resa" type="submit" value="Valider logement">
                                    </form>
                                <?php
                                }
                                else {
                                ?>
                                    <form action="./previsualiserLogement.php?id_logement=<?php echo $id_logement ?>&type_demande=casser&id_logement_casser=<?php echo $id_logement_date ?>" method="post">
                                        <input class="button-resa" type="submit" value="Valider logement">
                                    </form>
                                <?php
                                }
                                ?>
                            </div>
                    </div>
                </div>

                <!-- Affiche les options offertes dans le logement -->
                <aside>
                    <div class="container container-amenagements white-bg">
                        <h3>Aménagements</h3>
                        <div class="options-card">
                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._contient") as $amenagement) {
                                if  ($amenagement['id_logement'] == $logement['id_logement'] && $amenagement['nom_amenagement'] == "Jardin"){
                                    ?>
                                    <div class="card-options">
                                        <label for="jardin" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-tree"></i> Jardin</label>
                                    </div>
                                    <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._contient") as $amenagement) {
                                if  ($amenagement['id_logement'] == $logement['id_logement'] && $amenagement['nom_amenagement'] == "Balcon"){
                                    ?>
                                    <div class="card-options">
                                        <label for="balcon" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-person-through-window"></i>  Balcon</label>
                                    </div>
                                    <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._contient") as $amenagement) {
                                if  ($amenagement['id_logement'] == $logement['id_logement'] && $amenagement['nom_amenagement'] == "Terrasse"){
                                    ?>
                                    <div class="card-options">
                                        <label for="terrasse" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-table"></i>  Terrasse</label>
                                    </div>
                                    <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._contient") as $amenagement) {
                                if  ($amenagement['id_logement'] == $logement['id_logement'] && $amenagement['nom_amenagement'] == "Parking prive"){
                                    ?>
                                    <div class="card-options">
                                        <label for="parking_prive" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-square-parking"></i>  Parking Privé</label>
                                    </div>
                                    <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._contient") as $amenagement) {
                                if  ($amenagement['id_logement'] == $logement['id_logement'] && $amenagement['nom_amenagement'] == "Parking public"){
                                    ?>
                                    <div class="card-options">
                                    <label for="parking_public" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-square-parking"></i>  Parking Public</label>
                                    </div>
                                    <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._possede") as $installation) {
                                if ($installation['id_logement'] == $logement['id_logement'] && $installation['nom_installation'] == "Climatisation") {
                            ?>
                                    <div class="card-options">
                                        <label for="climatisation" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-wind"></i>  Climatisation</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._possede") as $installation) {
                                if ($installation['id_logement'] == $logement['id_logement'] && $installation['nom_installation'] == "Piscine") {
                            ?>
                                    <div class="card-options">
                                        <label for="piscine" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-water-ladder"></i>  Piscine</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._possede") as $installation) {
                                if ($installation['id_logement'] == $logement['id_logement'] && $installation['nom_installation'] == "Jacuzzi") {
                            ?>
                                    <div class="card-options">
                                        <label for="jacuzzi" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-hot-tub-person"></i>  Jacuzzi</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._possede") as $installation) {
                                if ($installation['id_logement'] == $logement['id_logement'] && $installation['nom_installation'] == "Hammam") {
                            ?>
                                    <div class="card-options">
                                        <label for="hammam" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-temperature-three-quarters"></i>  Hammam</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._possede") as $installation) {
                                if ($installation['id_logement'] == $logement['id_logement'] && $installation['nom_installation'] == "Sauna") {
                            ?>
                                    <div class="card-options">
                                        <label for="sauna" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-bath"></i>  Sauna</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._equipe") as $equipement) {
                                if ($equipement['id_logement'] == $logement['id_logement'] && $equipement["nom_equipement"] == "Television") {
                            ?>
                                    <div class="card-options">
                                        <label for="television" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-tv"></i>  Télevision</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._equipe") as $equipement) {
                                if ($equipement['id_logement'] == $logement['id_logement'] && $equipement["nom_equipement"] == "Lave-linge") {
                            ?>
                                    <div class="card-options">
                                        <label for="lave_linge" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-soap"></i>  Lave-linge</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._equipe") as $equipement) {
                                if ($equipement['id_logement'] == $logement['id_logement'] && $equipement["nom_equipement"] == "Barbecue") {
                            ?>
                                    <div class="card-options">
                                        <label for="barbecue" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-drumstick-bite"></i>  Barbecue</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._equipe") as $equipement) {
                                if ($equipement['id_logement'] == $logement['id_logement'] && $equipement["nom_equipement"] == "Wifi") {
                            ?>
                                    <div class="card-options">
                                        <label for="wifi" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-wifi"></i>  Wifi</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>                            

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._equipe") as $equipement) {
                                if ($equipement['id_logement'] == $logement['id_logement'] && $equipement["nom_equipement"] == "Seche-linge") {
                            ?>
                                    <div class="card-options">
                                        <label for="seche_linge" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-shirt"></i>  Sèche-linge</label>
                                    </div>
                            <?php
                                }
                            }
                            ?> 

                            <?php
                            foreach ($dbh->query("SELECT * FROM SAE._equipe") as $equipement) {
                                if ($equipement['id_logement'] == $logement['id_logement'] && $equipement["nom_equipement"] == "Lave-vaisselle") {
                            ?>
                                    <div class="card-options">
                                        <label for="lave_vaiselle" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-utensils"></i>  Lave-vaisselle</label>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="container container-prix white-bg">
                        <?php
                        $idLogement = $logement['id_logement'];
                        $total = 0;
                        foreach ($dbh->query("SELECT * FROM SAE._service WHERE id_logement = $id_logement") as $row) {
                            $total = $total + $row['prix_service_ht'];
                        }  
                        
                        $total_service = $total;

                        foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._charge ON _logement.id_logement = _charge.id_logement WHERE _logement.id_logement = $id_logement") as $row) {
                            $total += $row['prix_charge_ht'];
                        }

                        $prixmin = -1;
                        
                        foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._jour ON _logement.id_logement = _jour.id_logement WHERE _logement.id_logement = $id_logement_date") as $row) {
                            if ($prixmin == -1) {
                                $prixmin = $row['tarif_nuit_ht'];
                            }
                            else if ($row['tarif_nuit_ht'] < $prixmin){
                                $prixmin = $row['tarif_nuit_ht'];
                            }
                        }

                        $total_charge = $total - $total_service;
                        ?>
                        <div class="top-prix">
                            <h2><?php $prixTTC = $prixmin + $total; echo $prixTTC . "€" . " la nuit";?></h2>
                            <h4>Tarif : <?php echo $prixmin . "€/nuit"?></h4>
                            <h4>Charges : <?php echo $total_charge . "€"?></h4>
                            <h4>Services : <?php echo $total_service . "€"?></h4>
                        </div>
            
                        <!-- Bouton pour faire une réservation -->
                        <?php
                            $estPresent = false;
                            foreach ($dbh->query("SELECT id_proprietaire FROM SAE._proprietaire") as $row) {
                                if ($row['id_proprietaire'] == $_SESSION['idUtilisateur']) {
                                    $estPresent = true;
                                }
                            }
                            
                            if ($_SESSION['idUtilisateur'] > 1 && $estPresent == false) {
                            ?>
                                <form action="/reservation.php?idLogement=<?php echo $idLogement ?>" method="post">
                                    <input class="button-resa" type="submit" value="réserver" style="opacity: .2; pointer-events: none;">
                                </form>
                            <?php
                            }else{
                            ?>
                                <form action="/reservation.php?idLogement=<?php echo $idLogement ?>" method="post">
                                    <input class="button-resa" type="submit" value="réserver" style="opacity: .2; pointer-events: none;">
                                </form>
                            <?php
                            }
                            ?>
                    </div>
                </aside>
            </div>
            

            <div class="background_svg bs_bottom">
                <div class="back_bottom_svg ">
                    <svg class="bottom_svg svg-1" viewBox="0 0 1920 622" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z" fill="#FFB74C"/>
                    </svg>

                    <svg class="bottom_svg svg-2" viewBox="0 0 2337 766" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path opacity="0.5" d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z" fill="#FFB74C"/>
                    </svg>

                    <svg class="bottom_svg svg-3" viewBox="0 0 3253 890" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path opacity="0.3" d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z" fill="#FFB74C"/>
                    </svg>

                    <svg class="bottom_svg svg-4" viewBox="0 0 3495 1056" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path opacity="0.2" d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z" fill="#FFB74C"/>
                    </svg>

                    <svg class="bottom_svg svg-5" viewBox="0 0 3692 1176" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path opacity="0.1" d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z" fill="#FFB74C"/>
                    </svg>
                </div>     
            </div>
        </main>

        <!-- Affichage du footer et des informations à propos du site -->
        <footer>
            <div class="footer-top">
                <div class="logo-footer">
                    <a href="/index.php"><img src="src/logo.png" alt="Logo ALHaIZ Breizh"></a>
                    <h1>ALHaIZ Breizh</h1>
                    <a href="/easterEgg.html">&#xA0</a>
                </div>

                <div class="liens-footer">
                    <a href="#">Obtenir de l'aide</a>
                    <a href="#">Ajoutez votre logement</a>
                    <a href="#">À propos d'ALHaIZ Breizh</a>
                </div>
            </div>
            
            <div class="separator"></div>

            <div class="footer-bottom">
                <div class="reseaux">
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-square-facebook"></i></a>
                </div>

                <div class="liens-footer-2">
                    <a href="ConditionsGenerales.html">Conditions générales</a>
                    <a href="#">Politique de confidentialité</a>
                    <a href="MentionsLegales.html">Mentions Légales</a>
                    <a href="#">Tarifs</a>
                </div>
            </div>

            <div class="copyright">
                <p>© 2023 ALHaIZ Breizh Inc.</p>
            </div>
        </footer>
    </body>
    <script src="https://kit.fontawesome.com/1d8b63688b.js" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</html>