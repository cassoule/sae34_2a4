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

// Chargement de la base de données
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Permet de stocker l'idUtilisateur dans une variable
$id_user = $_SESSION['idUtilisateur'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de la messagerie</title>
    <link rel="stylesheet" href="style/style.css">
    <script src="https://kit.fontawesome.com/1d8b63688b.js" crossorigin="anonymous"></script>
        <script src="script.js"></script>
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
        } else {
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
                if ($row['id_proprietaire'] == $id_compte) {
                    $proprietaire = true;
                }
            }
            if ($proprietaire == true) {
            ?>
                <a href="https://site-sae-sixpetitscochons.bigpapoo.com/mesLogements.php">Mes logements</a>
                <a href="https://site-sae-sixpetitscochons.bigpapoo.com/mesReservationsProprietaires.php">Mes réservations</a>
            <?php
            } else {
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

    <!-- CSS de la messagerie -->
    <style>
        h1 {
            text-align: center;
            color: #333;
        }

        header {
            text-align: center;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        header h1 {
            margin: 0 10px;
        }

        header a {
            color: #fff;
            margin: 0 10px;
            text-decoration: none;
        }

        header a:hover {
            text-decoration: underline;
        }

        header img {
            max-width: 100px;
            max-height: 100px;
            margin-right: 10px;
        }

        body {
            margin: 0;
        }

        #messagerie_globale {
            margin: 5px;
            display: flex;
            /* Utiliser flex pour aligner #contact et #messagerie côte à côte */
            width: 100%;
        }

        .contact {
            border: 2px solidemande_devis black;
        }

        #contacts {
            border: 2px solid black;
            width: 20%;
            height: 75vh;
            color: #333;
        }

        #messagerie {
            margin: 0 5px 0 5px;
            width: 80%;
            /* Réduire la largeur de #messagerie pour laisser de l'espace pour #contact */
            height: 75vh;
            border: 2px solid gray;
            overflow-y: scroll;
            /* Activer la barre de défilement vertical */
        }

        #ligne_message {
            display: flex;
        }

        input[type="text"] {
            width: 72%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            float: right;
            margin: 5px 25px 5px 5px;
        }

        .message {
            border: 2px solid black;
            border-radius: 15px;
            max-width: 45%;
            margin: 5px;
            padding: 10px;
        }

        .mon_compte {
            color: red;
        }

        .autre_compte {
            color: blue;
        }
        
        #devis {
            max-width: 100%;
            width: 100%;

        }

        #demande_devis {
            max-width: 100%;
            width: 100%;

        }

        p {
            margin: 0;
        }
    </style>

    <!-- Affichage des informations principales de la page -->
    <main>
        <div id="messagerie_globale">
            <div id="contacts">
                <h2>CONTACTS</h2>
                <?php
                if ($proprietaire == true) {//afficher les contacts si tu est le propriétaire
                    foreach ($dbh->query("SELECT * FROM sae._messagerie WHERE id_proprietaire = " . $id_user) as $row) {
                        $result = $dbh->query("SELECT * FROM sae._compte WHERE id_compte = " . $row['id_client']);
                        $fichepers = $result->fetch(PDO::FETCH_ASSOC);
                        $a = $fichepers['prenom'] . " " . $fichepers['nom'];
                        //recup la liste des id de tes contacts 
                        $lst[] = $row['id_client'];
                ?>
                        <a href="">
                            <div class="contact">
                                <p><?php echo $a ?></p>
                            </div>
                        </a>
                    <?php
                    }
                }
                else {//afficher les contacts si tu est le client
                    foreach ($dbh->query("SELECT * FROM sae._messagerie WHERE id_client = " . $id_user) as $row) { 
                        $result = $dbh->query("SELECT * FROM sae._compte WHERE id_compte = " . $row['id_proprietaire']); 
                        $fichepers = $result->fetch(PDO::FETCH_ASSOC);
                        $a = $fichepers['prenom'] . " " . $fichepers['nom'];
                        //recup la liste des id de tes contacts 
                        $lst[] = $row['id_proprietaire'];
                    ?>
                        <a id="contact" href="">
                            <div>
                                <p><?php echo $a ?></p>
                            </div>
                        </a>
                <?php
                    }
                }
                ?>
            </div>
            <!--Pour selectionner la personne il faut du js la ca va etre en dur -->
            <?php
            if ($proprietaire == true) {
                $id_correspondant=$lst[0];
            } else {
                $id_correspondant=$lst[0];
            }
            
            ?>
            <div id="messagerie">

                <?php
                if ($proprietaire == true){
                    foreach ($dbh->query("SELECT * FROM sae._message WHERE id_contient_client = " . $id_correspondant . " AND id_contient_proprietaire = " . $id_user) as $row) { /* a modif */
                    ?>
                        <div id="ligne_message">
                            <div class="message" id="
                                    <?php
                                    $classes = [];

                                    // Vérifiez si l'ID de message est dans la table _message_devis
                                    foreach ($dbh->query("SELECT id_message_devis, id_logement FROM sae._message_devis inner join sae._message on _message_devis.id_message_devis = _message.id_message") as $row_devis) {
                                        if ($row['id_message'] == $row_devis['id_message_devis']) {
                                            // Ajoutez la classe "devis" au tableau
                                            $classes[] = "devis";
                                            $classes[] = $row_devis['id_logement'];
                                        }
                                    }

                                    // Vérifiez si l'ID de message est dans la table _message_demande_devis
                                    foreach ($dbh->query("SELECT id_message_demande_devis, id_logement FROM sae._message_demande_devis inner join sae._message on _message_demande_devis.id_message_demande_devis = _message.id_message") as $row_demande_devis) {
                                        print_r($row_demande_devis);
                                        if ($row['id_message'] == $row_demande_devis['id_message_demande_devis']) {
                                            // Ajoutez la classe "demande_devis" au tableau 
                                            $classes[] = "demande_devis";
                                            $classes[] = $row_demande_devis['id_message_demande_devis'];
                                            $classes[] = $row_demande_devis['id_logement'];
                                        }
                                    }

                                    // Concaténez les classes en une seule chaîne
                                    $class_attribute = implode(' ', $classes);
                                    // Affichez la classe résultante
                                    echo $class_attribute;
                                    ?>">

                                <p class="<?php
                                            /* a modif*/
                                            if ($row['id_emetteur'] == $id_user) {
                                                echo 'mon_compte';
                                            } else {
                                                echo 'autre_compte';
                                            }
                                            ?>">
                                    <?php echo $row['contenu'] ?>
                                </p>

                                <?php
                                // Si la classe "demande_devis" est présente, affichez le formulaire
                                if (in_array('demande_devis', $classes)) {
                                ?>
                                    <form action="https://site-sae-sixpetitscochons.bigpapoo.com/envoieMessageDevis.php?idMessageDevis=<?php echo $classes[1] ?>&idLogement=<?php echo $classes[2] ?>" method="post">
                                        <button type="submit" name="bouton" value="accepter">Accepter</button>
                                    </form>
                                    <!-- Peut-être faire retourner l'utilisateur sur la page d'accueil si il refuse -->
                                    <form action="https://site-sae-sixpetitscochons.bigpapoo.com/messagerie.php" method="post">
                                        <button type="submit" name="bouton" value="refuser">Refuser</button>
                                    </form>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
            </div>
            <?php
                }
            }
            else {
                foreach ($dbh->query("SELECT * FROM sae._message WHERE id_contient_client = ". $id_user . " AND id_contient_proprietaire = " . $id_correspondant) as $row) { /* a modif */
                    ?>
                        <div id="ligne_message">
                            <div class="message" id="
                                    <?php
                                    $classes = [];
    
                                    // Vérifiez si l'ID de message est dans la table _message_devis
                                    foreach ($dbh->query("SELECT id_message_devis, id_logement FROM sae._message_devis inner join sae._message on _message_devis.id_message_devis = _message.id_message") as $row_devis) {
                                        if ($row['id_message'] == $row_devis['id_message_devis']) {
                                            // Ajoutez la classe "devis" au tableau
                                            $classes[] = "devis";
                                            $classes[] = $row_devis['id_message_devis'];
                                            $classes[] = $row_devis['id_logement'];
                                        }
                                    }
    
                                    // Vérifiez si l'ID de message est dans la table _message_demande_devis
                                    foreach ($dbh->query("SELECT id_message_demande_devis, id_logement FROM sae._message_demande_devis inner join sae._message on _message_demande_devis.id_message_demande_devis = _message.id_message") as $row_demande_devis) {
                                        if ($row['id_message'] == $row_demande_devis['id_message_demande_devis']) {
                                            // Ajoutez la classe "demande_devis" au tableau    
                                            $classes[] = "demande_devis";
                                            $classes[] = $row_demande_devis['id_message_demande_devis'];
                                            $classes[] = $row_demande_devis['id_logement'];
                                        }
                                    }
    
                                    // Concaténez les classes en une seule chaîne
                                    $class_attribute = implode(' ', $classes);
                                    // Affichez la classe résultante
                                    echo $class_attribute;
                                    ?>">
    
                                <p class="<?php
                                            /* a modif*/
                                            if ($row['id_emetteur'] == $id_user) {
                                                echo 'mon_compte';
                                            } else {
                                                echo 'autre_compte';
                                            }
                                            ?>">
                                    <?php echo $row['contenu'] ?>
                                </p>
    
                                <?php
                                // Si la classe "devis" est présente, affichez le formulaire
                                if (in_array('devis', $classes)) {
                                ?>
                                    <div id="bouton_devis">
                                        <form action="https://site-sae-sixpetitscochons.bigpapoo.com/paiement.php?id_devis=<?php echo $classes[1] ?>&id_logement=<?php echo $classes[2] ?>" method="post">
                                            <button type="submit" name="bouton" value="accepter">Accepter</button>
                                        </form>
                                        <!-- Peut-être retourner l'utilisateur sur la page d'accueil si il refuse -->
                                        <form action="https://site-sae-sixpetitscochons.bigpapoo.com/messagerie.php>" method="post">
                                            <button type="submit" name="bouton" value="refuser">Refuser</button>
                                        </form>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                </div>
            <?php
                }
            }
        ?>
        </div>
        </div>

        <form action="https://site-sae-sixpetitscochons.bigpapoo.com/sendMessage.php" method="post">
            <input type="text" id="message" name="message">
        </form>
    </main>

    <!-- Affichage du footer et des informations à propos du site -->
    <footer>
        <a href="https://site-sae-sixpetitscochons.bigpapoo.com"><img src="src/logo.png" alt="Logo ALHaIZ Breizh"></a>
        <h1>ALHaIZ Breizh</h1>
        <a href="https://site-sae-sixpetitscochons.bigpapoo.com/easterEgg.html">&#xA0</a>
    </footer>

    <!-- Script JavaScript de la page pour se rafraichir automatiquement -->
    <script>
        // Fonction pour rafraîchir la page
        function rafraichirPage() {
            location.reload();
        }

        // Appeler la fonction de rafraîchissement toutes les 5 secondes (5000 millisecondes)
        setInterval(rafraichirPage, 500000);

        // Attendez que le document soit prêt
        document.addEventListener("DOMContentLoaded", function() {
            // Sélectionnez l'élément #messagerie
            var messagerie = document.getElementById("messagerie");

            // Définissez le défilement automatiquement en bas
            messagerie.scrollTop = messagerie.scrollHeight;
        });
    </script>
</body>

</html>