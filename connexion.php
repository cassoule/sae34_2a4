<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Si jamais l'utilisateur se connecte sur la page et qu'il ne sait pas connecté alors on lui donne l'idUtilisateur 1 qui correspond au compte visiteur
if (!isset($_SESSION['idUtilisateur'])) {
    $_SESSION['idUtilisateur'] = 1;
}

// Si l'utilisateur à valider la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifiant = $_POST['identifiant'];
    $password = $_POST['password'];
    $estPresent = false;

    // Vérification sur la connexion sur la base de données
    foreach ($dbh->query("SELECT * FROM SAE._compte") as $row) {
        // Si l'identifiant/compte existe et que le mot de passe correspond au compte alors on le connecte e on envoie l'utilisateur sur la page d'accueil
        if ((($row['email'] == $identifiant) || ($row['pseudo'] == $identifiant) || ($row['telephone'] == $identifiant)) && ($row['mot_de_passe'] == $password)) {
            $_SESSION['idUtilisateur'] = $row['id_compte'];

            // Renvoies vers la page d'accueil du site et arrête le script de la page
            header('Location: /index.php');
            exit();
        }
        // Si l'identifiant/compte existe mais que le mot de passe est incorrect
        else if ((($row['email'] == $identifiant) || ($row['pseudo'] == $identifiant) || ($row['telephone'] == $identifiant)) && ($row['mot_de_passe'] != $password)) {
            $estPresent = true;
            header('Location: /connexion.php?erreurMdp=1');
            exit();
        }
    }
    
    // Si l'identifiant/compte n'existe pas
    if ($estPresent == false) {
        header('Location: /connexion.php?erreurId=1');
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
        <title>Formulaire de connexion</title>
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
            <div class="logInfo">
                <a class="white-rectangle" href="/creerCompte.php">S'enregistrer</a>
                <a class="blue-rectangle" href="/connexion.php">Se connecter</a>
            </div>
        </header>

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
        <main class="top-svg-main">
            <div class="big-card">
                <!-- Formulaire de connexion au site -->
                <form class="connexion-container connexion" action="connexion.php" method="post">
                    <label for="identifiant" class="label"> Email, téléphone, nom d'utilisateur </label>
                    <div class="row" style="display:flex;">
                        <input type="text" id="identifiant" name="identifiant" required><br>
                    </div>
                    <label for="password" class="label"> Mot de passe </label>
                    <input type="password" id="password" name="password" required>
                    
                    <a href="#" class="forgot-password underline" onclick="recuperationMdpPopUp()">Mot de passe oublié ?</a>

                    <input type="submit" value="Se connecter">
                </form>
                <a class="underline" href="/creerCompte.php" class="register-link">Je n'ai pas de compte</a>
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

    <!-- Script JS pour récupéré le mot de passe -->
    <script>
        function recuperationMdpPopUp() {
            var email = prompt("Veuillez entrer votre adresse e-mail pour récupérer votre mot de passse :", "");

            if (email != null && email != "") {
                // Envoyer l'e-mail avec l'adresse spécifiée
                window.location.href = "/mdpOublie.php?email=" + email;
            }
        }
    </script>    
</html>

<?php
// Pop-up dans le cas où le mot de passe entré est faux
if (isset($_GET['erreurMdp'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le mot de passe est incorrect !");
        }, 500);
        </script>
<?php
}

// Pop-up dans le cas où l'identifiant ou le mot de passe de l'utilisateur est faux
if (isset($_GET['erreurId'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le compte ou le mot de passe est incorrect !");
        }, 500);
    </script>
<?php
}

// Pop-up dans l'utilisateur a entré une adresse mail valide et qu'il veut récupéré son mot de passe
if (isset($_GET['recupMdp'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Voici votre mot de passe : <?php echo $_GET['recupMdp'] ?>");
        }, 500);
        </script>
<?php
}

// Pop-up dans le cas où l'utilisateur a rentré une adresse mail invalide et qu'il veut récupéré son mot de passe
if (isset($_GET['erreurRecup'])) {
?> 
    <script>
        setTimeout(function() {
            alert("L'adresse mail n'existe pas !");
        }, 500);
        </script>
<?php
}
?>