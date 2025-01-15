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
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

// Chargement de la base de données
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Si jamais l'utilisateur se connecte sur la page et qu'il ne sait pas connecté alors on lui donne l'idUtilisateur 1 qui correspond au compte visiteur
if (!isset($_SESSION['idUtilisateur'])) {
    $_SESSION['idUtilisateur'] = 1;
}

// Si l'utilisateur fait une recherche de logement
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "La fonction n'a pas encore été implémenté";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Logements</title>
    <link rel="stylesheet" href="style/style.css">
    <script src="https://kit.fontawesome.com/1d8b63688b.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

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
        <a href="/consulterSonCompte.php">Mon Compte</a>
        <?php
        if ($proprietaire=="Propriétaire") {
        ?>
            <a href="/mesLogements.php">Mes logements</a>
            <a href="/mesReservationsProprietaires.php">Mes réservations</a>
        <?php
        }else{
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

    <div class="background_svg bs_top">
        <div class="back_top_svg">
            <svg class="top_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z" fill="#0777DE" />
            </svg>

            <svg class="top_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.5" d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z" fill="#0777DE" />
            </svg>

            <svg class="top_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3" d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z" fill="#0777DE" />
            </svg>

            <svg class="top_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.2" d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z" fill="#0777DE" />
            </svg>

            <svg class="top_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.1" d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z" fill="#0777DE" />
            </svg>
        </div>
    </div>



    <!-- Affichage des informations principales de la page -->
    <main class="both-svg-main connected-main">
        <!-- Formulaire pour faire une recherche de logement -->
        <div class="container-filtre">
            <div class="card-filtre">
                <div class="filtre" id="filtreForm">
                    <div class="recherche-filtre">
                        <input id="destination" type="text" name="filtre" placeholder="Destination, lieu...">
                        <input id="date-arrive" type="text" name="date" placeholder="Date d'arrivée" >
                        <input id="date-depart" type="text" name="date-depart" placeholder="Date de départ">
                    </div>
                    <input type="button" value="Filtres" onclick="MontrerMasquefiltre()">
                    <input type="button" value="Rechercher" onclick="filtre_lieu()">
                </div>
            </div>
            <div class="card-filtre-plus hidden">
                <form class="filtre" action="/listeLogement.php" method="post">
                    <div class="ajout-card-filtres">
                        <div class="row-filtres">
                            <div class="card-ajout-filtres" onclick="toggleCheckbox('jardinCheckbox', this)">
                                <label for="Jardin" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-tree"></i> Jardin</label>
                                <input class="checkbox" type="checkbox" name="Jardin" id="jardinCheckbox" value="1">
                            </div>

                            <div class="card-ajout-filtres" onclick="toggleCheckbox('parking_publicCheckbox', this)">
                                <label for="Climatisation" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-wind"></i> Climatisation</label>
                                <input class="checkbox" type="checkbox" name="Climatisation" id="parking_publicCheckbox" value="1">
                            </div>

                            <div class="card-ajout-filtres" onclick="toggleCheckbox('parking_publicCheckbox', this)">
                                <label for="Piscine" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-water-ladder"></i> Piscine</label>
                                <input class="checkbox" type="checkbox" name="Piscine" id="parking_publicCheckbox" value="1">
                            </div>


                            <div class="card-ajout-filtres" onclick="toggleCheckbox('parking_publicCheckbox', this)">
                                <label for="Wifi" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-wifi"></i> Wifi</label>
                                <input class="checkbox" type="checkbox" name="Wifi" id="parking_publicCheckbox" value="1">
                            </div>

                            <div class="card-ajout-filtres" onclick="toggleCheckbox('parking_publicCheckbox', this)">
                                <label for="Barbecue" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-drumstick-bite"></i> Barbecue</label>
                                <input class="checkbox" type="checkbox" name="Barbecue" id="parking_publicCheckbox" value="1">
                            </div>

                            <div class="card-ajout-filtres-prix" onclick="toggleCheckbox('prixCheckbox', this)">
                                <label id="Prix_C" for="Prix_C" onselectstart="return false;" ondblclick="preventLabelSelection()"> Prix Croissant</label>
                                <input type="checkbox" name="Prix_C" id="prixCheckbox" value="1">
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Permet de parcourir la liste de tous les logements existant -->
        <div id="conteneur">
            <?php
            $tousLogement = array();
            foreach ($dbh->query("SELECT * FROM SAE._logement natural join SAE._adresse where est_actif = true") as $row) {
                $amenagements = [];
                $idLogementFiltres = $row['id_logement'];

                foreach ($dbh->query("SELECT nom_amenagement FROM SAE._contient WHERE id_logement = $idLogementFiltres") as $amenagement) {
                    $amenagements[] = $amenagement;
                }
                foreach ($dbh->query("SELECT nom_installation FROM SAE._possede WHERE id_logement = $idLogementFiltres") as $installation) {
                    $amenagements[] = $installation;
                }
                foreach ($dbh->query("SELECT nom_equipement FROM SAE._equipe WHERE id_logement = $idLogementFiltres") as $equipement) {
                    $amenagements[] = $equipement;
                }
                foreach ($dbh->query("SELECT * FROM SAE._jour WHERE id_logement = $idLogementFiltres") as $jour) {
                    $amenagements[] = $jour;
                }

                $valSansCle = array_map('array_values', $amenagements);
                $val = call_user_func_array('array_merge', $valSansCle);

                $tousLogement[] = array_merge($row, $val);

                $tousLogementJSON = json_encode($tousLogement);

                /* recupere le prix du jour d'un logement*/
                $dateDuJour = date('Y-m-d');
                foreach ($dbh->query("SELECT tarif_nuit_ht FROM SAE._jour inner join SAE._logement on _jour.id_logement = _logement.id_logement WHERE _logement.id_logement = $idLogementFiltres and date_jour = '$dateDuJour'") as $prix) {
                }
            ?>
                <div id="<?php echo $idLogementFiltres ?>" data-nombre="<?php echo $prix['tarif_nuit_ht'] ?>" class="container-liste-logement">
                    <!-- Affiche chaque image lié à un logement -->
                    <?php
                    foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                        if ($image['id_logement_image'] == $row['id_logement']) { ?>
                            <div class="card-liste-logements">
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
                        <div class="infos-logement"> <!-- tout sauf bouton -->
                            <div class="tout-sauf-amenagement">

                                <!-- Informations du logement -->



                                <!-- Affiche la photo de profil du propriétaire ainsi que son nom et son prénom -->
                                <div class="proprietaire-liste-logement">
                                    <?php
                                    foreach ($dbh->query("SELECT * FROM SAE._compte") as $compte) {
                                        if ($compte['id_compte'] == $row['id_proprietaire']) {
                                            foreach ($dbh->query("SELECT * FROM SAE._image WHERE id_compte = " . $compte['id_compte']) as $img) { ?>

                                                <img src="<?php echo $img['lien_image'] ?>" alt="Photo de profil propriétaire" height="85" width="85">
                                            <?php
                                            } ?>
                                            <h3><?php echo $compte['prenom'] . " " . $compte['nom'][0] . "." ?></h3>
                                    <?php

                                        }
                                    }
                                    ?>
                                </div>

                                <!-- Affiche la description du logement -->
                                <div class="description-liste-logement">
                                    <h3>Description</h3>
                                    <p><?php echo $row['description_detaille'] ?></p>
                                </div>
                                <h1 class="prix_filtres">à partir de <?php echo $prix['tarif_nuit_ht'] ?>€</h1>
                            </div>


                            <!-- Affiche les charges comprises dans le logement -->
                            <?php
                            /*
                        foreach ($dbh->query("SELECT * FROM SAE._charge") as $charge) {
                            if ($charge['id_logement'] == $row['id_logement']) {
                        ?>
                                <h4><?php echo $charge['nom_charge'] ?></h4>
                        <?php
                            }
                        }
                        */ ?>
                            <div class="amenagement-liste-logement">
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

                                                default:
                                                    $iconClass = "fa-question"; 
                                                    break;
                                            }
                                            if ($amenagement["nom_amenagement"] != "Balcon" && $amenagement["nom_amenagement"] != "Parking prive" && $amenagement["nom_amenagement"] != "Parking public" && $amenagement["nom_amenagement"] != "Terrasse") {

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
                                    }
                                        ?>

                                        <!-- Affiche les aménagements disponible dans le logement -->
                                        <?php
                                        foreach ($dbh->query("SELECT * FROM SAE._possede") as $installation) {
                                            if ($installation['id_logement'] == $row['id_logement']) {
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
                                                    default:
                                                        $iconClass = "fa-question"; 
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
                                                    default:
                                                        $iconClass = "fa-question"; 
                                                        break;
                                                }
                                    
                                                if ($installation["nom_equipement"] != "Television" && $installation["nom_equipement"] != "Lave-linge" && $installation["nom_equipement"] != "Seche-linge" && $installation["nom_equipement"] != "Lave-vaisselle") {
                                                ?>
                                                    <li>
                                                        <div class="option-logement">
                                                            <p><i class="fa-solid <?php echo $iconClass; ?>"></i><?php echo " " . $installation['nom_equipement'] ?></p>
                                                        </div>
                                                    </li>

                                                <?php
                                                }
                                            }
                                        }
                                        ?>
                                            </ul><?php
                                                    /*?>

                                <!-- Affiche les services présent dans le logement -->
                                <?php
                                foreach ($dbh->query("SELECT * FROM SAE._service") as $service) {
                                    if ($service['id_logement'] == $row['id_logement']) {
                                ?>
                                        <h4><?php echo $service['nom_service'] ?></h4>
                                <?php
                                    }
                                }
                                */ ?>
                                </div>
                            </div>
                        </div>
                        <!-- Lien vers la page avec les détails du logement -->
                        <div id="form">
                            <a id="submit-liste-logement" href="/logementDetail.php?idLogement=<?php echo $row['id_logement']; ?>">Détails logement</a>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <div class="background_svg bs_bottom">
                <div class="back_bottom_svg ">
                    <svg class="bottom_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z" fill="#FFB74C" />
                    </svg>

                    <svg class="bottom_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.5" d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z" fill="#FFB74C" />
                    </svg>

                    <svg class="bottom_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.3" d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z" fill="#FFB74C" />
                    </svg>

                    <svg class="bottom_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.2" d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z" fill="#FFB74C" />
                    </svg>

                    <svg class="bottom_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.1" d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z" fill="#FFB74C" />
                    </svg>
                </div>
            </div>
        </div>
    </main>

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
                <a href="#">Fonctionnement du site AlHaIZH Breizh</a>
                <a href="#">Politique de confidentialité</a>
                <a href="#">Conditions</a>
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
<script>
    function toggleCheckbox(checkboxId, element) {
        var filtres = document.getElementsByClassName("card-ajout-filtres");

        var checkbox = document.getElementById(checkboxId);
        checkbox.checked = !checkbox.checked;
        // Ajoute ou supprime la classe "selected" en fonction de l'état de la case à cocher
        element.classList.toggle('selected', checkbox.checked);



        var divs = document.getElementsByClassName("container-liste-logement");
        console.log("div", divs);

        var nom = element.querySelector('input').name;
        if (nom == "Prix_C") {
            console.log("Prix_C");

            triPrix(divs, element);
        } else {
            var tousLogements = <?php echo $tousLogementJSON; ?>;
            console.log(checkboxId);
            console.log(tousLogements);
            console.log(element);

            var tab = [];
            var filtreSelected = []
            for (filtre of filtres) {
                var nomSelect = filtre.querySelector('.checkbox').name;
                console.log("ffffff", filtre);

                if (filtre.classList.length == 2) {
                    filtreSelected.push(nomSelect)
                }
            }
            console.log("aaaa", filtreSelected);

            for (var cle1 in tousLogements) {
                var cpt = 0;
                console.log("cle1", cle1);
                console.log("tous", );


                for (var cle2 in tousLogements[cle1]) {
                    for (var cle3 of filtreSelected) {
                        console.log("cpt", cpt);
                        if (tousLogements[cle1][cle2] == cle3) {
                            console.log("log", tousLogements[cle1][cle2]);
                            console.log("cle3", cle3);
                            cpt++;

                            if (cpt == filtreSelected.length) {
                                tab.push(tousLogements[cle1].id_logement);
                                cpt = 0;

                            }
                        }


                    }

                }
            }
            console.log(filtreSelected);
            if (filtreSelected.length == 0) {
                for (var j = 0; j < divs.length; j++) {
                    idDiv = divs[j].id;
                    console.log(idDiv);
                    console.log(tab);
                    elem = document.getElementById(divs[j].id);
                    elem.style.display = 'flex';

                }
            }

            for (var j = 0; j < divs.length; j++) {
                idDiv = divs[j].id;
                console.log(idDiv);
                console.log("tab", tab);
                elem = document.getElementById(divs[j].id);
                if (!tab.includes(parseInt(idDiv))) {
                    console.log("tab", tab);
                    if (filtreSelected.length == 0) {
                        elem.style.display = 'flex';
                    } else {
                        elem.style.display = 'none';
                    }
                } else {
                    elem.style.display = 'flex';
                }
            }


        }





    }

    function preventLabelSelection() {
        return false;
    }




    function estSelectionne(divs) {
        for (var i = 0; i < divs.length; i++) {
            idDiv = divs[i].id;
            elem = document.getElementById(divs[i].id);
            if (elem.classList.contains('selected') == false) {
                elem.style.display = 'flex';

            }
        }
    }
    var ordreCroissant = false;

    function triPrix(divs) {
        // Convertir les éléments en tableau pour pouvoir utiliser la méthode sort
        var tableauElements = Array.from(divs);
        // Fonction de comparaison pour le tri
        function comparer(a, b) {
            var nombreA = parseInt(a.dataset.nombre);
            var nombreB = parseInt(b.dataset.nombre);
            console.log(nombreA)
            console.log("b", nombreB)

            if (ordreCroissant) {
                return nombreA - nombreB;
            } else {
                return nombreB - nombreA;
            }
        }
        // Inverser l'ordre pour le prochain clic
        ordreCroissant = !ordreCroissant;

        // Tri du tableau d'éléments
        tableauElements.sort(comparer);

        // Mettre à jour l'ordre dans le DOM
        var conteneur = document.getElementById('conteneur');
        tableauElements.forEach(function(element) {
            conteneur.appendChild(element);
        });
    }

    // Lier la fonction de tri à l'événement change de la checkbox
    var checkboxPrixC = document.getElementById('Prix_C');
    var a = document.querySelectorAll('.card-ajout-filtres input[name="Prix_C"]');
    var PrixCNom = checkboxPrixC.querySelector('input');

    checkboxPrixC.addEventListener('change', function() {
        if (checkboxPrixC.checked) {
            triPrix();
        } else {
            // Réinitialiser l'ordre croissant si la checkbox est désactivée
            ordreCroissant = true;
        }
    });

    function MontrerMasquefiltre() {
        var card = document.getElementsByClassName("card-filtre-plus");

        if (card[0].classList.contains("hidden")) {
            card[0].classList.remove("hidden")
        } else {
            card[0].classList.add("hidden")
        }
    }
    
    document.getElementById("date-depart").addEventListener("click", function() {
        this.type = "date";
        this.focus();
    });
    document.getElementById("date-depart").addEventListener("blur", function() {
        if (this.value === "") {
            this.type = "text";
        }
    });
    document.getElementById("date-arrive").addEventListener("click", function() {
        this.type = "date";
        this.focus();
    });
    document.getElementById("date-arrive").addEventListener("blur", function() {
        if (this.value === "") {
            this.type = "text";
        }
    });


    function filtre_lieu() {

        if (document.getElementById("destination").value != "") {
            var divs = document.getElementsByClassName("container-liste-logement");
            var tousLogements = <?php echo $tousLogementJSON; ?>;
            var tableauElements = Array.from(divs);
            var tab = [];

            for (var cle1 in tousLogements) {
                console.log("cle1", cle1);
                console.log("tous", tousLogements);
                if (document.getElementById("destination").value.toLowerCase() == tousLogements[cle1].ville.toLowerCase()) {
                    tab.push(tousLogements[cle1].id_logement);
                }
            }

            for (var j = 0; j < divs.length; j++) {
                idDiv = divs[j].id;
                console.log(idDiv);
                console.log("tab", tab);
                elem = document.getElementById(divs[j].id);
                if (!tab.includes(parseInt(idDiv))) {
                    elem.style.display = 'none';
                } else {
                    elem.style.display = 'flex';
                }
            }


        }
    }

    // Fonction pour vérifier la disponibilité du logement
    function verifierDisponibilite() {
    const dateArrive = new Date(document.getElementById("date-arrive").value);
    const dateDepart = new Date(document.getElementById("date-depart").value);

    // Vérifie chaque jour entre la date d'arrivée et la date de départ
    for (let date = dateArrive; date <= dateDepart; date.setDate(date.getDate() + 1)) {
        const dateString = date.toISOString().split('T')[0];
        const jour = joursDisponibles.find(jour => jour.date_jour === dateString);
        if (!jour || !jour.disponible) {
            return false;
        }
    }
    return true;
    }

</script>

</html>