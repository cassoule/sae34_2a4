<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Permet de stocker l'idUtilisateur dans une variable
$idClient = $_SESSION['idUtilisateur'];

// Permet de récupérer le nom du client qui souhaite faire la réservation
$result = $dbh->query("SELECT nom from SAE._compte where id_compte=$idClient");
$row = $result->fetch(PDO::FETCH_ASSOC);
$nomClient = $row['nom'];

// Permet de récupérer le prénomdu client qui souhaite faire la réservation
$result = $dbh->query("SELECT prenom from SAE._compte where id_compte=$idClient");
$row = $result->fetch(PDO::FETCH_ASSOC);
$prenomClient = $row['prenom'];

$idLogement = $_GET['idLogement'];

// Si l'utilisateur à valider la soumission du formulaire de la page réservation
if ((isset($_POST['nom'])) && (isset($_POST['prenom'])) && (isset($_POST['nbPersonnes'])) && (isset($_POST['dateDebut'])) && (isset($_POST['dateFin']))) {
    // Informations de la réservation
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $nbPersonnes = $_POST['nbPersonnes'];
    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    
    $maxpersonne = $dbh->query("SELECT max_personnes FROM SAE._logement where id_logement = $idLogement");
    $maxpersonne = $maxpersonne->fetch(PDO::FETCH_ASSOC)['max_personnes'];

    $lien = "Location: /envoieMessageDemandeDevis.php?nom=$nom&prenom=$prenom&nbPersonnes=$nbPersonnes&dateDebut=$dateDebut&dateFin=$dateFin&idLogement=$idLogement";

    //ajout des services complementaires
    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._service ON _logement.id_logement = _service.id_logement where _logement.id_logement = $idLogement") as $row) {
        $cle = str_replace(" ", "_",[$row['nom_service']])[0];

        if (isset($_POST[$cle])) {
            $lien = $lien . "&". str_replace(" ", "_",[$row['nom_service']])[0] . "=1";
        }
        else {
            $lien = $lien . "&". str_replace(" ", "_",[$row['nom_service']])[0] . "=0";
        }
    }

    //ajout des charges suplémentaires
    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._charge ON _logement.id_logement = _charge.id_logement where _logement.id_logement = $idLogement") as $row) {
        $cle = str_replace(" ", "_",[$row['nom_charge']])[0];
        if (isset($_POST[$cle])) {
            $lien = $lien . "&". str_replace(" ", "_",[$row['nom_charge']])[0] . "=1";
        }
        else {
            $lien = $lien . "&". str_replace(" ", "_",[$row['nom_charge']])[0] . "=0";
        }
    }

    // Récupération de la date actuelle
    $dateActuelle = date("Y-m-d");
    $dateDebutObj = DateTime::createFromFormat('Y-m-d', $dateDebut);
    $dateFinObj = DateTime::createFromFormat('Y-m-d', $dateFin);
    $diff = date_diff($dateDebutObj, $dateFinObj);
    $joursDeDifference = $diff->days;

    //verification si un date est comprise entre deux date déjà occupé
    $pris = false;

    // Verification de la plage de disponibilite d'un logement
    foreach ($dbh->query("SELECT disponible FROM SAE._jour where _jour.id_logement = $idLogement AND date_jour BETWEEN '$dateDebut' AND '$dateFin'") as $row) {
        if ($row['disponible'] == false){
            header("Location: /reservation.php?idLogement=$idLogement&erreurDate=3");
            exit(); 
        }
    }

    // Permet de vérifier et gérer les erreurs
    if (0) {
        //changer base de donnée
        //($dateDebut <= $dateActuelle) || ($dateFin < $dateDebut)
        // Renvoies vers la page de réservation et arrête le script de la page avec une erreur
        header("Location: /reservation.php?idLogement=$idLogement&erreurDate=1");
        exit();
    } elseif (0) {
        //changer base de donnée
        //$joursDeDifference < $jourmin
        //si le nombre de jour minimum n'est pas respecté
        header("Location: /reservation.php?idLogement=$idLogement&erreurDate=2");
        exit();
    } elseif ($nbPersonnes > $maxpersonne) {
        //verifier si le nombre de personne est inferieur ou egale au nombre de personne max autorisé dans le logements
        header("Location: /reservation.php?idLogement=$idLogement&erreurPersonne=1");
        exit();
    } elseif (0) {
        //changer base de donnée
        //$pris
        //verifie si la date selectionné est disponible (pas pris par quelqu'un + pas enlever par le proprietaire)
        header("Location: /reservation.php?idLogement=$idLogement&erreurDate=3");
        exit();
    } else {
        // Renvoies vers la page d'envoie de message de devis et arrête le script de la page
        $stmtdate = $dbh->prepare("UPDATE SAE._jour SET disponible = false, raison = 'reserve' WHERE id_logement = $idLogement AND date_jour BETWEEN '$dateDebut' AND '$dateFin'");
        $stmtdate->execute();

        // Renvoies vers la page d'envoie de message de devis et arrête le script de la page
        header($lien);
        exit();
    }

}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" name="description" content="Description de votre page">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page de réservation</title>
        <link rel="stylesheet" type="text/css" href="style/style_thomas.css">
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
        
        <?php
        if($_SESSION['idUtilisateur'] == 1){
        ?>
            <div class="container_accueil">
                <p class="presentation">ALHaIZ Breizh c'est un site de locations de vacances entre particulier en Bretagne</p>
                <img src="img/Phare-de-Pontusval.jpg" alt="">
            </div>

            <div class="background_svg bs_accueil">
                <div class="back_top_svg_accueil">
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
        <?php
        }
        ?>

        <!-- Affichage des informations principales de la page -->
        <main class="bottom-svg-main connected-main">
        <div class="container" onclick="exit-overlay()">
            <!-- Formulaire pour la création d'une réservation -->
            <form action="/reservation.php?idLogement=<?php echo $_GET['idLogement'] ?>" method="post" enctype="multipart/form-data" >
                <!-- Permet de rentrer les informations obligatoire pour la réservation -->
                <div>
                    <input class="nomprenom" placeholder="Nom" type="text" id="nom" name="nom" value="<?php echo $nomClient ?>" required />
                    <br />  <br />

                    <input class="nomprenom" type="text" placeholder="Prenom" id="prenom" name="prenom" value="<?php echo $prenomClient ?>" required />
                    <br /> <br />

                    <label for="nbPersonnes">Nombre personnes :</label>
                    <input type="number" id="nbPersonnes" name="nbPersonnes" value="1" min="1" required />
                    <br/> <br/>
                    
                    <label for="dateDebut">Date de début de réservation :</label>
                    <input type="date" id="dateDebut" name="dateDebut" required />
                    <br/> <br/>
                    <label for="dateFin">Date de fin de réservation :</label>
                    <input type="date" id="dateFin" name="dateFin" required />

                </div>
                <!-- Permet de rentrer les informations optionelles pour la réservation -->
                <div>
                    <h6>Séléctionner vos service complémentaires</h6>
                    <?php
                    //service complementaires
                    //print_r($idLogement);
                    //$result = $dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._offre ON _logement.id_logement = _offre.id_logement INNER JOIN SAE._service ON _offre.nom_service = _service.nom_service where _logement.id_logement = $idLogement");
                    $row = $result->fetch(PDO::FETCH_ASSOC);
                    //print_r($row);
                    foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._service ON _logement.id_logement = _service.id_logement where _logement.id_logement = $idLogement") as $row) {
                        //print_r($row); ?> 
                        <label for=<?php echo $row['nom_service'] ?>><?php echo $row['nom_service']." (".$row['prix_service_ht']."€ )" ?></label>
                        <input type="checkbox" id="<?php echo $row['nom_service'] ?>" name="<?php echo $row['nom_service'] ?>" value="true" />
                        <br> <?php
                    }
                    ?>
                </div>
                <div >
                <h6>Séléctionner les charges additionnelles</h6>
                    <?php
                        //charge additionnelles
                        foreach ($dbh->query("SELECT * FROM SAE._logement INNER JOIN SAE._charge ON _logement.id_logement = _charge.id_logement where _logement.id_logement = $idLogement") as $row) {
                            ?> 
                            <label for=<?php echo $row['nom_charge'] ?>><?php echo $row['nom_charge']." (".$row['prix_charge_ht']."€ )" ?></label>
                            <input type="checkbox" id="<?php echo $row['nom_charge'] ?>" name="<?php echo $row['nom_charge'] ?>" value="true" />
                            <br> <?php
                        }
                    ?>
                </div>
                <br>
                <div class="bouton">
                    <input type="reset" name="Annuler" value="Annuler" onclick="return confirm('Etes vous sur de vouloir annuler ?')"/>
                    <input class="valide" type="submit" value="Envoyer" onclick="return confirm('Etes vous sur de vouloir envoyer ce devis ?')"/>
                </div>
            </form>

        </div>
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
</html>

<?php
if (isset($_GET["erreurDate"])) {
    if ($_GET["erreurDate"] == 1) {
        ?> <script>setTimeout(function() {
            alert("Les dates ne sont pas cohérentes. Veuillez réessayer.");
        }, 500);</script> <?php
    } elseif ($_GET["erreurDate"] == 2) {
        ?> <script>setTimeout(function() {
            alert("Le nombre de jour minimum du logements n'est pas respecté.");
        }, 500);</script>  <?php
    } elseif ($_GET["erreurDate"] == 3) {
        ?> <script>setTimeout(function() {
            alert("Les dates séléctionnées sont déjà prise par quelqu'un ou ont été enlevé par le propriétaire");
        }, 500);</script>  <?php
    }
}

if (isset($_GET["erreurPersonne"])) {
    if ($_GET["erreurPersonne"] == 1) {
        ?> <script>setTimeout(function() {
            alert("Le nombre de personne rentré dépasse le nombre de personne max autorisé dans le logements.");
        }, 500);</script>  <?php
    }
}
?>

<script src="https://kit.fontawesome.com/1d8b63688b.js" crossorigin="anonymous"></script>
<script src="script.js"></script>