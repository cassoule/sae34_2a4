<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$cpt = 0;
$res = [];

// Récupération de l'id du client
$idClient = $_SESSION['idUtilisateur'];
$nb = 0;
// Récupération des différentes réservations du client
foreach ($dbh->query("SELECT * from SAE._reservation join SAE._message on id_message_devis = id_message join SAE._compte on id_contient_proprietaire = _compte.id_compte") as $row) {
    foreach ($row as $key => $value) {
        $nb += 1;
        if ($value == $idClient and $key == 'id_contient_proprietaire') {
            $cpt = $cpt + 1;
            $res[$cpt] = $row;
        }
    }
}

//print_r($nb);

foreach ($res as $key => $value) {
    $id_devis = $value['id_message_devis'];

    $stmtDevis = $dbh->prepare("SELECT date_debut, date_fin, id_logement FROM SAE._message_devis WHERE id_message_devis = :id_message_devis");
    $stmtDevis->bindParam(':id_message_devis', $id_devis, PDO::PARAM_INT);
    $stmtDevis->execute();
    $date = $stmtDevis->fetch(PDO::FETCH_ASSOC);

    $res[$key][] = $date;

    $stmtAdresse = $dbh->prepare("SELECT ville FROM SAE._adresse WHERE id_adresse = :id_adresse");
    $stmtAdresse->bindParam(':id_adresse', $value['id_adresse']);
    $stmtAdresse->execute();
    $ville = $stmtAdresse->fetch(PDO::FETCH_ASSOC);

    $res[$key][] = $ville;

    $stmtLogement = $dbh->prepare("SELECT * FROM SAE._logement WHERE id_logement = :id_logement");
    $stmtLogement->bindParam(':id_logement', $res[$key][0]['id_logement']);
    $stmtLogement->execute();
    $logement = $stmtLogement->fetch(PDO::FETCH_ASSOC);

    $res[$key][] = $logement;
}

$nb = count($res);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'accueil</title>
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
    </header>

    <div id="profil-overlay">
        <a href="/consulterSonCompte.php">Mon Compte</a>
        <?php
            if ($proprietaire=="Propriétaire") {
            ?>
        <a href="/mesLogements.php">Mes logements</a>
        <a href="/mesReservationsProprietaires.php">Mes réservations</a>
        <?php
            }
        else{
            ?>
        <a href="/mesReservationsClients.php">Mes Réservations</a>
        <?php
            }
            ?>

        <a href="/devis.php">Mes devis</a>
        <form class="deconnexion" action="/index.php?deconnexion=true" method="post" class="blue-rectangle">
            <input type="submit" value="Déconnexion">
        </form>
    </div>
    

    <?php if ($cpt >= 2) {
        ?> <div class="background_svg bs_top">
        <div class="back_top_svg">
            <svg class="top_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z"
                    fill="#0777DE" />
            </svg>

            <svg class="top_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.5"
                    d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z"
                    fill="#0777DE" />
            </svg>

            <svg class="top_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3"
                    d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z"
                    fill="#0777DE" />
            </svg>

            <svg class="top_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.2"
                    d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z"
                    fill="#0777DE" />
            </svg>

            <svg class="top_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.1"
                    d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z"
                    fill="#0777DE" />
            </svg>
        </div>
    </div> <?php
    }
    
        if ($nb >= 2) {
           ?> <main class="both-svg-main"> <?php
        } else {
            ?> <main class="bottom-svg-main"> <?php
        }
        ?>
            <!-- Affichage des informations principales de la page -->
                <div class="container2 invisible">
                    <div class="big-card-Resa">
                        <div class="recherche">
                            
                            <h2>trier les réservations par <select class="select-date">
                                    <option value="date">date</option>
                                    <option value="prix">prix</option>
                                </select>
                            </h2>
                        </div>
                    </div>


                    <?php
                //print_r($nb);
                if ($cpt > 0) {
                    foreach ($res as $key => $value) {
                ?>
                    <div class="big-card-Resa">
                        <div class="carroussel">
                            <div class="carousel-container">
                                <?php  
                                foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                                    if ($image['id_logement_image'] == $value[0]['id_logement']) {
                                ?>
                                        <img src="<?php echo $image['lien_image'] ?>" alt="Photo logement" height="250" width="350" class="pLog">
                                <?php
                                        break;
                                    }
                                }
                                ?>      
                            </div>
                        </div>
                            <div class="infoLog">
                                <div class="infos">
                                    <h3><?php echo $value[1]['ville'] ?></h3>
                                    <h3><?php echo $value[0]['date_debut'] ?> -> <?php echo $value[0]['date_fin'] ?></h3>

                                    <h4><?php echo $value[2]['avis_logement_total'] ?>/5</h4>

                                    <!-- Affiche le nombre d'avis sur le logement -->
                                    <?php 
                                    $nbAvis = $dbh->prepare("SELECT COUNT(*) AS nombre_avis FROM SAE._avis WHERE id_logement = :id_logement");
                                    $nbAvis->bindParam(':id_logement', $value[2]['id_logement'], PDO::PARAM_INT);
                                    $nbAvis->execute();
                                    $nbAvis = $nbAvis->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <h4><?php echo $nbAvis['nombre_avis']?> avis clients</h4>

                                    <?php  
                                    foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                                        if ($image['id_compte'] == $value['id_contient_proprietaire']) {
                                    ?>
                                            <img src="<?php echo $image['lien_image'] ?>" alt="Photo profil" height="75" width="75">
                                    <?php
                                            break;
                                        }
                                    }
                                    ?>
                                    <h4><?php echo $value['prenom'] ?> <?php echo $value['nom'][0] ?>.</h4>

                                    <p class="description-detaille"><?php echo $value[2]['description_detaille'] ?></p>
                                </div>

                                <div class="buttons">
                                    <form action="/logementDetail.php?idLogement=<?php echo $value[0]['id_logement'] ?>" method="post">
                                        <input class="submit" type="submit" value="Détail logement">
                                        <br>
                                        <br>
                                    </form>
                                        <!--
                                    <form action="#" method="post">
                                        <input type="reset" id="annulerReservation" name="annulerReservation" value="annuler reservation" class="boutonAn">
                                    </form>
                                    -->
                                </div>
                            </div>
                        </div>
                        <br>
                <?php
                    }
} else { 
                    //faire quand il n'y a pas de reservations
                    ?>
                    <div class="big-card-Resa">
                        <h3> Vous n'avez aucune reservation pour le moment </h3>
                    </div>
                <?php
                }
            ?>
                </div>


                <div class="background_svg bs_bottom">
                    <div class="back_bottom_svg ">
                        <svg class="bottom_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z"
                                fill="#FFB74C" />
                        </svg>

                        <svg class="bottom_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.5"
                                d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z"
                                fill="#FFB74C" />
                        </svg>

                        <svg class="bottom_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.3"
                                d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z"
                                fill="#FFB74C" />
                        </svg>

                        <svg class="bottom_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.2"
                                d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z"
                                fill="#FFB74C" />
                        </svg>

                        <svg class="bottom_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.1"
                                d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z"
                                fill="#FFB74C" />
                        </svg>
                    </div>
                </div>

            </main>

            <!-- Affichage du footer et des informations à propos du site -->
            <!-- Affichage du footer et des informations à propos du site -->
            <footer class="connected-footer">
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