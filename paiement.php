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

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$idDevis = $_GET['id_devis'];

// Si l'utilisateur à valider la soumission du formulaire
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['nomProprietaire']))  && (isset($_POST['cryptogramme'])) && (isset($_POST['numeroCarte'])) && (isset($_POST['dateExpiration']))) {
    $nom = $_POST['nomProprietaire'];
    $crypto = $_POST['cryptogramme'];
    $carte = $_POST['numeroCarte'];
    $expiration = $_POST['dateExpiration'];

    $marche = true;

    $condition = $_POST['condition'][0];

    // Valider le numéro de carte
    if (!preg_match('/^\d{16}$/', $carte)) {
        $marche = false;
        echo "Le numéro de carte n'est pas valide.";
    }
    // Valider le cryptogramme visuel
    if (!preg_match('/^\d{3}$/', $crypto)) {
        $marche = false;
        echo "Le cryptogramme visuel n'est pas valide.";
    }
    // Valider la date d'expiration
    if (preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiration, $matches)) {
        $moisExpiration = $matches[1];
        $anneeExpiration = $matches[2];

        // Convertir la date d'expiration en timestamp
        $expirationTimestamp = strtotime("20$anneeExpiration-$moisExpiration-01");

        // Obtenir la date actuelle
        $dateActuelle = time();

        // Vérifier si la date d'expiration est ultérieure à la date actuelle
        if ($expirationTimestamp <= $dateActuelle) {
            $marche = false;
            echo "La date d'expiration de la carte est invalide.";
        }
    }

    // Si chaque condition de vérification des informations est validé alors on peux enregistrer le paiement
    if ($marche == true) {
        $estPresent = false;
        foreach ($dbh->query("SELECT * FROM SAE._paiement") as $paiement) {
            if (($paiement['numero_carte'] == $carte) && ($paiement['cryptogramme'] == $crypto)) {
                $estPresent = true;
            }
        }

        // Si le moyen de paiement utilisé n'est pas présent dans la base de données
        if ($estPresent == false) {
            // Permet d'enregistrer le nouveau moyen de paiement dans la base de données
            $stmt = $dbh->prepare("INSERT INTO SAE._paiement (numero_carte, date_validite, cryptogramme) VALUES (:carte, :expiration, :crypto)");
            $stmt->bindParam(':carte', $carte, PDO::PARAM_STR);
            $stmt->bindParam(':expiration', $expiration, PDO::PARAM_STR);
            $stmt->bindParam(':crypto', $crypto, PDO::PARAM_STR);
            $stmt->execute();
        }

        // Si le moyen de paiement existe déjà dans la base de données afin d'éviter le surplus d'informations
        foreach($dbh->query("SELECT * FROM SAE._paiement") as $paiement) {
            if (($paiement['numero_carte'] == $carte) && ($paiement['cryptogramme'] == $crypto)) {
                $idPaiement = $paiement['id_paiement'];
            }
        }
        // Renvoies vers la page de création de la réservation et arrête le script de la page
        header("Location: https://site-sae-sixpetitscochons.bigpapoo.com/creerReservation.php?acceptation_cgv=$condition&est_paye=true&id_message_devis=$idDevis&id_paiement=$idPaiement");
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
        <title>Page de paiement</title>
        <link rel="stylesheet" href="style/style_Manon.css">
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

        <!-- Affichage des informations principales de la page -->
        <main class="bottom-svg-main">
            <div class="big-card">
                <!--- Formulaire pour le paiement --->
            <form action="https://site-sae-sixpetitscochons.bigpapoo.com/paiement.php?id_devis=<?php echo $_GET['id_devis'] ?>&id_logement=<?php echo $_GET['id_logement'] ?>" method="post">
                <div class="row">
                    <input type="text" placeholder="Nom" name="nomProprietaire" required><br><br>
                </div>

                <div class="row">
                    <input type="text" placeholder="Numéro de carte" name="numeroCarte" required><br><br>
                </div>
                
                <div class="row-Carte">
                    <input type="text" placeholder="Date d'expiration" name="dateExpiration" required>
                    <input type="text" class ="crypto" placeholder="Cryptograme visuel" name="cryptogramme" required><br><br>
                </div>

                <div class="row">
                    <label for="conditionUtilisation">Accepter vous les conditions d'utilisation</label>
                    <input type="checkbox" id="conditionUtilisation" name="condition[]" value="true" />
                </div>

                <div class="row-paiement">
                    <input type="submit" value="Effectuer le paiement">
                </div>
            </div>
            </form>

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
