<div class="container container-planning">
                            <?php
                            $id_logement = $_GET['idLogement'];

                            if (isset($_POST['submit']) && $_POST['submit'] == "Appliquer") {                          
                                $cal_prix = $_POST;
                                foreach ($dbh->query("SELECT * FROM sae._jour WHERE id_logement = $id_logement") as $row) {
                                    $cle_prix = "calc_prix". $row['date_jour'];
                                    if (isset($_POST[$cle_prix])){
                                        $update_prix = $_POST[$cle_prix];
                                    } 
                                    $bdd_date = $row['date_jour'];
                                    $tarif_mois_HT = $_POST['calc_prix_mois'];
                                    if (isset($_POST["check_ind" . $bdd_date])){
                                        $indispo = $_POST["check_ind" . $bdd_date];
                                    } else {
                                        $indispo = "disponible";
                                    }
                                    $bdd_mois = date("m", strtotime($row['date_jour']));
                                    $bdd_annee = date("Y", strtotime($row['date_jour']));
                                    
                                    try{
                                        if (isset($update_prix)) {
                                            $dbh->query("UPDATE sae._jour SET tarif_nuit_HT = $update_prix WHERE date_jour = '$bdd_date' and id_logement = $id_logement");
                                        }
                                        
                                        if (isset($indispo) && $indispo == 'indisponible'){
                                            $dbh->query("UPDATE sae._jour SET disponible = false, raison = 'raison personnelle' WHERE date_jour = '$bdd_date' and id_logement = $id_logement"); 
                                        } elseif ($indispo == "disponible" && $row['disponible'] == false) {
                                            $dbh->query("UPDATE sae._jour SET disponible = false, raison = 'raison personnelle' WHERE date_jour = '$bdd_date' and id_logement = $id_logement");
                                        } else{
                                            $dbh->query("UPDATE sae._jour SET disponible = true, raison = 'raison personnelle' WHERE date_jour = '$bdd_date' and id_logement = $id_logement");
                                        }

                                    }catch (PDOException $e) {
                                        echo "Erreur lors de l'enregistrement des modifications : " . $e->getMessage();
                                    }
                                }
                            }

                            // Récupérer le mois et l'année à afficher
                            if (isset($_GET['mois']) && isset($_GET['annee'])) {
                                $mois = $_GET['mois'];
                                $annee = $_GET['annee'];
                            }
                            else {
                                // Si aucun mois spécifié, utilisez le mois et l'année actuels
                                $mois = date("n");
                                $annee = date("Y");
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
                            <div>
                                <table>
                                    <thead>
                                        <tr>
                                            <?php
                                            // Afficher les noms de jours en commençant par lundi
                                            foreach ($joursSemaine as $jour) {
                                                echo "<th>$jour</th>";
                                            }
                                            // A quoi ca sert ??? Car ne fait rien de visible sur les pages et ca marche toujours sans
                                            ?>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <?php
                                            // Afficher les noms de jours en commençant par lundi
                                            foreach ($dbh->query("SELECT * FROM sae._jour WHERE id_logement = $id_logement ORDER BY id_jour" ) as $row) { // id_logement !!!!
                                                $jour = date("d", strtotime($row['date_jour']));
                                                $bdd_mois = date("m", strtotime($row['date_jour']));
                                                $bdd_annee = date("Y", strtotime($row['date_jour']));
                                                $bdd_date = date("Y-m-d", strtotime($row['date_jour']));
                                                $tarif_nuit_HT = $row['tarif_nuit_ht'];

                                                if ($bdd_date < date("Y-m-d")){
                                                    $dbh->query("UPDATE SAE._jour SET disponible = false, raison = 'date passe' WHERE id_logement = $id_logement and date_jour = '$bdd_date'");
                                                }

                                                if(date("d", strtotime($row['date_jour'])) == 1 && $bdd_mois == $mois && $bdd_annee == $annee){
                                                    // Remplir les premier jours du mois avec des cellules du mois precedent
                                                    for ($a = 1; $a < $jourSemaine; $a++) {
                                                        $jourMoisPrecedent = date("t", mktime(0, 0, 0, $mois - 1, 1, $annee)) - ($jourSemaine - $a) + 1;
                                                        echo "<td class='mois-precedent'>$jourMoisPrecedent</td>";
                                                    }
                                                }

                                                if ($bdd_mois == $mois && $bdd_annee == $annee){
                                                    if ($row['disponible'] == false){
                                                        ?><td class="indisponible"><?php echo $jour?></td><?php
                                                    } else {
                                                        ?><td class="disponible"><p><?php echo $jour ?></p><p class="cal_prix"><?php echo $tarif_nuit_HT . "€"?></p></td><?php
                                                    }
                                                }
                                                // Passer à une nouvelle ligne après chaque septième jour ou à la fin du mois
                                                if (($jour + $jourSemaine - 1) % 7 == 0) {
                                                    echo "</tr><tr>";
                                                }

                                                // Remplir les derniers jours du mois avec des cellules du mois suivant
                                                $dernierJour = date("N", mktime(0, 0, 0, $mois, $joursDansMois, $annee));
                                                

                                                if($jour == $joursDansMois && $bdd_mois == $mois && $bdd_annee == $annee){
                                                    //echo $joursDansMois;
                                                    for ($jour = $dernierJour + 1; $jour <= 7; $jour++) {
                                                        $jourMoisSuivant = $jour - $dernierJour;
                                                        echo "<td class='mois-suivant'>$jourMoisSuivant</td>";
                                                    }
                                                }
                                            
                                                        
                                            }
                                            

                                            ?>
                                        </tr>
                                    </tbody>
                                </table>

                                <div id="nav-mois-global">
                                    <div id='nav-mois-modif'>
                                        <a href="?idLogement=<?php echo $id_logement ?>&mois=<?php echo ($mois == 1) ? 12 : ($mois - 1); ?>&annee=<?php echo ($mois == 1) ? ($annee - 1) : $annee; ?>">Mois précédent</a>
                                        <h2><?php echo date("F Y", $premierJour); ?></h2>
                                        <a href="?idLogement=<?php echo $id_logement ?>&mois=<?php echo ($mois == 12) ? 1 : ($mois + 1); ?>&annee=<?php echo ($mois == 12) ? ($annee + 1) : $annee; ?>">Mois suivant</a>
                                    </div>
                                    <a href="./calendrier_ind.php?idLogement=<?php echo $id_logement ?>">Modifier</a>
                                </div>
                            </div>
                        </div>

// Création du planning pour chaque jour
$date_jour = new DateTime("2024-01-01");
$disponible = true;
$raison = 'enregistrement';

while ($date_jour->format('Y') <= date('Y') + 1) {
$date_string = $date_jour->format('Y-m-d');

$stmt = $dbh->prepare("INSERT INTO SAE._jour (date_jour, disponible, raison, tarif_nuit_HT, id_logement) VALUES (:date_jour, :disponible, :raison, :tarif_nuit_HT, :id_logement)");
$stmt->bindParam(':date_jour', $date_string); // Utilisation de la variable $date_string
$stmt->bindParam(':disponible', $disponible);
$stmt->bindParam(':raison', $raison);
$stmt->bindParam(':tarif_nuit_HT', $tarif_nuit_HT);
$stmt->bindParam(':id_logement', $row['id_logement']);

$stmt->execute();

$date_jour->add(new DateInterval('P1D'));
}