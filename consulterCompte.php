<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$userId = $_SESSION['user_id'];

if(isset($_GET['id']) AND $_GET['id'] > 0) {
    $getid = intval($_GET['id']);
    $requser = $bdd->prepare('SELECT * FROM membres WHERE id = ?');
    $requser->execute(array($getid));
    $userinfo = $requser->fetch();
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Création d'un compte client</title>
        <link rel="stylesheet" href="style/style.css">
    </head>
    <body>
        <!-- Affichage du header du site -->
        <header>
            <!-- Affichage du logo et du nom du site -->
            <a href="https://site-sae-sixpetitscochons.bigpapoo.com"><img src="src/logo.png" alt="Logo ALHaIZ Breizh"></a>
            <h1>ALHaIZ Breizh</h1>

            <!-- Affichage de différents informations en fonction d'un visiteur / client / propriétaire -->   
            <?php
            if ($_SESSION['idUtilisateur'] == 1) {
            ?>
                <a href="https://site-sae-sixpetitscochons.bigpapoo.com/creerCompte.php">S'enregistrer</a>
                <a href="https://site-sae-sixpetitscochons.bigpapoo.com/connexion.php">Se connecter</a>
            <?php
            }
            else {
            ?>
                <!-- Affichage des informations lié au compte de la personne -->
                <div>
                    <?php
                    foreach ($dbh->query("SELECT * FROM SAE._compte") as $compte) {
                        if ($compte['id_compte'] == $_SESSION['idUtilisateur']) {
                        ?>
                            <p>
                            <?php
                            echo $compte['pseudo'];
                            $id_compte = $compte['id_compte'];
                            ?>
                            </p>
                        <?php
                        }
                    }
                    ?>
                    <?php
                    foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                        if ($image['id_compte'] == $_SESSION['idUtilisateur']) {
                        ?>
                            <img src="<?php echo $image['lien_image'] ?>" alt="photo de profil" height="85" width="85">
                        <?php
                        }
                    }
                    ?>
                </div>

                <!-- Affichage de différentes informations suivant le client / propriétaire -->
                <?php
                $proprietaire = false;
                foreach ($dbh->query("SELECT id_proprietaire FROM sae._proprietaire") as $row) {
                    if($row['id_proprietaire'] == $id_compte){
                        $proprietaire = true;
                    }
                }
                if($proprietaire == true){
                ?>
                    <a href="https://site-sae-sixpetitscochons.bigpapoo.com/mesLogements.php">Mes logements</a>
                    <a href="https://site-sae-sixpetitscochons.bigpapoo.com/mesReservationsProprietaires.php">Mes réservations</a>
                <?php
                }
                else {
                ?>
                    <a href="https://site-sae-sixpetitscochons.bigpapoo.com/mesReservationsClients.php">Mes réservations</a>
            <?php
                }
            }
            ?>

            <?php
            if ($_SESSION['idUtilisateur'] > 1) {
            ?>
                <form action="https://site-sae-sixpetitscochons.bigpapoo.com?deconnexion=true" method="post">
                    <input type="submit" value="Déconnexion">
                </form>
            <?php
            }
            ?>
        </header>


        <body>
      <h2>Voici le profil de <?= $compte['pseudo']; ?></h2>
      <div>Quelques informations sur lui : </div>  
      
      <?php
                        foreach ($dbh->query("SELECT * FROM SAE._image WHERE id_compte=id") as $image) {
                        ?>
                                <img src="<?php echo $image['lien_image'] ?>" alt="Photo de profil" height="85" width="85">
                        <?php
                        }
                        ?>

                        <!-- Affiche le pseudo -->
                        <?php
                        foreach ($dbh->query("SELECT * FROM SAE._compte") as $compte) {
                        ?>
                                <h3><?php echo $compte['pseudo'] ?></h3>
                        <?php
                            
                        }
                        foreach ($dbh->query("SELECT * FROM SAE._avis WHERE id_compte=id") as $avis) {
                            ?>
                                    <h2><?php echo $avis['titre_avis'] ?></h2>
                                    <h2><?php echo $avis['note_avis'] ?></h2>
                                    <h2><?php echo $avis['contenu_avis'] ?></h2>
                            <?php
                            }
                        ?>
       
      <div>
                <h1>Les logements que l'utilisateur propose :</h1>
                <?php
                foreach ($dbh->query("SELECT * FROM SAE._logement WHERE id_proprietaire=id") as $row) {
                ?>
                        <!-- Lien vers la page détaillé du logement -->
                        <a href="https://site-sae-sixpetitscochons.bigpapoo.com/logementDetail.php?idLogement=<?php echo $row['id_logement'] ?>">
                            <div>
                                <!-- Recherche de l'image en fonction de son id_logement puis affichage de celle-ci -->
                                <?php
                                foreach ($dbh->query("SELECT * FROM SAE._image") as $image) {
                                    if ($image['id_logement_image'] == $row['id_logement']) {
                                ?>
                                        <img src="<?php echo $image['lien_image'] ?>" alt="Photo logement" height="250" width="350">
                                <?php
                                        break;
                                    }
                                }
                                ?>

                                <!-- Recherche de l'adresse en fonction de son id_adresse puis affichage de celui-ci -->
                                <?php
                                foreach ($dbh->query("SELECT * FROM SAE._adresse") as $adresse) {
                                    if ($adresse['id_adresse'] == $row['id_adresse']) {
                                ?>
                                        <h2><?php echo $adresse['ville'] ?></h2>
                                <?php
                                    }
                                }
                                ?>
                                <p><?php echo $row['avis_logement_total'] ?></p>
                                <br>
                                <br>
                            </div>
                        </a>
                <?php
                    }
                ?>
            </div>                                                                                           
  <body>  







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