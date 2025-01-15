<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Si l'utilisateur à valider la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['email'])) {
    $email = $_GET['email'];
    $estPresent = false;

    // Vérification sur la connexion sur la base de données
    foreach ($dbh->query("SELECT * FROM SAE._compte") as $row) {
        // Si l'email existe dans la base de données
        if ($row['email'] == $email) {
            $mdp = $row['mot_de_passe'];

            header("Location: /connexion.php?recupMdp=$mdp");
            exit();
        }
    }

    header('Location: /connexion.php?erreurRecup=1');
    exit();

    /*try {
        // Vérification sur la connexion sur la base de données
        foreach ($dbh->query("SELECT * FROM SAE._compte") as $row) {
            // Si l'email existe dans la base de données
            if ($row['email'] == $email) {
                $estPresent = true;

                $mdp = $row['mot_de_passe'];
                $sujet = 'Récupération du mot de passe';
                $message = "Voici votre mot de passe : $mdp\n";
                $headers = 'From: irikunda.667@gmail.com';

                $envoieMail = mail($email, $sujet, $message, $headers);

                if ($envoieMail) {
                    header('Location: /index.php');
                    exit();
                }
                else {
                    throw new Exception("Erreur lors de l'envoi de l'e-mail.");
                }
            }
        }

        if ($estPresent == false) {
            // retour vers connexion avec un petit message d'erreur
            header('Location: /connexion.php');
            exit();
        }
    }
    catch (Exception $e) {
        echo "Une erreur s'est produite : " . $e->getMessage();
    }*/
}

ob_end_flush();
?>