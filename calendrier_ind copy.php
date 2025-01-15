
<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier PHP</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            border-radius: 20px;
            border: 1px solid #ddd;
        }

        #calendrier-complet{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        #calendrier-mois{
            width: 50%;
        }

        

        th, td {
            padding: 15px;
            text-align: center;
        }

        td, thead {
            background-color: #f2f2f2;
            border-radius: 50px;
            width: 20px;
        }

        .div-calendar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }

        .div-calendar a {
            color: #333;
            text-decoration: none;
        }

        .div-calendar a:hover {
            color: #555;
        }

        .div-calendar h2 {
            color: #333;
            font-size: 24px;
            font-weight: normal;
            margin: 0;
        }


        form{
            
            width: 40%;

        }

        input[type="checkbox"]{
            display: none;
        }

        .no-arrow::-webkit-inner-spin-button, .no-arrow::-webkit-outer-spin-button{
            -webkit-appearance: none ;
      }

        
        .zone_prix > .cal_prix{
            width: 40px;
            height: 25px;
            text-align: center;
        }

        .zone_prix_mois > .cal_prix {
            width: 50%;
            height: 25px;
            text-align: center;
        }

        .ind{
            display: none;
        }
 
        


    </style>
</head>
<body>
<?php     
$id_logement = $_GET['idLogement'];

if (isset($_POST['dateDebut'])) {
    $date_debut_indisponibilite = date($_POST['dateDebut']);
    $date_fin_indisponibilite = date($_POST['dateFin']);
    foreach ($dbh->query("SELECT * FROM sae._jour WHERE id_logement = $id_logement") as $row) { // id_logement !!!!
            $dbh->query("UPDATE _jour SET disponible = false, raison = 'raison personnelle' WHERE date_jour >= '$date_debut_indisponibilite' and date_jour <= '$date_fin_indisponibilite' and  id_logement = $id_logement"); // voir si on garde raison
    }
}

// Récupérer le mois et l'année à afficher
if (isset($_GET['mois']) && isset($_GET['annee'])) {
    $mois = $_GET['mois'];
    $annee = $_GET['annee'];
} else {
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
                    <form action="modifierLogement.php?idLogement=<?php echo $id_logement ?>" method="post"><?php
                    // Afficher les noms de jours en commençant par lundi
                    foreach ($dbh->query("SELECT * FROM sae._jour WHERE id_logement = $id_logement ORDER BY id_jour") as $row) { // id_logement !!!!
                        $jour = date("d", strtotime($row['date_jour']));
                        $bdd_mois = date("m", strtotime($row['date_jour']));
                        $bdd_annee = date("Y", strtotime($row['date_jour']));
                        $tarif_nuit_ht = $row['tarif_nuit_ht'];

                        if(date("d", strtotime($row['date_jour'])) == 1 && $bdd_mois == $mois && $bdd_annee == $annee){
                            // Remplir les premier jours du mois avec des cellules du mois precedent
                            for ($a = 1; $a < $jourSemaine; $a++) {
                                $jourMoisPrecedent = date("t", mktime(0, 0, 0, $mois - 1, 1, $annee)) - ($jourSemaine - $a) + 1;
                                ?><td class='mois-precedent'><?php echo $jourMoisPrecedent?></td><?php
                            }
                        }

                        if ($bdd_mois == $mois && $bdd_annee == $annee){?>
                            <td class="<?php (print_r(($row['disponible'])? "disponible" : "indisponible")); ?>" onclick="toggleCheckbox('<?php echo $row['date_jour'] ?>', this)">
                                <label for="<?php echo $jour ?>" onselectstart="return false;" ondblclick="preventLabelSelection()"><?php echo $jour ?></label><?php

                                if ($row['disponible'] == true){?>
                                    <input type="checkbox" name="check_ind<?php echo $row['date_jour'] ?>" id="<?php echo $row['date_jour'] ?>" value="disponible" ><?php /* inverse car indisponible quand il click*/
                                    
                                } else {?>
                                    <input type="checkbox" name="check_ind<?php echo $row['date_jour'] ?>" id="<?php echo $row['date_jour'] ?>" value="indisponible" checked><?php
                                }?>

                                <div class="zone_prix"><!-- voir si ne pas mettre pour les indispo -->
                                    <input type="number" class="cal_prix no-arrow" name="calc_prix<?php echo $row['date_jour'] ?>" value=<?php echo "$tarif_nuit_ht" ?> required onclick="stopPropagation(event)" />€
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
                                ?><td class='next-button'><?php echo $jourMoisSuivant ?></td><?php
                            }
                        }
                        

                        
                                
                    }
                    
                    ?>
                </tr>
            </tbody>
        </table>

        <div class="div-calendar">
        <div class="pre-button" onclick="preMonth()"><span>aaaaaaa</span></div>
                <h2><?php echo date("F Y", $premierJour); ?></h2>
            <div class="next-button" onclick="nextMonth()"><span>aaaaaaa</span></div>
        </div>
    </div>
    
    <div>
        
        
        <div class="zone_prix_mois"> <!-- pop up ? -->
            <label for="calc_prix_mois">Prix par jours sur un mois (optionnel)</label>
            <input type="number" class="cal_prix no-arrow" name="calc_prix_mois" />€
        </div>

        <div>
            <input type="submit" name="submit" value="Appliquer" />
            <input type="reset" name="Annuler" value="Annuler" onclick="return confirm('Etes vous sur de vouloir annuler ?')"/>
        </div>
        
    </div>
    
    </form>




