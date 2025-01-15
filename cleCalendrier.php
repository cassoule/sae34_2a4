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
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Stocke l'idUtilisateur dans une nouvelle variable
$id_compte = $_SESSION['idUtilisateur'];

//nombre de logements
$stmtLogement = $dbh->prepare("SELECT COUNT(*) FROM sae._logement WHERE id_proprietaire = :id_compte");
$stmtLogement->bindParam(':id_compte', $id_compte);
$stmtLogement->execute();
$stmtLogement = $stmtLogement->fetch(PDO::FETCH_ASSOC);
$nblogement = $stmtLogement['count'];


function genererCleAleatoire()
{
    $caracteresPermis = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $longueurCle = 16;
    $cle = '';

    for ($i = 0; $i < $longueurCle; $i++) {
        $cle .= $caracteresPermis[random_int(0, strlen($caracteresPermis) - 1)];
    }

    return $cle;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_GET['action']) && ($_GET['action'] == 'ajouter')) {


        //print_r($_POST);
        //print_r($_POST['logement']);
        $logementsArray = "{" ;

        foreach ($dbh->query("SELECT * FROM sae._logement") as $cle) {
            if ($cle['id_proprietaire'] == $_SESSION['idUtilisateur']) {
                if(isset($_POST[$cle['id_logement']])) {
                    $logementsArray = $logementsArray . $_POST[$cle['id_logement']] . ", ";
                }
            }
        }

        $logementsArray = $logementsArray .  "}";
        $logementsArray = substr($logementsArray, 0, strlen($logementsArray) - 2) . substr($logementsArray, -1);
        $logementsArray = substr($logementsArray, 0, strlen($logementsArray) - 2) . substr($logementsArray, -1);

        //print_r($logementsArray);

        
        $stmtCle = $dbh->prepare("INSERT INTO sae._clecalendrier (token, nom_cal, id_proprietaire, reservation, demandeReservation, indisponibilite, debut, fin, logement) VALUES (:token, :nom_cal, :id_proprietaire,  :reservation, :demandeReservation, :indisponibilite, :debut, :fin, :logement)");
        $stmtCle->bindValue(':token', genererCleAleatoire());
        $stmtCle->bindValue(':id_proprietaire', $_SESSION['idUtilisateur']);
        $stmtCle->bindValue(':nom_cal', $_POST['nom']);
        $stmtCle->bindValue(':debut', $_POST['trip-start']);
        $stmtCle->bindValue(':fin', $_POST['trip-end']);
        if (isset($_POST['reservation'])) {
            $stmtCle->bindValue(':reservation', true, \PDO::PARAM_BOOL);
        } else {
            $stmtCle->bindValue(':reservation', false, \PDO::PARAM_BOOL);
        }
        if (isset($_POST['demandeReservation'])) {
            $stmtCle->bindValue(':demandeReservation', true, \PDO::PARAM_BOOL);
        } else {
            $stmtCle->bindValue(':demandeReservation', false, \PDO::PARAM_BOOL);
        }
        if (isset($_POST['indisponibilite'])) {
            $stmtCle->bindValue(':indisponibilite', true, \PDO::PARAM_BOOL);
        } else {
            $stmtCle->bindValue(':indisponibilite', false, \PDO::PARAM_BOOL);
        }
        $stmtCle->bindValue(':logement', $logementsArray);
        $stmtCle->execute();
        

        //print_r($_POST);
    }
}




ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clées Calendrier</title>
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
        } else {
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
                    if ($row['id_proprietaire'] == $id_compte) {
                        $proprietaire = true;
                    }
                }
                if ($proprietaire == true) {
                    $proprietaire = "Propriétaire";
                } else {
                    $proprietaire = "Client";
                }
                ?>
                <div class="info-text">
                    <p class="pseudo"><?php echo $pseudo; ?></p>
                    <p class="proprietaire"><?php echo $proprietaire; ?></p>
                </div>
                <div class="pdp-container">
                    <img class="pdp" src="<?php echo $lien_image ?>" alt="pdp">
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
        if ($proprietaire == "Propriétaire") {
        ?>
            <a href="/mesLogements.php">Mes logements</a>
            <a href="/mesReservationsProprietaires.php">Mes réservations</a>
        <?php
        } else {
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


    <main class="top-svg-main">

        <div class="container">
            <div class="card-container">
                <table class="card ">
                    <thead class="clecalhead">
                        <tr>
                            <td class="tr-cle">Nom du calendrier</td>
                            <td class="tr-cle">Réservations</td>
                            <!--<td class="tr-cle">Demandes des réservations</td>-->
                            <td class="tr-cle">Indisponibilitées</td>
                            <td class="tr-cle">Date de début</td>
                            <td class="tr-cle">Date de fin</td>
                            <td class="tr-cle">Logements utilisés</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($dbh->query("SELECT * FROM sae._cleCalendrier") as $cle) {
                            if ($cle['id_proprietaire'] == $_SESSION['idUtilisateur']) {
                        ?>
                                <tr>
                                    <td class="tr-cle"><?php echo $cle['nom_cal'] ?></td>

                                    <td class="tr-cle"><?php if ($cle['reservation'] == false) {
                                                            echo "Non";
                                                        } else {
                                                            echo "Oui";
                                                        } ?></td>

                                    <td class="tr-cle"><?php if ($cle['demandereservation'] == false) {
                                                            echo "Non";
                                                        } else {
                                                            echo "Oui";
                                                        } ?></td>

                                    <!-- <td class="tr-cle"><?php /*if ($cle['indisponibilite'] == false) {
                                                            echo "Non";
                                                        } else {
                                                            echo "Oui";
                                                        } */?></td> -->

                                    <td class="tr-cle"><?php echo $cle['debut'] ?></td>

                                    <td class="tr-cle"><?php echo $cle['fin'] ?></td>

                                    <td class="tr-cle"><?php
                                                        $liste = explode(",", substr($cle['logement'], 1, -1));
                                                        echo "| ";
                                                        foreach ($liste as $key => $value) {
                                                            //echo $value;
                                                            
                                                            foreach ($dbh->query("SELECT * FROM sae._logement") as $logement) {
                                                                if ($value == $logement['id_logement']) {
                                                                    echo $logement['libelle_logement'];
                                                                    echo " | ";
                                                                }
                                                            }
                                                        }
                                                        ?></td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="card-container">
                <table class="card ">
                    <thead class="clecalhead">
                        <tr>
                            <td class="tr-cle">Nom du calendrier</td>
                            <td class="tr-cle">Lien du calendrier</td>
                            <td class="tr-cle">Copier le calendrier</td>
                            <td class="tr-cle">Supprimer le calendrier</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($dbh->query("SELECT * FROM sae._cleCalendrier") as $cle) {
                            if ($cle['id_proprietaire'] == $_SESSION['idUtilisateur']) {
                                $lien = "https://site-sae-sixpetitscochons.bigpapoo.com/cal.php?token=" . $cle["token"];
                        ?>
                                <tr>
                                    <td class="tr-cle"><?php echo $cle['nom_cal'] ?></td>
                                    <td class="tr-cle"> <a href="<?php echo $lien ?>"> <?php echo $lien ?> </a></td>
                                    <td class="tr-cle"> <img class="img-cleCalendrier" src="./img/copy-two-paper-sheets-interface-symbol.png" alt="copié" width="32px" onclick="copylien('<?php echo $lien ?>')"> </td>
                                    <td class="tr-cle"> <img class="img-cleCalendrier" src="./img/delete.png" alt="supprimer" width="32px" onclick="confirmerSuppression('<?php echo $cle['id_clecal'] ?>')"> </td>
                                </tr>
                        <?php
                            }
                        } ?>
                    </tbody>
                </table>
            </div>

            <div class="card-container">

                <form action="./cleCalendrier.php?action=ajouter" method="post" class="card" onsubmit="return validateCheckboxes()">

                    <label for='nom'> Nom du calendrier: </label>
                    <input type="text" id="nom" name="nom" required>

                    <fieldset class="event-fieldset">
                        <legend>Choisissez vos évènement:</legend>

                        <div>
                            <input type="checkbox" id="indisponibilite" name="indisponibilite" value="indisponibilite" />
                            <label for="indisponibilite">Indisponibilitées</label>
                        </div>

                        <div>
                            <input type="checkbox" id="reservation" name="reservation" value="reservation" />
                            <label for="reservation">Réservations confirmées</label>
                        </div>

                        <!--
                        <div>
                            <input type="checkbox" id="demandeReservation" name="demandeReservation" value="demandeReservation" />
                            <label for="demandeReservation">Demande de réservations</label>
                        </div>
                        -->

                    </fieldset>

                    <label for="start">Date de debut:</label>
                    <input type="date" id="start" name="trip-start" value="2024-04-10" min="2018-01-01" max="2025-12-31" required />

                    <label for="end">Date de fin:</label>
                    <input type="date" id="end" name="trip-end" value="2024-04-20" min="2018-01-01" max="2025-12-31" required />

                    <fieldset class="logement-fieldset">
                        <legend>Choisissez vos logements:</legend>
                        <?php
                        foreach ($dbh->query("SELECT * FROM sae._logement") as $cle) {
                            if ($cle['id_proprietaire'] == $_SESSION['idUtilisateur']) {
                        ?>
                                <div>
                                    <input type="checkbox" id="logement" name="<?php echo $cle['id_logement'] ?>" value="<?php echo $cle['id_logement'] ?>" />
                                    <label for="<?php echo $cle['id_logement'] ?>"> <?php echo $cle['libelle_logement'] ?> </label>
                                </div>

                        <?php
                            }
                        }

                        ?>
                    </fieldset>

                    <input type="submit" value="Ajouter une clé">
                </form>

            </div>
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

<script>
    function validateCheckboxes() {
        print_r("aaaaaa");
        var eventFieldset = document.querySelector('.event-fieldset');
        var logementFieldset = document.querySelector('.logement-fieldset');

        var eventChecked = false;
        var logementChecked = false;

        // Vérifier les checkboxes des évènements
        var eventCheckboxes = eventFieldset.querySelectorAll('input[type="checkbox"]');
        for (var i = 0; i < eventCheckboxes.length; i++) {
            if (eventCheckboxes[i].checked) {
                eventChecked = true;
                break;
            }
        }

        // Vérifier les checkboxes des logements
        var logementCheckboxes = logementFieldset.querySelectorAll('input[type="checkbox"]');
        for (var i = 0; i < logementCheckboxes.length; i++) {
            if (logementCheckboxes[i].checked) {
                logementChecked = true;
                break;
            }
        }

        // Si aucune checkbox n'est cochée dans un des deux groupes, empêcher la soumission du formulaire
        if (!eventChecked || !logementChecked) {
            alert('Veuillez sélectionner au moins un évènement et un logement.');

            return false;
        }

        // Si au moins une checkbox est cochée dans chaque groupe, permettre la soumission du formulaire
        return true;
    }

    function copylien(lien) {
        try {
            navigator.clipboard.writeText(lien);
            //console.log('Lien copié dans le presse-papier :', lien);
            alert("Le lien a été copié");
        } catch (err) {
            //console.error('Erreur lors de la copie du lien : ', err);
            alert("Erreur de copy");
        }
    }

    function confirmerSuppression(id) {
        // Affiche une boîte de dialogue pour confirmer la suppression
        var confirmation = confirm("Etes-vous sûr de vouloir ce calendrier ?");
        // Si l'utilisateur a confirmé, redirige vers supprimer.php
        if (confirmation) {
            window.location.href = "/suprCal.php?id_clecal=" + id;
        } else {
            // Si l'utilisateur a annulé, redirige vers mesLogements.php
            //window.location.href = "/cleCalendrier.php";
            alert("Le calendrier n'a pas été supprimé")
        }

        // Empêche l'envoi du formulaire par défaut
        return false;
    }
</script>


</html>