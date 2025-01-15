<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

error_reporting(0);

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$idUtilisateur = $_SESSION['idUtilisateur'];
$estProprietaire = false;

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page devis</title>
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

    <main>
        <?php if($taille >= 4) {
            ?>
        <div class="background_svg bs_top">
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
        </div>
        <?php
        }

        $id_logement = $_GET['idLogement'];

        foreach ($dbh->query("SELECT id_proprietaire FROM SAE._proprietaire") as $row) {
            if ($idUtilisateur == $row['id_proprietaire']){
                $estProprietaire = true;
            }
        }

        if ($estProprietaire == true) {
            $requete = "SELECT DISTINCT id_logement, id_proprietaire, id_contient_client, id_message FROM SAE._logement NATURAL JOIN sae._message WHERE id_contient_proprietaire = $idUtilisateur order by id_logement desc";
        }
        else {
            $requete = "SELECT DISTINCT id_logement, id_proprietaire, id_contient_client, id_message FROM SAE._logement NATURAL JOIN SAE._message WHERE id_contient_client = $idUtilisateur order by id_logement desc";
        }
        

        foreach ($dbh->query($requete) as $row) {
            $idLogementFiltres = $row['id_logement'];
            $id_client = $row['id_contient_client']; 
            $id_proprietaire = $row['id_proprietaire'];

            $id_message = $row['id_message'];
            $requeteDevis = "SELECT id_message_devis FROM SAE._message_devis";
            $estDevis = false;

            foreach ($dbh->query($requeteDevis) as $verifDevis) {
                if ($verifDevis['id_message_devis'] == $row['id_message']) {
                    $estDevis = true;
                }
            }
 
            $query = "SELECT count(*) FROM sae._logement INNER JOIN sae._adresse ON sae._logement.id_adresse = sae._adresse.id_adresse INNER JOIN sae._message_demande_devis ON sae._logement.id_logement = sae._message_demande_devis.id_logement INNER JOIN sae._message ON _message_demande_devis.id_message_demande_devis = _message.id_message WHERE _logement.id_logement = :id_logement AND id_contient_client = :id_client AND id_contient_proprietaire = :id_proprietaire";
            // Préparer la requête
            $stmt = $dbh->prepare($query);
            // Liage des paramètres
            $stmt->bindParam(':id_client', $id_client);
            $stmt->bindParam(':id_proprietaire', $id_proprietaire);
            $stmt->bindParam(':id_logement', $idLogementFiltres);

            // Exécuter la requête
            $stmt->execute();
            
            // Récupérer le résultat
            $count = $stmt->fetchColumn();
            
            // Afficher le résultat
            ?><div class="LlogementDevis">
            <?php if ($count != 0){
                if($estProprietaire == false){
                    ?>      
                    <a href="/message.php?idContact=<?php echo $id_proprietaire ?>&idLogement=<?php echo $idLogementFiltres ?>&id_message=<?php echo $row['id_message'] ?>" id="lienDevis">
                <?php
                } else {?>
                    <a href="/message.php?idContact=<?php echo $id_client ?>&idLogement=<?php echo $idLogementFiltres ?>&id_message=<?php echo $row['id_message'] ?>" id="lienDevis">
                <?php
                }
                ?>
                
                    <div id="<?php echo $idLogementFiltres ?>" data-nombre="<?php echo $prix['tarif_nuit_ht'] ?>" class="container-liste-logement ">
                        <!-- Affiche chaque image lié à un logement -->
                        <?php
                        foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                            if ($image['id_logement_image'] == $row['id_logement']) {?> 
                                <div class="card-liste-logements card-liste-logements-devis">
                                    <div class="image-container-liste ">
                                        <img class="cover-liste-logement" src="<?php echo $image['lien_image'] ?>" alt="Photo logement">
                                    </div>
                                </div>
                            <?php
                                break;
                            }
                        }
                        ?>
                        <div class="toutes-infos-logement">
                            <div class="infos-logement">   <!-- tout sauf bouton -->
                                <div class="tout-sauf-amenagement">
                                    <?php
                                    if ($estDevis == true) {
                                    ?>
                                        <p class="infoDevisP">Devis pour le client</p>
                                    <?php
                                    }
                                    else {
                                    ?>
                                        <p class="infoDevisP">Demande de devis pour le propriétaire</p>
                                    <?php
                                    }
                                    ?>

                                    <!-- Informations du logement -->
                                    <!-- Affiche la photo de profil du propriétaire ainsi que son nom et son prénom -->
                                    <div class="proprietaire-liste-logement">
                                    <?php
                                        foreach ($dbh->query("SELECT * FROM SAE._compte") as $compte) {
                                            if ($compte['id_compte'] == $row['id_contient_client']) {
                                                foreach ($dbh->query("SELECT * FROM SAE._image WHERE id_compte = " . $compte['id_compte']) as $img) {?>
                                                    
                                                    <img src="<?php echo $img['lien_image'] ?>" alt="Photo de profil propriétaire" height="85" width="85">
                                            <?php  
                                                } ?>
                                            <h3><?php echo $compte['prenom'] . " " . $compte['nom'][0] . "."?></h3>
                                            <?php
    
                                            }
                                        }
                                        ?>
                                    </div>
    
                                
                                </div>   
                            </div>              
                            <!-- Lien vers la page avec les détails du logement -->
                            <div id="form">
                                <a id="submit-liste-logement" href="/logementDetail.php?idLogement=<?php echo $row['id_logement']; ?>">Détails logement</a>
                            </div>
                        </div>
                    </div>
                    </div></div>
    
                <?php           
                }
            
            
            }?>
            
        


    </main>

    <!-- Affichage du footer et des informations à propos du site -->
    <?php
        if($_SESSION['idUtilisateur'] == 1){
        ?>
    <footer>
        <?php
        }else{
        ?>
        <footer class="connected-footer">
            <?php
        }
        ?>
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