<?php






?>


</div>
</body>

<script>
    var next = document.getElementsByClassName('next-button');
    console.log(next);

    var today = new Date(),
        year = today.getFullYear(),
        month = today.getMonth(),
        monthTag =["Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre"],
        day = today.getDate(),
        days = document.getElementsByTagName('td'),
        selectedDay,
        setDate,
        daysLen = days.length;


    function nextMonth() {
        if(month >= 11){
            month = 0;
            year =  year + 1; 
        }else{é
            month = month + 1;
        }
        console.log(month);
        console.log(year);

    };

    function preMonth() {
        if(month < 1){ 
            month = 11;
            year = year - 1; 
        }else{
            month = month - 1;
        }
        console.log(month);
        console.log(year);
    };
/*
    document.addEventListener('DOMContentLoaded', function(){
    

    function Calendar(selector, options) {
        this.options = options;
        this.draw();
    }
    
    Calendar.prototype.draw  = function() {
        this.getCookie('selected_day');
        this.getOptions();
        this.drawDays();
        var that = this,
            pre = document.getElementsByClassName('pre-button'),
            next = document.getElementsByClassName('next-button');
            
            pre[0].addEventListener('click', function(){that.preMonth(); });
            next[0].addEventListener('click', function(){that.nextMonth(); });
        while(daysLen--) {
            days[daysLen].addEventListener('click', function(){that.clickDay(this); });
        }
    };
    
    
    
    Calendar.prototype.clickDay = function(o) {
        var selected = document.getElementsByClassName("selected"),
            len = selected.length;
        if(len !== 0){
            selected[0].className = "";
        }
        o.className = "selected";
        selectedDay = new Date(year, month, o.innerHTML);
        this.drawHeader(o.innerHTML);
        this.setCookie('selected_day', 1);
        
    };
    
    
    
    
    
        
}, false);

*/





    function preventLabelSelection() {
        return false;
    }

    function toggleCheckbox(checkboxId, element) {
        var checkbox = document.getElementById(checkboxId);
        checkbox.checked = !checkbox.checked;
        var checkbox_class = element.className;
        element.className = checkbox_class == 'disponible' ? "indisponible" : "disponible";
        element.value = element.checked ? "indisponible" :  "disponible";

    }

    function stopPropagation(event) {
        event.stopPropagation();
    }
</script>

</html>








