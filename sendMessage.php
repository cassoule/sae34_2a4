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
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Si l'utilisateur à valider la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $message = $_POST['message'];
    $date = date('Y-m-d H:i:s'); // Date correctement formatée entre guillemets simples

    $id_emetteur = $_SESSION['idUtilisateur'];
    $id_contient_client = 2;
    $id_contient_proprietaire = 3;

    // Si le message n'est pas vide
    if (!empty($message)) {
        // Insérer les informations dans la base de données
        try {
            $stmt = $dbh->prepare("INSERT INTO sae._message (contenu, date_envoi, id_emetteur, id_contient_client, id_contient_proprietaire) VALUES (:message, :date, :id_emetteur, :id_contient_client, :id_contient_proprietaire)");
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':id_emetteur', $id_emetteur);
            $stmt->bindParam(':id_contient_client', $id_contient_client);
            $stmt->bindParam(':id_contient_proprietaire', $id_contient_proprietaire);

            $stmt->execute();
        }
        // Pour gérer les cas d'erreur de la base de données
        catch (PDOException $e) {
            echo "Il y a eu une erreur lors de l'enregistrement : " . $e->getMessage();
        }

        // Renvoies vers la page de messagerie du site et arrête le script de la page
        header('Location: https://site-sae-sixpetitscochons.bigpapoo.com/messagerie.php');
        exit();
    } 
    else {
        echo "Le champ 'message' doit être rempli.";
    }

    // A quoi ca sert ??? Car ne fait rien de visible sur les pages et ca marche toujours sans
    foreach ($dbh->query("SELECT * FROM sae._client") as $row) {
        ?>
            <h2> <?php echo $row['id_client']?></h2>
        <?php
        }
}
?>