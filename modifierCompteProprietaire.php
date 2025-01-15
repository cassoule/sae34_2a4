<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

// Chargement de la base de données
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$stmtCompte = $dbh->prepare("SELECT * FROM SAE._compte WHERE id_compte = :id_compte");
$stmtCompte->bindParam(':id_compte', $_SESSION['idUtilisateur']);
$stmtCompte->execute();
$compteActuel = $stmtCompte->fetch(PDO::FETCH_ASSOC);

$stmtAdresse = $dbh->prepare("SELECT * FROM SAE._adresse WHERE id_adresse = :id_adresse");
$stmtAdresse->bindParam(':id_adresse', $compteActuel['id_adresse']);
$stmtAdresse->execute();
$adresse = $stmtAdresse->fetch(PDO::FETCH_ASSOC);

$stmtProprietaire = $dbh->prepare("SELECT * FROM SAE._proprietaire WHERE id_proprietaire = :id_proprietaire");
$stmtProprietaire->bindParam(':id_proprietaire', $_SESSION['idUtilisateur']);
$stmtProprietaire->execute();
$proprietaireActuel = $stmtProprietaire->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cp'])) {
    // Informations liées à la création d'un compte propriétaire
    $cp = $_POST['cp'];
    $adresse = $_POST['adresse'];
    $complement_adresse = $_POST['complement_adresse'];
    $ville = $_POST['ville'];

    $conditions = $_POST['cgu'];
    $nomBanque = $_POST['nomBanque'];
    $codeBanque = $_POST['codeBanque'];
    $codeGuichet = $_POST['codeGuichet'];
    $numCompte = $_POST['numCompte'];
    $cleRIB = $_POST['cleRIB'];
    $iban = $_POST['iban'];
    $bic = $_POST['bic'];

    $pseudo = $_POST['pseudo'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $civilite = $_POST['civilite'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $mdp = $_POST['password'];

    // Si l'utilisateur a donné 2 mot de passe différents
    if ($mdp != $_POST['mdp']) {
        header('Location: /formProprietaire.php?erreurMdp=1');
        exit();
    }
    // Si le code postal est incorrect
    else if (is_numeric($cp)&&strlen($cp) !== 5) {
        header('Location: /formProprietaire.php?erreurCodePostal=1');
        exit();
    }
    // Si le numéro de téléphone est incorrect
    else if (!preg_match('/^[0-9]{10}$/',$tel)) {
        header('Location: /formProprietaire.php?erreurTelephone=1');
        exit();
    }
    // Si la clé RIB n'est pas bonne
    else if (is_numeric($cleRIB)&&strlen($cleRIB) !== 2) {
        header('Location: /formProprietaire.php?erreurRIB=1');
        exit();
    }
    // Si le code banque n'est pas valide
    else if (is_numeric($codeBanque)&&strlen($codeBanque) !== 5) {
        header('Location: /formProprietaire.php?erreurBanque=1');
        exit();
    }
    // Si le code guichet n'est pas valide
    else if (is_numeric($codeGuichet)&&strlen($codeGuichet) !== 5) {
        header('Location: /formProprietaire.php?erreurGuichet=1');
        exit();
    }
    // Si le numéro de compte n'est pas valide
    else if (is_numeric($numCompte)&&strlen($numCompte) !==11) {
        header('Location: /formProprietaire.php?erreurCompte=1');
        exit();
    }
    // Si l'IBAN n'est pas valide
    else if (!preg_match('/^FR[0-9]{25}$/',$iban)) {
        header('Location: /formProprietaire.php?erreurIBAN=1');
        exit();
    }
    // Si le BIC n'est pas valide
    /*else if (!preg_match('/^[A-Z0-9]{8-11}$/',$bic)) {
        header('Location: /formProprietaire.php?erreurBIC=1');
        exit();
    }*/
    // Si le mot de passe ne respecte pas les consignes de sécurité
    else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@$#%^&*()_+])[A-Za-z\d!@$#%^&*()_+]{12,}$/',$mdp)) {
        header('Location: /formProprietaire.php?erreurSecurite=1');
        exit();
    }
    // Si le pseudo exède les 20 caractères
    else if (strlen($pseudo) >= 20) {
        header('Location: /formProprietaire.php?erreurLongueurPseudo=1');
        exit();
    }
    // Si le nom possède pas que des lettres, espaces ou tirets
    else if (!preg_match('/^[A-Za-z\s\-]+$/',$nom)) {
        header('Location: /formProprietaire.php?erreurNom=1');
        exit();
    }
    // Si le prenom possède pas que des lettres, espaces ou tirets
    else if (!preg_match('/^[A-Za-z\s\-]+$/',$prenom)) {
        header('Location: /formProprietaire.php?erreurPrenom=1');
        exit();
    }
    // Si l'adresse mail n'est pas valide
    else if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
        header('Location: /formProprietaire.php?erreurEmail=1');
        exit();
    }
    else {
        $stmtAdresse = $dbh->prepare("UPDATE SAE._adresse SET code_postal = :code_postal, adresse = :adresse, complement_adresse = :complement_adresse, ville = :ville WHERE id_adresse = :id_adresse");
        $stmtAdresse->bindParam(':code_postal', $cp);
        $stmtAdresse->bindParam(':adresse', $adresse);
        $stmtAdresse->bindParam(':complement_adresse', $complement_adresse);
        $stmtAdresse->bindParam(':ville', $ville);
        $stmtAdresse->bindParam(':id_adresse', $compteActuel['id_adresse']);
        $stmtAdresse->execute();

        $stmtCompte = $dbh->prepare("UPDATE SAE._compte SET nom = :nom, prenom = :prenom, civilite = :civilite, mot_de_passe = :mot_de_passe WHERE id_compte = :id_compte");
        $stmtCompte->bindParam(':nom', $nom);
        $stmtCompte->bindParam(':prenom', $prenom);
        $stmtCompte->bindParam(':civilite', $civilite);
        $stmtCompte->bindParam(':mot_de_passe', $mdp);
        $stmtCompte->bindParam(':id_compte', $_SESSION['idUtilisateur']);
        $stmtCompte->execute();

        $stmtProprietaire = $dbh->prepare("UPDATE SAE._proprietaire SET nom_banque = :nom_banque, code_banque = :code_banque, code_guichet = :code_guichet, numero_compte = :numero_compte, cle_rib = :cle_rib, iban = :iban, bic = :bic WHERE id_proprietaire = :id_proprietaire");
        $stmtProprietaire->bindParam(':nom_banque', $nomBanque);
        $stmtProprietaire->bindParam(':code_banque', $codeBanque);
        $stmtProprietaire->bindParam(':code_guichet', $codeGuichet);
        $stmtProprietaire->bindParam(':numero_compte', $numCompte);
        $stmtProprietaire->bindParam(':cle_rib', $cleRIB);
        $stmtProprietaire->bindParam(':iban', $iban);
        $stmtProprietaire->bindParam(':bic', $bic);
        $stmtProprietaire->bindParam(':id_proprietaire', $_SESSION['idUtilisateur']);
        $stmtProprietaire->execute();

        header('Location: /consulterSonCompte.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifer son compte</title>
        <link rel="stylesheet" href="style/style.css">
        <script src="https://kit.fontawesome.com/1d8b63688b.js" crossorigin="anonymous"></script>
        <script src="script.js"></script>
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

            <a href="/messagerie.php">Mes Messages</a>
            <form class="deconnexion" action="/index.php?deconnexion=true" method="post" class="blue-rectangle">
                <input type="submit" value="Déconnexion">
            </form>
        </div>

        <main class="bottom-svg-main">
            <div class="big-card">
                <form class="connexion-container enregistrement" action="/modifierCompteProprietaire.php" method="post">
                    <div id="compte">
                        <div class="row">
                            <select name="civilite" id="civilite" class="civ-selectbox">
                                <option value="M">M.</option>
                                <option value="Mme">Mme.</option>
                                <option value="Autre">Autre</option>
                            </select>
                            <input type="text" id="nom" name="nom" placeholder="Nom" required value="<?php echo $compteActuel['nom'] ?>">
                            <input type="text" id="prenom" name="prenom" placeholder="Prénom" required value="<?php echo $compteActuel['prenom'] ?>">
                        </div>

                        <div class="row">
                            <input type="text" id="pseudo" name="pseudo" placeholder="Pseudo" required readonly value="<?php echo $compteActuel['pseudo'] ?>">
                        </div>

                        <div class="adresse">
                            <div class="row">
                                <input type="text" id="adresse" name="adresse" placeholder="Adresse" required value="<?php echo $adresse['adresse'] ?>">
                            </div>

                            <div class="row">
                                <input type="text" id="cp" name="cp" placeholder="Code postal" required value="<?php echo $adresse['code_postal'] ?>">
                                <input type="text" name="ville" id="ville" placeholder="Ville" required value="<?php echo $adresse['ville'] ?>">
                            </div>

                            <div class="row">
                                <input type="text" id="complement_adresse" name="complement_adresse" placeholder="Complément d'adresse" value="<?php echo $adresse['complement_adresse'] ?>">
                            </div>
                        </div>

                        <div class="row email-tel">
                            <input type="email" id="email" name="email" placeholder="Email" required readonly  value="<?php echo $compteActuel['email'] ?>">
                            <input type="text" id="tel" name="tel" placeholder="Téléphone" required readonly value="<?php echo $compteActuel['telephone'] ?>">
                        </div>

                        <div class="adresse">
                            <div class="row">
                                <input type="text" id="nomBanque" name="nomBanque" placeholder="Nom de banque" required value="<?php echo $proprietaireActuel['nom_banque'] ?>">
                            </div>

                            <div class="row">
                                <input type="text" id="codeBanque" name="codeBanque" placeholder="Code de la banque" required value="<?php echo $proprietaireActuel['code_banque'] ?>">
                                <input type="text" id="codeGuichet" name="codeGuichet" placeholder="Code du guichet" required value="<?php echo $proprietaireActuel['code_guichet'] ?>">
                            </div>

                            <div class="row">
                                <input type="text" id="numCompte" name="numCompte" placeholder="Numéro du compte" required value="<?php echo $proprietaireActuel['numero_compte'] ?>">
                                <input type="text" id="bic" name="bic" placeholder="BIC" required value="<?php echo $proprietaireActuel['bic'] ?>">
                            </div>

                            <div class="row">
                                <input type="text" id="iban" name="iban" placeholder="IBAN" required value="<?php echo $proprietaireActuel['iban'] ?>">
                                <input type="text" id="cleRIB" name="cleRIB" placeholder="Clé RIB" required value="<?php echo $proprietaireActuel['cle_rib'] ?>">
                            </div>
                        </div>

                        <div class="row last-row">
                            <div class="half-row" id="hr-mdp">
                                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                                <input type="password" id="mdp" name="mdp" placeholder="Confirmer le mot de passe" required>
                                <div class="checkbox-container">
                                    <input type="checkbox" name="cgu" id="cgu" class="checkbox" required>
                                    <label for="cgu">J'accepte les <a href="#">Conditions Générales d'Utilisation</a></label>
                                </div>
                            </div>

                            <div class="half-row" id="hr-text">
                                <ul>
                                Le mot de passe doit contenir :
                                    <li>8 caractères</li>
                                    <li>1 majuscule, 1 minuscule</li>
                                    <li>1 chiffre et 1 caractère spécial</li>
                                </ul>
           
                                <input type="submit" value="Modifier compte">
                            </div>
                        </div>
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
</html>

<?php
// Pop-up dans le cas où l'utilisateur à donné 2 mot de passe différents
if (isset($_GET['erreurMdp'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Vous avez donnez 2 mot de passe différents !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le code postal n'est pas valide
if (isset($_GET['erreurCodePostal'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le code postal doit contenir exactement 5 chiffres !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le numéro de téléphone n'est pas valide
if (isset($_GET['erreurTelephone'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le numéro de téléphone doit avoir exactement 10 chiffres !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le RIB n'est pas valide
if (isset($_GET['erreurRIB'])) {
?> 
    <script>
        setTimeout(function() {
            alert("La clé RIB doit être constituée de 2 chiffres entre 01 et 97 !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le code banque n'est pas valide
if (isset($_GET['erreurBanque'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le code Banque doit contenir exactement 5 chiffres !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le code guichet n'est pas valide
if (isset($_GET['erreurGuichet'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le code guichet doit contenir exactement 5 chiffres !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le numéro de compte n'est pas valide
if (isset($_GET['erreurCompte'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le numéro de compte doit contenir exactement 11 chiffres !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le numéro de téléphone n'est pas valide
if (isset($_GET['erreurIBAN'])) {
?> 
    <script>
        setTimeout(function() {
            alert("L'IBAN n'est pas valide !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le numéro de téléphone n'est pas valide
if (isset($_GET['erreurBIC'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le BIC n'est pas valide !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le mot de passe est pas assez sécuriser
if (isset($_GET['erreurSecurite'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le mot de passe ne correspond pas aux consignes de sécurités\n - Au moins une minuscule\n - Au moins une majuscule\n - Au moins un chiffre\n - Au moins un caractère spécial\n - Au moins 12 caractères");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le pseudo est trop long
if (isset($_GET['erreurLongueurPseudo'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le pseudo ne peut pas dépasser plus de 20 caractères !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le nom n'est pas valide
if (isset($_GET['erreurNom'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le champs 'nom' ne peut contenir uniquement des lettres, des espaces ou des tirets !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où le prénom n'est pas valide
if (isset($_GET['erreurPrenom'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Le champs 'prenom' ne peut contenir uniquement des lettres, des espaces ou des tirets !");
        }, 500);
    </script>
<?php
}

// Pop-up dans le cas où l'email n'est pas correct
if (isset($_GET['erreurEmail'])) {
?> 
    <script>
        setTimeout(function() {
            alert("L'adresse email n'est pas valide !");
        }, 500);
    </script>
<?php
}
?>