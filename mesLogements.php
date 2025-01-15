<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Stocke l'idUtilisateur dans une nouvelle variable
$id_compte = $_SESSION['idUtilisateur'];

//nombre de logements
$stmtLogement = $dbh->prepare("SELECT COUNT(*) FROM sae._logement WHERE id_proprietaire = :id_compte");
$stmtLogement->bindParam(':id_compte', $id_compte);
$stmtLogement->execute();
$stmtLogement = $stmtLogement->fetch(PDO::FETCH_ASSOC);
$nblogement = $stmtLogement['count'];

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mes logements</title>
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
                <svg class="top_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.5" d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3" d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.2" d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.1" d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z" fill="#0777DE"/>
                </svg>
            </div>
        </div>
        
        <!-- Affichage des informations principales de la page -->
        <?php if ($nblogement > 2) {
            ?> <main class="both-svg-main"> <?php
        } else {
            ?> <main class="top-svg-main"> <?php
        } ?>
            <!-- Bouton pour qu'un propriétaire puisse ajouter un logement -->
            <div class="container MessageAccueil">
                <?php
                // Récupére les informations du propriétaire
                $result = $dbh->query("SELECT prenom FROM sae._compte WHERE id_compte = $id_compte");
                $row = $result->fetch(PDO::FETCH_ASSOC);
                    
                $pseudo = $row['prenom'];
                ?>
                <h2 class="messageBonjour"> <?php echo 'Bienvenue ' . $pseudo . ', voici vos logements.'?></h2>

                <form action="/ajouterLogement.php" method="post">
                    <input type="submit" value="Ajouter un logement">
                </form>
            </div>
 
            <!-- Affiche les informations principales des logements du propriétaire -->
            <div class="container">
                <?php
                // Récupére les informations du propriétaire
                $result = $dbh->query("SELECT id_proprietaire FROM sae._proprietaire WHERE id_proprietaire = $id_compte");
                $row = $result->fetch(PDO::FETCH_ASSOC);
                    
                $id_proprietaire = $row['id_proprietaire'];

                $result = $dbh->query("SELECT * FROM sae._compte WHERE id_compte = $id_compte");
                $row = $result->fetch(PDO::FETCH_ASSOC);
                ?>
                <?php

                // Parcours tous les logements du propriétaire
                foreach ($dbh->query("SELECT * FROM sae._logement WHERE id_proprietaire = $id_proprietaire") as $row) {
                ?>
                    <section class="mesLogements">
                        <!-- Lien vers la page détaillé du logement -->
                        <a class="card card-logement marge" href="/logementDetail.php?idLogement=<?php echo $row['id_logement'] ?>">
                                <!-- Recherche de l'image en fonction de son id_logement puis affichage de celle-ci -->
                                <?php
                                foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                                    if ($image['id_logement_image'] == $row['id_logement']) {
                                ?>
                                        <div class="img-container">
                                            <img class="cover" src="<?php echo $image['lien_image'] ?>" alt="Photo logement">
                                        </div>
                                <?php
                                        break;
                                    }
                                }
                                ?>

                                <div class="txt-container">
                                    <!-- Recherche de l'adresse en fonction de son id_adresse puis affichage de celui-ci -->
                                    <?php
                                    foreach ($dbh->query("SELECT * FROM SAE._adresse") as $adresse) {
                                        if ($adresse['id_adresse'] == $row['id_adresse']) {
                                    ?>
                                            <h2><?php echo $adresse['ville'] ?><p><?php echo $adresse['code_postal']?></p></h2>
                                    <?php
                                        }
                                    }
                                    
                                    $idLogement = $row['id_logement'];
                                    $total = 0;
                                    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._service ON _logement.id_logement = _service.id_logement where _logement.id_logement = $idLogement") as $row) {
                                        $total += $row['prix_service_ht'];
                                    }
                                    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._charge ON _logement.id_logement = _charge.id_logement where _logement.id_logement = $idLogement") as $row) {
                                        $total += $row['prix_charge_ht'];
                                    }

                                    $prixmin = -1;
                        
                                    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._jour ON _logement.id_logement = _jour.id_logement WHERE _logement.id_logement = $idLogement") as $row) {
                                        if ($prixmin == -1) {
                                            $prixmin = $row['tarif_nuit_ht'];
                                        }
                                        else if ($row['tarif_nuit_ht'] < $prixmin){
                                            $prixmin = $row['tarif_nuit_ht'];
                                        }
                                    }
                                     ?>                                        
                                    <h1><?php echo $total + $prixmin. "€";?></h1>
                                    
                                    
                                        
                                </div>

                                <div class="amenagements-container">
                                    <div class="menu-deroulant-liste-logement">
                                        <!-- Affiche les aménagements disponible dans le logement -->
                                        <?php
                                        foreach ($dbh->query("SELECT * FROM SAE._contient") as $amenagement) {
                                            if ($amenagement['id_logement'] == $row['id_logement']) {
                                                // Utilisez la structure de contrôle switch pour déterminer quelle icône afficher
                                                switch ($amenagement['nom_amenagement']) {
                                                    case "Jardin":
                                                        $iconClass = "fa-tree";
                                                        break;
                                                    case "Balcon":
                                                        $iconClass = "fa-person-through-window";
                                                        break;
                                                    case "Terrasse":
                                                        $iconClass = "fa-table";
                                                        break;
                                                    case "Parking prive":
                                                        $iconClass = "fa-square-parking";
                                                        break;
                                                    case "Parking public":
                                                        $iconClass = "fa-square-parking";
                                                        break;
                                                    
                                                    // Ajoutez d'autres cas ici en fonction de vos installations
                                                    default:
                                                        $iconClass = "fa-question"; // Icône par défaut pour les cas non traités
                                                        break;
                                                }
                                                ?>
                                                <ul>
                                                    <li>
                                                        <div class="option-logement">
                                                            <p><i class="fa-solid <?php echo $iconClass; ?>"></i><?php echo " " . $amenagement['nom_amenagement'] ?></p>
                                                            
                                                        </div>
                                                    </li>
                                                    <?php
                                            }
                                        }
                                        ?>

                                        <!-- Affiche les aménagements disponible dans le logement -->
                                        <?php
                                        foreach ($dbh->query("SELECT * FROM SAE._possede") as $installation) {
                                            if ($installation['id_logement'] == $row['id_logement']) {
                                                // Utilisez la structure de contrôle switch pour déterminer quelle icône afficher
                                                switch ($installation['nom_installation']) {
                                                    case "Climatisation":
                                                        $iconClass = "fa-wind";
                                                        break;
                                                    case "Piscine":
                                                        $iconClass = "fa-water-ladder";
                                                        break;
                                                    case "Jacuzzi":
                                                        $iconClass = "fa-hot-tub-person";
                                                        break;
                                                    case "Hammam":
                                                        $iconClass = "fa-temperature-three-quarters";
                                                        break;
                                                    case "Sauna":
                                                        $iconClass = "fa-bath";
                                                        break;
                                                    
                                                    // Ajoutez d'autres cas ici en fonction de vos installations
                                                    default:
                                                        $iconClass = "fa-question"; // Icône par défaut pour les cas non traités
                                                        break;
                                                }
                                                ?>
                                                    <li>
                                                        <div class="option-logement">
                                                            <p><i class="fa-solid <?php echo $iconClass; ?>"></i><?php echo " " . $installation['nom_installation'] ?></p>   
                                                        </div>
                                                    </li>
                                                <?php
                                            }
                                        }
                                        ?>

                                        <!-- Affiche les aménagements disponible dans le logement -->
                                        <?php
                                        foreach ($dbh->query("SELECT * FROM SAE._equipe") as $installation) {
                                            if ($installation['id_logement'] == $row['id_logement']) {
                                                // Utilisez la structure de contrôle switch pour déterminer quelle icône afficher
                                                switch ($installation['nom_equipement']) {
                                                    case "Television":
                                                        $iconClass = "fa-tv";
                                                        break;
                                                    case "Lave-linge":
                                                        $iconClass = "fa-soap";
                                                        break;
                                                    case "Barbecue":
                                                        $iconClass = "fa-drumstick-bite";
                                                        break;
                                                    case "Wifi":
                                                        $iconClass = "fa-wifi";
                                                        break;
                                                    case "Seche-linge":
                                                        $iconClass = "fa-shirt";
                                                        break;
                                                    case "Lave-vaisselle":
                                                        $iconClass = "fa-utensils";
                                                        break;
                                                    
                                                    // Ajoutez d'autres cas ici en fonction de vos installations
                                                    default:
                                                        $iconClass = "fa-question"; // Icône par défaut pour les cas non traités
                                                        break;
                                                }
                                                ?>
                                                    <li>
                                                        <div class="option-logement">
                                                            <p><i class="fa-solid <?php echo $iconClass; ?>"></i><?php echo " " . $installation['nom_equipement'] ?></p>    
                                                        </div>
                                                    </li>
                                                <?php
                                            }
                                        }
                                        ?></ul>
                                    </div>
                                </div>
                            </a>
                    
                        <div class="card card-logement BoutonModifierAnnuler marge">

                            <!-- Bouton pour modifier le logement -->
                            <form action="modifierLogement.php?idLogement=<?php echo $row['id_logement']; ?>" method="post">
                                <input type="submit" value="Modifier le logement" id="ModifierLogement">
                            </form>

                            <!-- Formulaire pour modidifier le calendrier -->
                            <form action="/calendrier_modif.php?idLogement=<?php echo $row['id_logement'] ?>" method="post">
                                <input type="submit" id="submit" name="submit" value="Modifier le calendrier">
                            </form>

                            <?php
                                $actif = $dbh->prepare("SELECT est_actif FROM SAE._logement WHERE id_logement = :id_logement");
                                $actif->bindParam(':id_logement', $row['id_logement']);
                                $actif->execute();
                                $actif = $actif->fetch(PDO::FETCH_ASSOC);
                                
                            if ($actif['est_actif']) { ?>
                                <!-- Bouton pour désactiver le logement -->
                            <form action="desactiverLogements.php?idLogement=<?php echo $row['id_logement']; ?>" method="post">
                                <input type="submit" value="Désactiver le logement" id="DesactiverLogement">
                            </form>
                            <?php } else { ?>
                                <!-- Bouton pour désactiver le logement -->
                                <form action="activerLogement.php?idLogement=<?php echo $row['id_logement']; ?>" method="post">
                                    <input type="submit" value="Activer le logement" id="ActiverLogement">
                                </form>
                            <?php } ?>
                            

                            
                        </div>
                    </section>
                <?php
                }
                ?>
            </div>

            <?php if ($nblogement > 2) { ?>
                <div class="background_svg bs_bottom">
                <div class="back_bottom_svg ">
                    <svg class="bottom_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z" fill="#FFB74C"/>
                    </svg>

                    <svg class="bottom_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.5" d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z" fill="#FFB74C"/>
                    </svg>

                    <svg class="bottom_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.3" d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z" fill="#FFB74C"/>
                    </svg>

                    <svg class="bottom_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.2" d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z" fill="#FFB74C"/>
                    </svg>

                    <svg class="bottom_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.1" d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z" fill="#FFB74C"/>
                    </svg>
                </div>     
            </div>
            <?php }   ?>
            
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

        <script>
            function confirmerSuppression() {
                // Affiche une boîte de dialogue pour confirmer la suppression
                var confirmation = confirm("Etes-vous sûr de vouloir supprimer votre logement ?");

                // Si l'utilisateur a confirmé, redirige vers supprimer.php
                if (confirmation) {
                    window.location.href = "/supprimerLogement.php?id_logement=<?php echo $row['id_logement'] ?>";
                } else {
                    // Si l'utilisateur a annulé, redirige vers mesLogements.php
                    window.location.href = "/mesLogements.php";
                }

                // Empêche l'envoi du formulaire par défaut
                return false;
            }
        </script>
    </body>
    <script src="https://kit.fontawesome.com/1d8b63688b.js" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</html>
