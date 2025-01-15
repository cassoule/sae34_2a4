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


// Si jamais l'utilisateur se connecte sur la page et qu'il ne sait pas connecté alors on lui donne l'idUtilisateur 1 qui correspond au compte visiteur
if (!isset($_SESSION['idUtilisateur'])) {
    $_SESSION['idUtilisateur'] = 1;
}

// Si l'utilisateur a cliquer sur le bouton déconnexion alors on récupère le $_GET afin de le savoir
if (isset($_GET['deconnexion'])) {
    // Pour déconnecter l'utilisateur on lui donne l'idUtilisateur qui correspond au compte visiteur
    if ($_GET['deconnexion'] == true) {
        $_SESSION['idUtilisateur'] = 1;

        // Renvoies vers la page d'accueil du site et arrête le script de la page
        header('Location: /index.php');
        exit();
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Page d'accueil</title>
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

    <!-- Affichage des informations principales de la page -->
    <main class="both-svg-main connected-main">
        <div class="big-card" id="big-card">
            <?php 
        $idLogement = $_GET['idLogement'];
        $dateJour = date('d/m/Y');
        
        $estProprietaire = false;

        $idUtilisateur = $_SESSION['idUtilisateur'];
        foreach ($dbh->query("SELECT id_proprietaire FROM SAE._proprietaire") as $row) {
            if ($idUtilisateur == $row['id_proprietaire']){
                $estProprietaire = true;
            }
        }
        
        $idContact = $_GET['idContact'];

        $requete1 = "id_contient_client";
                $requete2 = "id_proprietaire";

                if($estProprietaire == false){
                    $requete1 = "id_proprietaire";
                    $requete2 = "id_contient_client";
                }
            
    ?>
            <div class="white-rectangle borderless">
                <div class="Devis">

                    <div id=HautPage>
                        <img id=LogoDevis src="src/logo.png" alt="Logo ALHaIZ Breizh">
                        <h2 id=TitreDevis>Devis ALHaIZ Breizh</h2>
                    </div>

                    <h4 id="dateDevis">Fait le <?php echo $dateJour; ?></h4>

                    <?php 
                    $estDevis = false;
                    foreach ($dbh->query("SELECT id_message_devis FROM sae._message_devis") as $verifDevis) {
                        if ($verifDevis['id_message_devis'] == $_GET['id_message']) {
                            $estDevis = true;
                        }
                    }

                    $id_message = $_GET['id_message'];

                    if ($estDevis == true) {
                        $requeteLogement = "SELECT * FROM sae._logement NATURAL JOIN sae._adresse NATURAL JOIN sae._message_devis NATURAL JOIN sae._message WHERE id_logement = $idLogement AND sae._message.id_message = $id_message LIMIT 1";
                    }
                    else {
                        $requeteLogement = "SELECT * FROM sae._logement NATURAL JOIN sae._adresse NATURAL JOIN sae._message_demande_devis NATURAL JOIN sae._message WHERE id_logement = $idLogement AND sae._message.id_message = $id_message LIMIT 1";
                    }

                    foreach ($dbh->query($requeteLogement) as $logement) {
                        $idProprietaire = $logement['id_proprietaire'];
                        $idClient = $logement['id_contient_client'];
                        foreach ($dbh->query("SELECT * FROM sae._compte WHERE id_compte = $idClient") as $infoCompteClient){
                            
                        }
                        // Requête pour récupérer les informations du compte client
                        $stmt = $dbh->prepare("SELECT * FROM sae._compte WHERE id_compte = :idClient");
                        $stmt->execute(array(':idClient' => $idClient));
                        $infoCompteClient = $stmt->fetch(PDO::FETCH_ASSOC);

                        // Requête pour récupérer les informations du compte propriétaire
                        $stmt = $dbh->prepare("SELECT * FROM sae._compte WHERE id_compte = :idProprietaire");
                        $stmt->execute(array(':idProprietaire' => $idProprietaire));
                        $infoCompteProprietaire = $stmt->fetch(PDO::FETCH_ASSOC);


                        ?>
                    <div class="infosPro">
                        <h4>Nom du propriétaire: <?php echo $infoCompteProprietaire['prenom'] . " " . $infoCompteProprietaire['nom'] ?> </h4>
                        <h4>Téléphone: <?php echo $infoCompteProprietaire['telephone'] ?></h4>
                        <h4>Email: <?php echo $infoCompteProprietaire['email'] ?></h4>
                    </div>
                    <div class="infosCli">
                        <h4>Nom du client : <?php echo $infoCompteClient['prenom'] . " " . $infoCompteClient['nom'] ?> </h4>
                        <h4>Téléphone : <?php echo $infoCompteClient['telephone'] ?></h4>
                        <h4>Email : <?php echo $infoCompteClient['email'] ?> </h4>
                    </div>

                    <div class="logement-info">
                        <h3>Informations sur le logement</h3>
                        <table>
                            <tbody>
                                <tr>
                                    <td>Titre du logement:</td>
                                    <td> <?php echo $logement['libelle_logement']?></td>
                                </tr>
                                <tr>
                                    <td>Type de logement:</td>
                                    <td><?php echo $logement['type_logement']?></td>
                                </tr>
                                <tr>
                                    <td>Lieu du logement:</td>
                                    <td><?php echo $logement['ville']?></td>
                                </tr>
                                <tr>
                                    <td>Nombre de personnes : </td>
                                    <td><?php echo $logement['nb_personnes']?></td>
                                </tr>
                                <tr>
                                    <?php $date_debut = date('d-m-Y', strtotime($logement['date_debut'] ));
                                        $date_fin = date('d-m-Y', strtotime($logement['date_fin'] ));?>
                                    <td>Dates de séjour:</td>
                                    <td>du <?php echo $date_debut . '  au  ' . $date_fin;?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    

                    
                    ?>
                    <div class="paiement-info">
                        <h3 id=InfoPrix>Informations sur le prix</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Description</th>
                                    <th scope="col">Prix</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><?php
                                // Requête pour récupérer les informations du compte propriétaire
                                
                                $stmt = $dbh->prepare("SELECT * FROM sae._logement NATURAL JOIN sae._jour WHERE id_logement = $idLogement");
                                $stmt->execute();
                                $infoNuit = $stmt->fetch(PDO::FETCH_ASSOC);
                                // Calcul de la différence en jours

                                $date1 = new DateTime($logement['date_debut']); 
                                $date2 = new DateTime($logement['date_fin']); 
                                
                                // Calcul de la différence en jours
                                $interval = $date1->diff($date2);
                                $nb_jours =  $interval->format('%a'); ?>
                                    <th scope="row">Nuit HT</th>
                                    <td><?php echo $infoNuit['tarif_nuit_ht'];?>€ x <?php echo  $nb_jours ?></td>
                                    <?php $prixNuitCharge = $infoNuit['tarif_nuit_ht'] * $nb_jours;?>
                                </tr>
                                <tr>
                                    <th scope="row">Charges HT <br> 
                                    <?php 
                                        foreach ($dbh->query("SELECT * FROM sae._logement NATURAL JOIN sae._charge WHERE id_logement = $idLogement") as $infoLogement){ 
                                            echo "- " . $infoLogement['nom_charge'];?><br> 
                                            <?php $prixNuitCharge +=  $infoLogement['prix_charge_ht']?>
                                        <?php
                                        }
                                        ?>
                                        Total Charges HT 
                                    </th>
                                    <td> <br>                                                        
                                    <?php 
                                        $total = 0;

                                        foreach ($dbh->query("SELECT * FROM sae._logement NATURAL JOIN sae._charge  WHERE id_logement = $idLogement") as $infoLogement){ 
                                            echo $infoLogement['prix_charge_ht'];
                                            $total += $infoLogement['prix_charge_ht'];                             
                                            ?>€<br>
                                        <?php                    
                                        }?><b><?php echo  $total;?>€</b>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Sous-total (nuits et charges) </br> Application d'une TVA de 10%</th>
                                    <td><?php echo $prixTotal = $prixNuitCharge + $prixNuitCharge * 0.1 ?>€</td>
                                </tr>
                                <tr>
                                    <th scope="row">Frais de services de la plateforme </br> Application d'une TVA de 20%</th>
                                    <td><?php 
                                            $prixFrais = $prixTotal * 0.1;
                                            echo $prixFrais = sprintf("%.2f", $prixFrais);?>€<br><?php
                                            $prixFrais = $prixFrais + $prixFrais * 0.2;
                                            echo $prixFrais = sprintf("%.2f", $prixFrais);
                                        ?>€   
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Taxe de séjour</th>
                                    <td><?php echo $prixTaxeDeSejour = 1 ?> €</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="row" colspan="1">Total TTC</th>
                                    <td><?php echo $prixTotal = $prixTotal + $prixFrais + $prixTaxeDeSejour ?>€</td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div id="bouton_devis">
                <?php
                $estDevis = false;
                foreach($dbh->query("SELECT id_message_devis FROM sae._message_devis") as $row) {
                    if ($row['id_message_devis'] == $logement['id_message']) {
                        $estDevis = true;
                    }
                }

                if ($estProprietaire == false && $estDevis == true) {
                ?>
                    <form action="/paiement.php?id_devis=<?php echo $logement['id_message'] ?>&id_logement=<?php echo $logement['id_logement'] ?>" method="post">
                        <button type="submit" name="bouton" value="accepter">Accepter</button>
                    </form>
                    <!-- Peut-être retourner l'utilisateur sur la page d'accueil si il refuse -->
                    <form action="/index.php" method="post">
                        <button type="submit" name="bouton" value="refuser">Refuser</button>
                    </form>
                <?php
                }
                else if ($estProprietaire == true && $estDevis == false) {
                ?>
                    <form action="/envoieMessageDevis.php?idMessageDevis=<?php echo $logement['id_message'] ?>&idLogement=<?php echo $logement['id_logement'] ?>" method="post">
                        <button type="submit" name="bouton" value="accepter">Accepter</button>
                    </form>
                    <!-- Peut-être retourner l'utilisateur sur la page d'accueil si il refuse -->
                    <form action="/index.php" method="post">
                        <button type="submit" name="bouton" value="refuser">Refuser</button>
                    </form>
                <?php
                }
                ?>
            </div>
        </div>
        
        </div>



        <div class="background_svg bs_bottom">
            <div class="back_bottom_svg ">
                <svg class="bottom_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z"
                        fill="#FFB74C" />
                </svg>

                <svg class="bottom_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.5"
                        d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z"
                        fill="#FFB74C" />
                </svg>

                <svg class="bottom_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.3"
                        d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z"
                        fill="#FFB74C" />
                </svg>

                <svg class="bottom_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.2"
                        d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z"
                        fill="#FFB74C" />
                </svg>

                <svg class="bottom_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.1"
                        d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z"
                        fill="#FFB74C" />
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
<script src="https://kit.fontawesome.com/1d8b63688b.js" crossorigin="anonymous"></script>
<script src="script.js"></script>

</html>