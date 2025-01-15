<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

function genererCleAleatoire() {
    $caracteresPermis = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $longueurCle = 16;
    $cle = '';

    for ($i = 0; $i < $longueurCle; $i++) {
        $cle .= $caracteresPermis[random_int(0, strlen($caracteresPermis) - 1)];
    }

    return $cle;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_GET['action'] == 'supprimer') {
        $id_cle = $_GET['id_cle'];

        $stmtCle = $dbh->prepare("DELETE FROM sae._cle WHERE id_cle = :id_cle");
        $stmtCle->bindParam(':id_cle', $id_cle);
        $stmtCle->execute();
    }
    else if ($_GET['action'] == 'enregistrer') {
        if (isset($_POST['privilegie']) && ($_POST['privilegie'] == 'on')) {
            $privilege = 'true';
        }
        else {
            $privilege = 'false';
        }
        if (isset($_POST['proprietaire']) && ($_POST['proprietaire'] == 'on')) {
            $proprietaire = 'true';
        }
        else {
            $proprietaire = 'false';
        }

        if (isset($_POST['apirator']) && ($_POST['apirator'] == 'on')) {
            $apirator = 'true';
        }
        else {
            $apirator = 'false';
        }
        $idCle = $_GET['id_cle'];
        $estApirator = false;

        foreach ($dbh->query("SELECT privilege_3 FROM SAE._cle WHERE id_cle = $idCle") as $verifCle) {
            if ($verifCle['privilege_3'] == true) {
                $estApirator = true;
            }
        }

        if ($estApirator == false && $apirator == 'false') {
            $stmtCle = $dbh->prepare("UPDATE sae._cle SET privilege_1 = :privilege, privilege_2 = :proprietaire WHERE id_cle = :id_cle");
            $stmtCle->bindParam(':privilege', $privilege);
            $stmtCle->bindParam(':proprietaire', $proprietaire);
            $stmtCle->bindParam(':id_cle', $_GET['id_cle']);
            $stmtCle->execute();
        }
        else {
            $stmtCle = $dbh->prepare("UPDATE sae._cle SET privilege_3 = :privilege3 WHERE id_cle = :id_cle");
            $stmtCle->bindParam(':privilege3', $apirator);
            $stmtCle->bindParam(':id_cle', $_GET['id_cle']);
            $stmtCle->execute();
        }
    }
    else if ($_GET['action'] == 'ajouter') {
        $stmtCle = $dbh->prepare("INSERT INTO sae._cle (token, id_proprietaire, privilege_1, privilege_2, privilege_3) VALUES (:token, :id_proprietaire, false, false, false)");
        $stmtCle->bindParam(':token', genererCleAleatoire());
        $stmtCle->bindParam('id_proprietaire', $_SESSION['idUtilisateur']);
        $stmtCle->execute();
    }

    /*header('Location: ./cle.php');
    exit();*/
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion des clés API</title>
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
                        $prop = "Propriétaire";
                    }else {
                        $prop = "Client";
                    }
                    ?>
                    <div class="info-text">
                        <p class="pseudo"><?php echo $pseudo;?></p>
                        <p class="proprietaire"><?php echo $prop;?></p>
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

            <a href="/devis.php">Mes Messages</a>
            <form class="deconnexion" action="/index.php?deconnexion=true" method="post" class="blue-rectangle">
                <input type="submit" value="Déconnexion">
            </form>
        </div>

        <main class="top-svg-main">
            <div class="container">
                <div class="card-container">
                    <table class="card card-logement">
                        <tr>
                            <td class="tr-cle">Clé d'authentification</td>
                            <td class="tr-cle">Accès privilégié</td>
                            <td class="tr-cle">Accès propriétaire</td>
                            <td class="tr-cle">Accès apirator</td>
                        </tr>
                        <?php
                        foreach ($dbh->query("SELECT * FROM sae._cle") as $cle) {
                            if ($cle['id_proprietaire'] == $_SESSION['idUtilisateur']) {
                                // Pré-sélection des cases à cocher si privilege_1 ou privilege_2 sont vrais
                                $privilege_checked = $cle['privilege_1'] == 'true' ? 'checked' : '';
                                $proprietaire_checked = $cle['privilege_2'] == 'true' ? 'checked' : '';
                                $apirator_checked = $cle['privilege_3'] == 'true' ? 'checked' : '';
                        ?>
                                <tr>
                                    <td class="tr-cle"><?php echo $cle['token'] ?></td>
                                    <form action="./cle.php?action=enregistrer&id_cle=<?php echo $cle['id_cle'] ?>" method="post">
                                        <td class="tr-cle"><input type="checkbox" name="privilegie" id="privilegie" <?php echo $privilege_checked; ?>></td>
                                        <td class="tr-cle"><input type="checkbox" name="proprietaire" id="proprietaire" <?php echo $proprietaire_checked; ?>></td>
                                        <td class="tr-cle"><input type="checkbox" name="apirator" id="apirator" <?php echo $apirator_checked; ?>></td>
                                        <td class="tr-cle"><input type="submit" value="Enregistrer"></td>
                                    </form>
                                    <td class="tr-cle">
                                        <form action="/cle.php?action=supprimer&id_cle=<?php echo $cle['id_cle'] ?>" method="post">
                                            <td class="tr-cle"><input type="submit" value="Supprimer"></td>
                                        </form>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </table>
                </div>

                <form action="./cle.php?action=ajouter" method="post">
                    <input type="submit" value="Ajouter une clé">
                </form>
            </div>
        </main>

        <!-- Affichage du footer et des informations à propos du site -->
        <?php
        if ($_SESSION['idUtilisateur'] == 1) {
        ?>
            <footer>
            <?php
        } else {
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

    <!-- Script JS pour les différents boutons -->
    <script>
        function toggleCheckbox(checkboxId, element) {
            var checkbox = document.getElementById(checkboxId);
            checkbox.checked = !checkbox.checked;

            // Ajoute ou supprime la classe "selected" en fonction de l'état de la case à cocher
            element.classList.toggle('selected', checkbox.checked);
        }

        function preventLabelSelection() {
            return false;
        }
    </script>
</html>