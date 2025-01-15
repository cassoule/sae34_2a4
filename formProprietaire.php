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

// Si l'utilisateur à valider la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    // Si l'utilisateur souhaite créer un compte avec des moyens d'identification unique déjà existant
    foreach ($dbh->query("SELECT * FROM SAE._compte") as $row) {
        if (($pseudo == $row['pseudo']) || ($email == $row['email']) || ($tel == $row['telephone'])) {
            header('Location: /creerCompte.php?erreurIdentification=1');
            exit();
        }
    }

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
    // Sinon on enregistre les informations dans la base de données
    else {
        // Enregistrement des informations utilisateur pour après faire un ajout du propriétaire
        try {
            // Enregistrement de l'adresse de l'utilisateur
            try{
                $stmt = $dbh->prepare("INSERT INTO sae._adresse (code_postal, adresse, complement_adresse, ville) VALUES (:cp, :adresse, :complement_adresse, :ville)");
                $stmt->bindParam(':cp', $cp);
                $stmt->bindParam(':adresse', $adresse);
                $stmt->bindParam(':complement_adresse', $complement_adresse);
                $stmt->bindParam(':ville', $ville);

                $stmt->execute();
            }
            // Pour gérer les cas d'erreur de la base de données
            catch(PDOException $e){
                echo "Erreur lors de la creation de l'adresse : " . $e->getMessage();
            }

            // Enregistrement du compte dans la base de données
            $result = $dbh->query("SELECT id_adresse FROM sae._adresse WHERE (code_postal = '$cp') and (adresse = '$adresse') and (complement_adresse = '$complement_adresse') and (ville = '$ville')");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $id_adresse = $row['id_adresse'];

            $stmt = $dbh->prepare("INSERT INTO sae._compte (nom, prenom, civilite, email, telephone, mot_de_passe, pseudo, id_adresse) VALUES (:nom, :prenom, :civilite, :email, :telephone, :mot_de_passe, :pseudo, :id_adresse)");
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':civilite', $civilite);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $tel);
            $stmt->bindParam(':mot_de_passe', $mdp);
            $stmt->bindParam(':pseudo', $pseudo);
            $stmt->bindParam(':id_adresse', $id_adresse);

            $stmt->execute();
        }
        // Pour gérer les cas d'erreur de la base de données
        catch (PDOException $e) {
            echo "Erreur lors de la creation du compte : " . $e->getMessage();
        }

        // Enregistrement du propriétaire dans la base de données
        try{
            $result = $dbh->query("SELECT id_compte FROM sae._compte WHERE (nom = '$nom') and (prenom = '$prenom') and (civilite = '$civilite') and (email = '$email') and (telephone = '$tel')");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $id_proprietaire = $row['id_compte'];

            $stmt = $dbh->prepare("INSERT INTO sae._proprietaire (id_proprietaire, validation_conditions, nom_banque, code_banque, code_guichet, numero_compte, cle_rib, iban, bic) VALUES (:id_proprietaire, :validation_conditions, :nom_banque, :code_banque, :code_guichet, :numero_compte, :cle_rib, :iban, :bic)");
            $stmt->bindParam(':id_proprietaire', $id_proprietaire);
            $stmt->bindParam(':validation_conditions', $conditions);
            $stmt->bindParam(':nom_banque', $nomBanque);
            $stmt->bindParam(':code_banque', $codeBanque);
            $stmt->bindParam(':code_guichet', $codeGuichet);
            $stmt->bindParam(':numero_compte', $numCompte);
            $stmt->bindParam(':cle_rib', $cleRIB);
            $stmt->bindParam(':iban', $iban);
            $stmt->bindParam(':bic', $bic);

            $stmt->execute();

            header('Location: /index.php');
            exit();
        }
        // Pour gérer les cas d'erreur de la base de données
        catch(PDOException $e){
            echo "Erreur lors de la création du compte proprietaire : " . $e->getMessage();
        }
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Création d'un propriétaire propriétaire</title>
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
            <div class="logInfo">
                <a class="white-rectangle" href="/formProprietaire.php">S'enregistrer</a>
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
        <main class="bottom-svg-main">
            <div class="big-card" style="padding-left:0;">
            <?php //print_r($_GET); ?>
                <!-- Formulaire pour la création d'un compte propriétaire -->
                <form class="connexion-container enregistrement" action="/formProprietaire.php" method="post" enctype="multipart/form-data">
                    <!-- Informations liés à la création du compte -->
                    <div id="compte">
                    <div class="firstrow">
                            <select name="civilite" id="civilite" class="civ-selectbox">
                                <option value="M">M.</option>
                                <option value="Mme">Mme.</option>
                                <option value="Autre">Autre</option>
                            </select>
                            <label for="nom" class="label" > Nom :</label>
                            <input type="text" id="nom" name="nom" required value="<?php if(isset($_GET['nom'])) {echo $_GET['nom'];};?>">
                            <label for="prenom" class="label" > Prenom :</label>
                            <input type="text" id="prenom" name="prenom" required value="<?php if(isset($_GET['prenom'])) {echo $_GET['prenom'];}?>" style="flex-grow: 1;">
                        </div>
                        
                        <div class="row">
                            <label for="pseudo" class="label"> Pseudo :</label>
                            <input type="text" id="pseudo" name="pseudo" required value="<?php if(isset($_GET['pseudo'])) {echo $_GET['pseudo'];}?>">
                        </div>

                        <div class="adresse">
                            <div class="row">
                            <label for="pseudo" class="label"> Adresse :</label>
                                <input type="text" id="adresse" name="adresse" required value="<?php if(isset($_GET['adresse'])) {echo $_GET['adresse'];}?>">
                            </div>

                            <div class="row">
                                <label for="cp" class="label"> Code postal :</label>
                                <input type="text" id="cp" name="cp" required value="<?php if(isset($_GET['cp'])) {echo $_GET['cp'];};?>">
                                <label for="ville" class="label"> Ville :</label>
                                <input type="text" name="ville" id="ville" required value="<?php if(isset($_GET['ville'])) {echo $_GET['ville'];}?>">
                            </div>

                            <div class="row">
                                <label for="vilcomplement_adressele" class="label"> Complément d'adresse :</label>
                                <input type="text" id="complement_adresse" name="complement_adresse" value="<?php if(isset($_GET['vilcomplement_adressele'])) {echo $_GET['vilcomplement_adressele'];}?>">
                            </div>
                        </div>

                        <div class="row email-tel">
                            <label for="email" class="label"> Email :</label>
                            <input type="email" id="email" name="email" required value="<?php if(isset($_GET['email'])) {echo $_GET['email'];}?>">
                            <label for="tel" class="label"> Téléphone :</label>
                            <input type="text" id="tel" name="tel" required value="<?php if(isset($_GET['tel'])) {echo $_GET['tel'];}?>">
                        </div>
                        <br>
                        <hr style="margin-left:1em;">
                        <br>
                        <div class="adresse">
                            <div class="row">
                                <label for="nomBanque" class="label"> Nom de votre banque :</label>
                                <input type="text" id="nomBanque" name="nomBanque" required>
                            </div>

                            <div class="row">
                                <label for="codeBanque" class="label"> Code de la banque :</label>
                                <input type="text" id="codeBanque" name="codeBanque" required>
                            </div>
                            <div class="row">
                                <label for="codeGuichet" class="label"> Code du guichet :</label>
                                <input type="text" id="codeGuichet" name="codeGuichet" required>
                            </div>

                            <div class="row">
                                <label for="numCompte" class="label"> N° de compte :</label>
                                <input type="text" id="numCompte" name="numCompte" required>
                            </div>
                            <div class="row">
                                <label for="bic" class="label"> BIC :</label>
                                <input type="text" id="bic" name="bic" required>
                            </div>

                            <div class="row">
                                <label for="iban" class="label"> IBAN :</label>
                                <input type="text" id="iban" name="iban" required>
                            </div>
                            <div class="row">
                                <label for="cleRIB" class="label"> Clé RIB :</label>
                                <input type="text" id="cleRIB" name="cleRIB" required>
                            </div>
                        </div>
                        <hr style="margin-left:1em;">
                        <div class="row last-row">
                            <div class="half-row" id="hr-mdp">
                                <label for="password" class="label"> Mot de passe :</label>
                                <input type="password" id="password" name="password" required style="margin-left: 1em;">
                                <label for="mdp" class="label"> Confirmer votre mot de passe :</label>
                                <input type="password" id="mdp" name="mdp" required style="margin-left: 1em;">
                                <div class="checkbox-container">
                                    <input type="checkbox" name="cgu" id="cgu" class="checkbox" required style="margin-left: 1em;">
                                    <label for="cgu">J'accepte les <a href="#">Conditions Générales d'Utilisation</a></label>
                                </div>
                            </div>
                            <div class="half-row" id="hr-text">
                                <ul>
                                Le mot de passe doit contenir :
                                    <li>12 caractères</li>
                                    <li>1 majuscule, 1 minuscule</li>
                                    <li>1 chiffre et 1 caractère spécial</li>
                                </ul>

                                
                                <input type="submit" value="S'enregistrer">
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
</html>

<?php
// Pop-up dans le cas où l'utilisateur souhaite s'enregistrer avec un email, telephone ou pseudo déjà existant
if (isset($_GET['erreurIdentification'])) {
?> 
    <script>
        setTimeout(function() {
            alert("Email, téléphone ou pseudo déjà existant !");
        }, 500);
    </script>
<?php
}

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