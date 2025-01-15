<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

error_reporting(0);

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$id_logement = $_GET['idLogement'];
$id_logement_origine = $_GET['idLogement'];





?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modification du Logement</title>
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
        <main class="main-calendrier-ind">
            <div class="test">
            <div class="container container-planning">
                <?php

                $id_logement = $_GET['idLogement'];

                // Récupérer le mois et l'année à afficher
                if (isset($_GET['mois']) && isset($_GET['annee'])) {
                    $mois = $_GET['mois'];
                    $annee = $_GET['annee'];
                } else {
                    // Si aucun mois spécifié, utilisez le mois et l'année actuels
                    $mois = date("n");
                    $annee = date("Y");
                }

                if ($_POST['name'] == "reset") {   
                    header("Location: /mesLogements.php");

                }


                if (isset($_POST['submit']) && $_POST['submit'] == "Appliquer" || $_POST['submit'] == "Mois Suivant" || $_POST['submit'] == "Mois Precedent") {    
                    $cal_prix = $_POST;
                    $prixPeriode = $_POST['calc_prix_mois'];
                    print_r($cal_prix);
                    foreach ($dbh->query("SELECT * FROM sae._jour WHERE id_logement = $id_logement") as $row) {
                        $bdd_date = $row['date_jour'];
                        $bdd_mois = date("m", strtotime($row['date_jour']));
                        $bdd_annee = date("Y", strtotime($row['date_jour']));
                        if ($mois == $bdd_mois && $annee == $bdd_annee){
                            $cle_prix = "calc_prix". $row['date_jour'];
                            if (isset($_POST[$cle_prix])){
                                $update_prix = $_POST[$cle_prix];
                            } 

                            if ($row["raison"] == "reserve"){
                                $indispo = 'reserve';
                                echo $indispo . '<br>';

                            } else if (isset($_POST["check_ind" . $bdd_date])){
                                $indispo = $_POST["check_ind" . $bdd_date];
                            } else {
                                $indispo = "disponible";
                            }
                            
                            $moisUrl = $_GET['mois'];
                            $anneeUrl = $_GET['annee'];
                    
                    
                            /* modifier cal */
                            try{
                                if (isset($update_prix)) {
                                    $dbh->query("UPDATE sae._jour SET tarif_nuit_HT = $update_prix WHERE date_jour = '$bdd_date' and id_logement = $id_logement");
                                }
                                if ($indispo == 'reserve'){
                                    $dbh->query("UPDATE sae._jour SET disponible = false, raison = 'reserve' WHERE date_jour = '$bdd_date' and id_logement = $id_logement"); 
                                } else if (isset($indispo) && $indispo == 'indisponible'){
                                    $dbh->query("UPDATE sae._jour SET disponible = false, raison = 'raison personnelle' WHERE date_jour = '$bdd_date' and id_logement = $id_logement"); 
                                } elseif ($indispo == "disponible" && $row['disponible'] == false) {
                                    $dbh->query("UPDATE sae._jour SET disponible = true, raison = '' WHERE date_jour = '$bdd_date' and id_logement = $id_logement");
                                } else{
                                    $dbh->query("UPDATE sae._jour SET disponible = true, raison = '' WHERE date_jour = '$bdd_date' and id_logement = $id_logement");
                                }

                                if ($row['date_jour'] >= $_POST['dateDebut'] && $row['date_jour'] <= $_POST['dateFin']){
                                    
                                        $dbh->query("UPDATE sae._jour SET tarif_nuit_HT = $prixPeriode WHERE date_jour = '$bdd_date' and id_logement = $id_logement");
                                    
                                }
                                
                                
                            }catch (PDOException $e) {
                                echo "Erreur lors de l'enregistrement des modifications : " . $e->getMessage();
                            }
                        }
                       
                        
                    }
                
                
                    if ($_POST['submit'] == "Mois Precedent"){
                        $mois=($mois == 1) ? 12 : ($mois - 1); 
                        $annee=($mois == 1) ? ($annee - 1) : $annee;
                        header("Location: /calendrier_modif.php?idLogement=$id_logement&mois=$mois&annee=$annee");
                        exit();
                    } 


                    if ($_POST['submit'] == "Mois Suivant"){
                        $mois=($mois == 12) ? 1 : ($mois + 1); 
                        $annee=($mois == 12) ? ($annee + 1) : $annee;
                        header("Location: /calendrier_modif.php?idLogement=$id_logement&mois=$mois&annee=$annee");
                        exit();
                    } 

                
                
                    if ($_POST['submit'] == "Appliquer"){
                        header("Location: /mesLogements.php");
                        exit();
                    } 
                
                }




                // Calculer le premier jour du mois
                $premierJour = mktime(0, 0, 0, $mois, 1, $annee);

                // Nombre de jours dans le mois
                $joursDansMois = date("t", $premierJour);
                // Jour de la semaine du premier jour du mois 
                $jourSemaine = date("N", $premierJour);

                // Créer un tableau pour les noms de jours en commençant par lundi
                $joursSemaine = array("Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim");

                ?>

                <!-- Liens pour naviguer entre les mois -->
                <div id="calendrier-complet">
                    <div id="calendrier-mois">
                        <table>
                            <thead>
                                <tr>
                                    <?php
                                    // Afficher les noms de jours en commençant par lundi
                                    foreach ($joursSemaine as $jour) {?>
                                        <th><?php echo $jour?></th><?php
                                    }
                                    ?>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>

                                    <form action="calendrier_modif.php?idLogement=<?php echo $id_logement ?>&mois=<?php echo $mois?>&annee=<?php echo $annee?> " method="post"><?php
                                    // Afficher les noms de jours en commençant par lundi
                                    
                                    foreach ($dbh->query("SELECT * FROM sae._jour WHERE id_logement = $id_logement ORDER BY id_jour") as $row) { 
                                        $jour = date("d", strtotime($row['date_jour']));
                                        $bdd_mois = date("m", strtotime($row['date_jour']));
                                        $bdd_annee = date("Y", strtotime($row['date_jour']));
                                        $tarif_nuit_ht = $row['tarif_nuit_ht'];
                                        if(date("d", strtotime($row['date_jour'])) == 1 && $bdd_mois == $mois && $bdd_annee == $annee){
                                            // Remplir les premier jours du mois avec des cellules du mois precedent
                                            for ($a = 1; $a < $jourSemaine; $a++) {
                                                $jourMoisPrecedent = date("t", mktime(0, 0, 0, $mois - 1, 1, $annee)) - ($jourSemaine - $a) + 1;
                                                ?><td class='mois-precedent'><p><?php echo $jourMoisPrecedent?></p></td><?php
                                            }
                                        }
                                            
                                        
                                        
                                    
                                        if ($bdd_mois == $mois && $bdd_annee == $annee){
                                            /*<td class="<?php (print_r(($row['disponible'])? "disponible" : "indisponible")); ?>" onclick="toggleCheckbox('<?php echo $row['date_jour'] ?>', this)">*/

                                            if ($row['raison'] == "reserve"){?>
                                                <td class="reserve no-click" onclick="toggleCheckbox('<?php echo $row['date_jour'] ?>', this)">
                                            <?php
                                            } else if ($row['raison'] == "raison personnelle"){?>
                                                <td class="indisponible" onclick="toggleCheckbox('<?php echo $row['date_jour'] ?>', this)">
                                            <?php
                                            } else if ($row['raison'] == "date passe"){?>
                                                <td class="indisponible no-click" onclick="toggleCheckbox('<?php echo $row['date_jour'] ?>', this)">
                                            <?php
                                            } else{?>
                                                <td class="disponible" onclick="toggleCheckbox('<?php echo $row['date_jour'] ?>', this)">
                                            <?php
                                            }
                                            ?>
                                            

                                                <label for="<?php echo $jour ?>" onselectstart="return false;" ondblclick="preventLabelSelection()"><?php echo $jour ?></label><?php

                                                if ($row['disponible'] == true){?>
                                                    <input type="checkbox" name="check_ind<?php echo $row['date_jour'] ?>" class="hidden" id="<?php echo $row['date_jour'] ?>" value="disponible"><?php /* inverse car indisponible quand il click*/
                                                    
                                                } else {?>
                                                    <input type="checkbox" name="check_ind<?php echo $row['date_jour'] ?>" class="hidden" id="<?php echo $row['date_jour'] ?>" value="indisponible" checked><?php
                                                }?>

                                                <div class="zone_prix"><!-- voir si ne pas mettre pour les indispo -->
                                                    <input type="number" class="cal_prix no-arrow" name="calc_prix<?php echo $row['date_jour'] ?>" value=<?php echo "$tarif_nuit_ht" ?> required onclick="stopPropagation(event)"/>€
                                                </div>
                                                
                                                
                                            </td>
                                                
                                                
                                                
                                                <?php


                                            //}
                                        }
                                        // Passer à une nouvelle ligne après chaque septième jour ou à la fin du mois
                                        if (($jour + $jourSemaine - 1) % 7 == 0) {?>
                                            </tr><tr><?php
                                            
                                        }
                                        
                                        
                                        // Remplir les derniers jours du mois avec des cellules du mois suivant
                                        $dernierJour = date("N", mktime(0, 0, 0, $mois, $joursDansMois, $annee));
                                        

                                        if($jour == $joursDansMois && $bdd_mois == $mois && $bdd_annee == $annee){
                                            for ($jour = $dernierJour + 1; $jour <= 7; $jour++) {
                                                $jourMoisSuivant = $jour - $dernierJour;
                                                ?><td class='mois-suivant'><p><?php echo $jourMoisSuivant ?></p></td><?php
                                            }
                                        }
                                        

                                        
                                                
                                    }
                                    
                                    ?>
                                </tr>
                            </tbody>
                        </table>


                    

                    </div>
                </div>
                <div id='nav-mois-modif-ind'>
                    <input type="submit" name="submit" value="Mois Precedent"/>
                    <?php
                    setlocale(LC_TIME, 'fr_FR.utf8');?>
                    <h2><?php echo strtoupper(strftime("%B %Y", $premierJour)); ?></h2>
                    
                    <input type="submit" name="submit" value="Mois Suivant"/>
                </div>
            </div>

                <div class="container">
                    <div class="zone_prix_mois"> <!-- pop up ? -->
                        <label>Periode : </label>
                        <div class="date-container">
                            <div id="dateDebut">
                                <label for="dateDebut">Debut :</label>
                                <input type="date" class="date" name="dateDebut" />
                            </div>
                            <div id="dateFin">
                                <label id="dateFin" for="dateFin">   Fin :    </label>
                                <input type="date" class="date" name="dateFin" />
                            </div>

                        </div>
                    
                        <label for="calc_prix_mois">Prix par jours sur la periode (optionnel)</label>
                        <input type="number" class="calc_prix_mois no-arrow" name="calc_prix_mois" />
                        <!--oninput="updateInputMois(this.value)"-->
                    </div>
                        
                        <div id="bouton-valider">
                            <input id="appliquer-cal" type="submit" name="submit" value="Appliquer" />
                            <input id="annuler-cal" type="reset" name="Annuler" value="Annuler" onclick="return confirmerAnnulation()"/>
                        </div>
                    </div>
                        
                    </form>
                    
                </div>
                </div>                    
            
        </main>
        
        <!-- Affichage du footer et des informations à propos du site -->
        
    </body>

    <script>

    function updateInputMois(value) {
        var prixInputs = document.querySelectorAll('.cal_prix'); // Sélectionnez tous les éléments avec la classe cal_prix

        prixInputs.forEach(function(input) {
            input.value = value;
        });
    }


    function preventLabelSelection() {
        return false;
    }

    function toggleCheckbox(checkboxId, element) {
        var checkbox = document.getElementById(checkboxId);
        checkbox.checked = !checkbox.checked;
        var checkbox_class = element.className;
        element.className = checkbox_class == 'disponible' ? "indisponible" : "disponible";
        element.className = checkbox_class == 'disponible' ? "indisponible" : "disponible";
        checkbox.value = checkbox.checked ? "indisponible" :  "disponible";
        console.log(checkbox.value);
        console.log(element.className);

    }

    function stopPropagation(event) {
        event.stopPropagation();
    }

    function confirmerAnnulation() {
        if (confirm('Etes vous sur de vouloir annuler ?')) {
            // Rediriger vers la page mesLogements
            window.location.href = 'mesLogements.php'; // Assurez-vous de renseigner le bon chemin vers votre page
            return true;
        } else {
            return false;
        }
    }
</script>

<footer class="calendrier-modif-connected-footer">
        <?php
        
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

</html>


