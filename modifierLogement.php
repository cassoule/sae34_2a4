<?php
// Ouverture de la session pour stocker des informations dans les cookies
session_start();

ob_start();

// Chargement de la base de données
include ('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Fonction pour éviter certaines failles informatiques
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Récupération de l'id du logement
$id_logement = $_GET['idLogement'];
$id_logement_origine = $_GET['idLogement'];

try {
    // Récupération du logement à modifiés dans la base de données
    $tableau = $dbh->query("SELECT * from SAE._logement WHERE id_logement=$id_logement");
    $row = $tableau->fetch(PDO::FETCH_ASSOC);
    
    // Informations actuels du logement dans la base de données
    $libelle = $row['libelle_logement'];
    $accroche = $row['accroche'];
    $description = $row['description_detaille'];
    $max_personnes = $row['max_personnes'];
    $nature_logement = $row['nature_logement'];
    $type_logement = $row['type_logement'];
    $surface = $row['surface'];
    $nb_chambres = $row['nb_chambres'];
    $nb_lits_simple = $row['nb_lits_simple'];
    $nb_lits_double = $row['nb_lits_double'];
    $nb_salle_de_bain = $row['nb_salle_de_bain'];
    $id_adresse = $row['id_adresse'];
    

    $adressesql = $dbh->query("SELECT * from SAE._adresse where id_adresse=$id_adresse");
    $row = $adressesql->fetch(PDO::FETCH_ASSOC);

    $code_postal = $row['code_postal'];
    $adresse = $row['adresse'];
    if ($row['complement_adresse'] != '') {
        $complement_adresse = $row['complement_adresse'];
    }
    else {
        $complement_adresse = NULL;
    }
    $ville = $row['ville'];

    // Vérifiez que le formulaire a été soumis
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['libelle'])) && (isset($_POST['accroche'])) && (isset($_POST['description']))) {
        // Récupération de toutes les informations du logement
        $libelle = test_input($_POST['libelle']);
        $accroche = test_input($_POST['accroche']);
        $description = test_input($_POST['description']);
        $max_personnes = test_input($_POST['max_personnes']);
        $nature_logement = test_input($_POST['nature_logement']);
        if ($nature_logement === 'Autre') {
            $nature_logement = test_input($_POST['nature_logement_autre']);
        }
        $type_logement = test_input($_POST['type_logement']);
        if ($type_logement === 'Autre') {
            $type_logement = test_input($_POST['type_logement_autre']);
        }
        $surface = test_input($_POST['surface']);
        $nb_chambres = test_input($_POST['nb_chambres']);
        $nb_lits_simple = test_input($_POST['nb_lits_simple']);
        $nb_lits_double = test_input($_POST['nb_lits_double']);
        $nb_salle_de_bain = test_input($_POST['nb_salle_de_bain']);
        $code_postal = test_input($_POST['code_postal']);
        $adresse = test_input($_POST['adresse']);
        $complement_adresse = test_input($_POST['complement_adresse']);
        $ville = test_input($_POST['ville']);

        // Récupération des aménagements du logement
        $jardin = isset($_POST['jardin']) ? 1 : 0;
        $balcon = isset($_POST['balcon']) ? 1 : 0;
        $terrasse = isset($_POST['terrasse']) ? 1 : 0;
        $parking_prive = isset($_POST['parking_prive']) ? 1 : 0;
        $parking_public = isset($_POST['parking_public']) ? 1 : 0;

        // Récupération des installations offertes du logement
        $climatisation = isset($_POST['climatisation']) ? 1 : 0;
        $piscine = isset($_POST['piscine']) ? 1 : 0;
        $jacuzzi = isset($_POST['jacuzzi']) ? 1 : 0;
        $hammam = isset($_POST['hammam']) ? 1 : 0;
        $sauna = isset($_POST['sauna']) ? 1 : 0;

        // Récupération des équipements proposés par le logement 
        $television = isset($_POST['television']) ? 1 : 0;
        $lave_linge = isset($_POST['lave_linge']) ? 1 : 0;
        $barbecue = isset($_POST['barbecue']) ? 1 : 0;
        $wifi = isset($_POST['wifi']) ? 1 : 0;
        $seche_linge = isset($_POST['seche_linge']) ? 1 : 0;
        $lave_vaisselle = isset($_POST['lave_vaisselle']) ? 1 : 0;

        // Récupération des services complémentaires
        $linge_maison = isset($_POST['linge_maison']) && $_POST['linge_maison'] > 0 ? $_POST['linge_maison'] : 0;
        $produit_menage = isset($_POST['produit_menage']) && $_POST['produit_menage'] > 0 ? $_POST['produit_menage'] : 0;
        $navette = isset($_POST['navette']) && $_POST['navette'] > 0 ? $_POST['navette'] : 0;
        $produit_toilette = isset($_POST['produit_toilette']) && $_POST['produit_toilette'] > 0 ? $_POST['produit_toilette'] : 0;
        $petit_dejeuner = isset($_POST['petit_dejeuner']) && $_POST['petit_dejeuner'] > 0 ? $_POST['petit_dejeuner'] : 0;
        $autre_service = isset($_POST['autre_service']) && $_POST['autre_service'] > 0 ? $_POST['autre_service'] : 0;

        // Récupération des charges additionelles
        $menage = isset($_POST['menage']) && $_POST['menage'] > 0 ? $_POST['menage'] : 0;
        $taxe_sejour = isset($_POST['taxe_sejour']) && $_POST['taxe_sejour'] > 0 ? $_POST['taxe_sejour'] : 0;
        $animaux = isset($_POST['animaux']) && $_POST['animaux'] > 0 ? $_POST['animaux'] : 0;
        $visiteurs = isset($_POST['visiteurs']) && $_POST['visiteurs'] > 0 ? $_POST['visiteurs'] : 0;
        $autre_charge = isset($_POST['autre_charge']) && $_POST['autre_charge'] > 0 ? $_POST['autre_charge'] : 0;

        // Variable pour suivre s'il y a des erreurs
        $hasErrors = false;

        // Traiter des cas d'erreurs
        if ($max_personnes < 0 || $surface < 0 || $nb_chambres < 0 || $nb_lits_simple < 0 || $nb_lits_double < 0 || $nb_salle_de_bain < 0) {
            $hasErrors = true;
        }

        // Traiter des cas d'erreurs
        if (strlen($code_postal) !== 5) {
            $hasErrors = true;
        }

        // Si il n'y a pas d'erreur
        if (!$hasErrors) {
            // Enregistrement de l'adresse de l'utilisateur
            $stmtAdresse = $dbh->prepare("INSERT INTO SAE._adresse (code_postal, adresse, complement_adresse, ville) VALUES (:code_postal, :adresse, :complement_adresse, :ville)");
            $stmtAdresse->bindParam(':code_postal', $code_postal, PDO::PARAM_INT);
            $stmtAdresse->bindParam(':adresse', $adresse, PDO::PARAM_STR);
            $stmtAdresse->bindParam(':complement_adresse', $complement_adresse, PDO::PARAM_STR);
            $stmtAdresse->bindParam(':ville', $ville, PDO::PARAM_STR);

            // Si l'adresse est correctement enregistrer
            if ($stmtAdresse->execute()) {
                $id_proprietaire = $_SESSION['idUtilisateur'];
                $avis_logement = 5;

                // Récupération de l'id de l'adresse
                $stmtAdresse = $dbh->prepare("SELECT id_adresse FROM SAE._adresse WHERE code_postal = :code_postal AND adresse = :adresse AND complement_adresse = :complement_adresse AND ville = :ville");
                $stmtAdresse->bindParam(':code_postal', $code_postal, PDO::PARAM_INT);
                $stmtAdresse->bindParam(':adresse', $adresse, PDO::PARAM_STR);
                $stmtAdresse->bindParam(':complement_adresse', $complement_adresse, PDO::PARAM_STR);
                $stmtAdresse->bindParam(':ville', $ville, PDO::PARAM_STR);
                $stmtAdresse->execute();
                $row = $stmtAdresse->fetch(PDO::FETCH_ASSOC);

                // Enregistrement du logement de l'utilisateur dans la base de donnée
                $stmtLogement = $dbh->prepare("INSERT INTO SAE._logement (libelle_logement, accroche, description_detaille, max_personnes, nature_logement, type_logement, surface, nb_chambres, nb_lits_simple, nb_lits_double, nb_salle_de_bain, avis_logement_total, est_actif, id_proprietaire, id_adresse) VALUES (:libelle_logement, :accroche, :description_detaille, :max_personnes, :nature_logement, :type_logement, :surface, :nb_chambres, :nb_lits_simple, :nb_lits_double, :nb_salle_de_bain, :avis_logement_total, true, :id_proprietaire, :id_adresse)");

                // Liens pour les paramètres
                $stmtLogement->bindParam(':libelle_logement', $libelle, PDO::PARAM_STR);
                $stmtLogement->bindParam(':accroche', $accroche, PDO::PARAM_STR);
                $stmtLogement->bindParam(':description_detaille', $description, PDO::PARAM_STR);
                $stmtLogement->bindParam(':max_personnes', $max_personnes, PDO::PARAM_INT);
                $stmtLogement->bindParam(':nature_logement', $nature_logement, PDO::PARAM_STR);
                $stmtLogement->bindParam(':type_logement', $type_logement, PDO::PARAM_STR);
                $stmtLogement->bindParam(':surface', $surface, PDO::PARAM_INT);
                $stmtLogement->bindParam(':nb_chambres', $nb_chambres, PDO::PARAM_INT);
                $stmtLogement->bindParam(':nb_lits_simple', $nb_lits_simple, PDO::PARAM_INT);
                $stmtLogement->bindParam(':nb_lits_double', $nb_lits_double, PDO::PARAM_INT);
                $stmtLogement->bindParam(':nb_salle_de_bain', $nb_salle_de_bain, PDO::PARAM_INT);
                $stmtLogement->bindParam(':avis_logement_total', $avis_logement, PDO::PARAM_INT);
                $stmtLogement->bindParam(':id_proprietaire', $id_proprietaire, PDO::PARAM_INT);
                $stmtLogement->bindParam(':id_adresse', $row['id_adresse'], PDO::PARAM_INT);

                if ($stmtLogement->execute()) {
                    // Récupération de l'id du logement
                    $stmtLogement = $dbh->prepare("SELECT id_logement FROM SAE._logement WHERE libelle_logement = :libelle_logement AND id_adresse = :id_adresse AND accroche = :accroche AND description_detaille = :description_detaille ORDER BY id_logement DESC");
                    $stmtLogement->bindParam(':libelle_logement', $libelle, PDO::PARAM_STR);
                    $stmtLogement->bindParam(':accroche', $accroche, PDO::PARAM_STR);
                    $stmtLogement->bindParam(':description_detaille', $description, PDO::PARAM_STR);
                    $stmtLogement->bindParam(':id_adresse', $row['id_adresse'], PDO::PARAM_INT);
                    $stmtLogement->execute();
                    $row = $stmtLogement->fetch(PDO::FETCH_ASSOC);
                    $id_logement_new = $row['id_logement'];

                    // Enregistrement des aménagements du logement
                    if ($jardin == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._contient (id_logement, nom_amenagement) VALUES (:id_logement, :nom_amenagement)");
                        $jardin = 'Jardin';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_amenagement', $jardin, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }      
                    if ($balcon == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._contient (id_logement, nom_amenagement) VALUES (:id_logement, :nom_amenagement)");
                        $balcon = 'Balcon';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_amenagement', $balcon, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($terrasse == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._contient (id_logement, nom_amenagement) VALUES (:id_logement, :nom_amenagement)");
                        $terrasse = 'Terrasse';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_amenagement', $terrasse, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }    
                    if ($parking_prive == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._contient (id_logement, nom_amenagement) VALUES (:id_logement, :nom_amenagement)");
                        $parking_prive = 'Parking prive';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_amenagement', $parking_prive, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($parking_public == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._contient (id_logement, nom_amenagement) VALUES (:id_logement, :nom_amenagement)");
                        $parking_public = 'Parking public';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_amenagement', $parking_public, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }

                    // Enregistrement des installations offertes du logement
                    if ($climatisation == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._possede (id_logement, nom_installation) VALUES (:id_logement, :nom_installation)");
                        $climatisation = 'Climatisation';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_installation', $climatisation, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($piscine == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._possede (id_logement, nom_installation) VALUES (:id_logement, :nom_installation)");
                        $piscine = 'Piscine';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_installation', $piscine, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($jacuzzi == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._possede (id_logement, nom_installation) VALUES (:id_logement, :nom_installation)");
                        $jacuzzi = 'Jacuzzi';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_installation', $jacuzzi, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($hammam == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._possede (id_logement, nom_installation) VALUES (:id_logement, :nom_installation)");
                        $hammam = 'Hammam';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_installation', $hammam, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($sauna == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._possede (id_logement, nom_installation) VALUES (:id_logement, :nom_installation)");
                        $sauna = 'Sauna';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_installation', $sauna, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }

                    // Enregistrement des équipements proposés par le logement
                    if ($television == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._equipe (id_logement, nom_equipement) VALUES (:id_logement, :nom_equipement)");
                        $television = 'Television';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_equipement', $television, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($lave_linge == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._equipe (id_logement, nom_equipement) VALUES (:id_logement, :nom_equipement)");
                        $lave_linge = 'Lave-linge';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_equipement', $lave_linge, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($barbecue == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._equipe (id_logement, nom_equipement) VALUES (:id_logement, :nom_equipement)");
                        $barbecue = 'Barbecue';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_equipement', $barbecue, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($wifi == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._equipe (id_logement, nom_equipement) VALUES (:id_logement, :nom_equipement)");
                        $wifi = 'Wifi';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_equipement', $wifi, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($seche_linge == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._equipe (id_logement, nom_equipement) VALUES (:id_logement, :nom_equipement)");
                        $seche_linge = 'Seche-linge';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_equipement', $seche_linge, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }
                    if ($lave_vaisselle == 1) {
                        $stmtAmenagement = $dbh->prepare("INSERT INTO SAE._equipe (id_logement, nom_equipement) VALUES (:id_logement, :nom_equipement)");
                        $lave_vaisselle = 'Lave-vaisselle';
                        $stmtAmenagement->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtAmenagement->bindParam(':nom_equipement', $lave_vaisselle, PDO::PARAM_STR);
                        $stmtAmenagement->execute();
                    }

                    // Enregistrement des services complémentaires du logement
                    if ($linge_maison > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._service (nom_service, prix_service_HT, id_logement) VALUES (:nom_service, :prix_service_HT, :id_logement)");
                        $prix_linge_maison = $linge_maison;
                        $linge_maison = 'Linge de maison';
                        $stmtService->bindParam(':nom_service', $linge_maison, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_service_HT', $prix_linge_maison, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($produit_menage > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._service (nom_service, prix_service_HT, id_logement) VALUES (:nom_service, :prix_service_HT, :id_logement)");
                        $prix_produit_menage = $produit_menage;
                        $produit_menage = 'Produit de Ménage';
                        $stmtService->bindParam(':nom_service', $produit_menage, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_service_HT', $prix_produit_menage, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($navette > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._service (nom_service, prix_service_HT, id_logement) VALUES (:nom_service, :prix_service_HT, :id_logement)");
                        $prix_navette = $navette;
                        $navette = 'Navette';
                        $stmtService->bindParam(':nom_service', $navette, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_service_HT', $prix_navette, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($produit_toilette > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._service (nom_service, prix_service_HT, id_logement) VALUES (:nom_service, :prix_service_HT, :id_logement)");
                        $prix_produit_toilette = $produit_toilette;
                        $produit_toilette = 'Produit de toilette';
                        $stmtService->bindParam(':nom_service', $produit_toilette, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_service_HT', $prix_produit_toilette, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($petit_dejeuner > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._service (nom_service, prix_service_HT, id_logement) VALUES (:nom_service, :prix_service_HT, :id_logement)");
                        $prix_petit_dejeuner = $petit_dejeuner;
                        $petit_dejeuner = 'Petit dejeuner';
                        $stmtService->bindParam(':nom_service', $petit_dejeuner, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_service_HT', $prix_petit_dejeuner, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($autre_service > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._service (nom_service, prix_service_HT, id_logement) VALUES (:nom_service, :prix_service_HT, :id_logement)");
                        $prix_autre_service = $autre_service;
                        $autre_service = 'Autre service';
                        $stmtService->bindParam(':nom_service', $autre_service, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_service_HT', $prix_autre_service, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }

                    // Enregistrement des charges additionelles
                    if ($menage > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._charge (nom_charge, prix_charge_HT, id_logement) VALUES (:nom_charge, :prix_charge_HT, :id_logement)");
                        $prix_menage = $menage;
                        $menage = 'Menage';
                        $stmtService->bindParam(':nom_charge', $menage, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_charge_HT', $prix_menage, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($taxe_sejour > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._charge (nom_charge, prix_charge_HT, id_logement) VALUES (:nom_charge, :prix_charge_HT, :id_logement)");
                        $prix_taxe_sejour = $taxe_sejour;
                        $taxe_sejour = 'Taxe sejour';
                        $stmtService->bindParam(':nom_charge', $taxe_sejour, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_charge_HT', $prix_taxe_sejour, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($animaux > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._charge (nom_charge, prix_charge_HT, id_logement) VALUES (:nom_charge, :prix_charge_HT, :id_logement)");
                        $prix_animaux = $animaux;
                        $animaux = 'Animaux';
                        $stmtService->bindParam(':nom_charge', $animaux, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_charge_HT', $prix_animaux, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($visiteurs > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._charge (nom_charge, prix_charge_HT, id_logement) VALUES (:nom_charge, :prix_charge_HT, :id_logement)");
                        $prix_visiteurs = $visiteurs;
                        $visiteurs = 'Visiteurs';
                        $stmtService->bindParam(':nom_charge', $visiteurs, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_charge_HT', $prix_visiteurs, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }
                    if ($autre_charge > 0) {
                        $stmtService = $dbh->prepare("INSERT INTO SAE._charge (nom_charge, prix_charge_HT, id_logement) VALUES (:nom_charge, :prix_charge_HT, :id_logement)");
                        $prix_autre_charge = $autre_charge;
                        $autre_charge = 'Autre charge';
                        $stmtService->bindParam(':nom_charge', $autre_charge, PDO::PARAM_STR);
                        $stmtService->bindParam(':prix_charge_HT', $prix_autre_charge, PDO::PARAM_STR);
                        $stmtService->bindParam(':id_logement', $row['id_logement'], PDO::PARAM_INT);
                        $stmtService->execute();
                    }

                    // Préparation de la base de données pour l'insertion de nouvelles images
                    $cpt = 0;
                    $images = [];
                    
                    foreach ($_FILES as $image) {
                        $cpt = $cpt + 1;
                        if ($image['size'] > 0) {
                            $newImage = 'img/' . time() . '_idLogement_' . $row['id_logement'] . $cpt . "_" . $image['name'];
                            copy($image['tmp_name'], $newImage);
                            $images[] = [$newImage, $image['name']];
                        }
                    }

                    // Enregistrement des images du logement dans la base de donnée
                    foreach ($images as $image) {
                        $stmt = $dbh->prepare("INSERT INTO SAE._image (nom_image, lien_image, id_compte, id_logement_image) VALUES (:nom_image, :chemin_image, null, :id_logement)");
                        $stmt->bindParam(':nom_image', $image[1]);
                        $stmt->bindParam(':chemin_image', $image[0]);
                        $stmt->bindParam(':id_logement', $row['id_logement']);

                        $stmt->execute();
                    }
                }
            }
        }

        // Renvoies vers la page des logements de l'utilisateur et arrête le script de la page
        header("Location: ./previsualiserLogement.php?id_logement=$id_logement_new&type_demande=modifier&id_modifier=$id_logement_origine");
        exit();
    }
}
// Pour gérer les cas d'erreur de la base de données
catch (PDOException $e) {
    echo "Erreur lors de l'enregistrement des modifications : " . $e->getMessage();
}

ob_end_flush();
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

        <div class="background_svg bs_top">
            <div class="back_top_svg">
                <svg class="top_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.5" d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3" d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.2" d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z" fill="#0777DE"/>
                </svg>

                <svg class="top_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.1" d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z" fill="#0777DE"/>
                </svg>
            </div>
        </div>

        <!-- Affichage des informations principales de la page -->
        <main class="both-svg-main-ajout">
            <h1 class="ajout-titre">Modifier le Logement</h1>

            <form class="enregistrement" action="/modifierLogement.php?idLogement=<?php echo $_GET['idLogement'] ?>" method="post" enctype="multipart/form-data">
                <div class="container-ajout">
                    <div class="container-ajout-detail cad-left">
                        <div class="ajout-card">
                            <div id="detail">
                                <h2 class="ajout-mini-titre">Détails du logement</h2>

                                <div class="row row-tinier">
                                    <input type="text" name="libelle" placeholder="Ajoutez un libellé" required value="<?php echo $libelle ?>">
                                </div>

                                <div class="row row-tinier">
                                    <input type="text" name="accroche" placeholder="Accroche du logement" required value="<?php echo $accroche ?>">
                                </div>

                                <div class="row row-tinier">
                                    <textarea name="description" placeholder="Ajoutez ici une description détaillé du logement" required><?php echo $description ?></textarea>
                                </div>

                                <div class="row row-tinier">
                                    <input type="number" name="nb_lits_simple" placeholder="Combien de lit simple ?" required  min="0" value="<?php echo $nb_lits_simple ?>">
                                    <input type="number" name="nb_lits_double" placeholder="Combien de lit double ?" required min="0" value="<?php echo $nb_lits_double ?>">
                                </div>

                                <div class="row row-tinier">
                                    <input type="number" name="nb_chambres" placeholder="Combien de chambre ?" required min="0" value="<?php echo $nb_chambres ?>">
                                    <input type="number" name="nb_salle_de_bain" placeholder="Combien de salle de bain ?" required min="0" value="<?php echo $nb_salle_de_bain ?>">
                                </div>

                                <div class="row row-tinier">
                                    <input type="number" name="surface" placeholder="Surface du logement ?" required min="0" value="<?php echo $surface; ?>">
                                    
                                    <select name="type_logement" required>
                                        <option value="">Type du logement ?</option>
                                        <option value="Studio">Studio</option>
                                        <option value="Loft">Loft</option>
                                        <option value="T1">T1</option>
                                        <option value="T1 Bis">T1 Bis</option>
                                        <option value="T2">T2</option>
                                        <option value="T3">T3</option>
                                        <option value="T4">T4</option>
                                        <option value="Penthouse">Penthouse</option>
                                        <option value="Duplex">Duplex</option>
                                        <option value="Triplex">Triplex</option>
                                        <option value="Individuelle">Individuelle</option>
                                        <option value="Mitoyenne">Mitoyenne</option>                                
                                    </select>
                                </div>

                                <div class="row row-tinier">
                                    <select name="nature_logement" required>
                                        <option value="">Quelle est la nature du logement ?</option>
                                        <option value="Appartement">Appartement</option>
                                        <option value="Maison">Maison</option>
                                        <option value="Cabane">Cabane</option>
                                        <option value="Villa d'exception">Villa d'exception</option>
                                        <option value="Appartement de luxe">Appartement de luxe</option>
                                        <option value="Manoir">Manoir</option>
                                        <option value="Chalet">Chalet</option>
                                        <option value="Bungalow">Bungalow</option>
                                        <option value="Château">Château</option>
                                        <option value="Yourte">Yourte</option>
                                        <option value="Maison flottante">Maison flottante</option>
                                    </select>
                                    <input type="number" name="max_personnes" placeholder="Combien de personnes ?" required min="0" value="<?php echo $max_personnes ?>">
                                </div>
                            </div>
                        </div>

                        <div class="ajout-card">
                            <h2 class="ajout-mini-titre">Images</h2>

                            <div id="image">
                                <div class="row row-tinier">
                                    <label for="image1">Image 1 :</label>
                                    <input type="file" name="image1" accept=".jpg, .jpeg, .png, .gif" required>
                                </div>

                                <div class="row row-tinier">
                                    <label for="image2">Image 2 :</label>
                                    <input type="file" name="image2" accept=".jpg, .jpeg, .png, .gif" requiered>
                                </div>

                                <div class="row row-tinier">
                                    <label for="image3">Image 3 :</label>
                                    <input type="file" name="image3" accept=".jpg, .jpeg, .png, .gif" requiered>
                                </div>

                                <div class="row row-tinier">
                                    <label for="image4">Image 4 :</label>
                                    <input type="file" name="image4" accept=".jpg, .jpeg, .png, .gif">
                                </div>

                                <div class="row row-tinier">
                                    <label for="image5">Image 5 :</label>
                                    <input type="file" name="image5" accept=".jpg, .jpeg, .png, .gif">
                                </div>

                                <div class="row row-tinier">
                                    <label for="image6">Image 6 :</label>
                                    <input type="file" name="image6" accept=".jpg, .jpeg, .png, .gif">
                                </div>

                                <div class="row row-tinier">
                                    <label for="image7">Image 7 :</label>
                                    <input type="file" name="image7" accept=".jpg, .jpeg, .png, .gif">
                                </div>
                            </div>
                        </div>

                        <div class="ajout-card">
                            <div id="adresse">
                                <h2 class="ajout-mini-titre">Adresse</h2>

                                <div class="row row-tinier">
                                    <input type="text" name="adresse" placeholder="N° et nom de rue" required value="<?php echo $adresse; ?>">
                                </div>

                                <div class="row row-tinier">
                                    <input type="number" name="code_postal" placeholder="Code Postal" required min="0" value="<?php echo $code_postal ?>">
                                    <input type="text" name="ville" placeholder="Ville" required value="<?php echo $ville; ?>">
                                </div>

                                <div class="row row-tinier">
                                    <input type="text" name="complement_adresse" placeholder="Complément d'adresse" value="<?php echo $complement_adresse ?>">
                                </div>
                            </div>
                        </div>

                        <input type="submit" value="Confirmer">
                    </div>

                    <div class="container-ajout-detail cad-right">
                        <div class="ajout-card">
                            <h2 class="ajout-mini-titre">Aménagements</h2>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('jardinCheckbox', this)">
                                    <label for="jardin" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-tree"></i> Jardin</label>
                                    <input type="checkbox" name="jardin" id="jardinCheckbox" value="1">
                                </div>

                                <div class="card-ajout" onclick="toggleCheckbox('balconCheckbox', this)">
                                    <label for="balcon" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-person-through-window"></i>  Balcon</label>
                                    <input type="checkbox" name="balcon" id="balconCheckbox" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('terrasseCheckbox', this)">
                                    <label for="terrasse" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-table"></i>  Terrasse</label>
                                    <input type="checkbox" name="terrasse" id="terrasseCheckbox" value="1">
                                </div>
                            </div>
                                
                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('parking_priveCheckbox', this)">
                                    <label for="parking_prive" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-square-parking"></i>  Parking Privé</label>
                                    <input type="checkbox" name="parking_prive" id="parking_priveCheckbox" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('parking_publicCheckbox', this)">
                                    <label for="parking_public" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-square-parking"></i>  Parking Public</label>
                                    <input type="checkbox" name="parking_public" id="parking_publicCheckbox" value="1">
                                </div>
                            </div>
                        </div>

                        <div class="ajout-card">
                            <h2 class="ajout-mini-titre">Installations offertes</h2>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('climatisationCheckbox', this)">
                                    <label for="climatisation" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-wind"></i>  Climatisation</label>
                                    <input type="checkbox" name="climatisation" id="climatisationCheckbox" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('piscineCheckbox', this)">
                                    <label for="piscine" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-water-ladder"></i>  Piscine</label>
                                    <input type="checkbox" name="piscine" id="piscineCheckbox" value="1">
                                </div>

                                <div class="card-ajout" onclick="toggleCheckbox('jacuzziCheckbox', this)">
                                    <label for="jacuzzi" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-hot-tub-person"></i>  Jacuzzi</label>
                                    <input type="checkbox" name="jacuzzi" id="jacuzziCheckbox" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('hammamCheckbox', this)">
                                    <label for="hammam" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-temperature-three-quarters"></i>  Hammam</label>
                                    <input type="checkbox" name="hammam" id="hammamCheckbox" value="1">
                                </div>

                                <div class="card-ajout" onclick="toggleCheckbox('saunaCheckbox', this)">
                                    <label for="sauna" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-bath"></i>  Sauna</label>
                                    <input type="checkbox" name="sauna" id="saunaCheckbox" value="1">
                                </div>
                            </div>
                        </div>

                        <div class="ajout-card">
                            <h2 class="ajout-mini-titre">Equipement proposés</h2>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('televisionCheckbox', this)">
                                    <label for="television" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-tv"></i>  Télevision</label>
                                    <input type="checkbox" name="television" id="televisionCheckbox" value="1">
                                </div>

                                <div class="card-ajout" onclick="toggleCheckbox('lave_lingeCheckbox', this)">
                                    <label for="lave_linge" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-soap"></i>  Lave-linge</label>
                                    <input type="checkbox" name="lave_linge" id="lave_lingeCheckbox" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('barbecueCheckbox', this)">
                                    <label for="barbecue" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-drumstick-bite"></i>  Barbecue</label>
                                    <input type="checkbox" name="barbecue" id="barbecueCheckbox" value="1">
                                </div>

                                <div class="card-ajout" onclick="toggleCheckbox('wifiCheckbox', this)">
                                    <label for="wifi" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-wifi"></i>  Wifi</label>
                                    <input type="checkbox" name="wifi" id="wifiCheckbox" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('seche_lingeCheckbox', this)">
                                    <label for="seche_linge" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-shirt"></i>  Sèche-linge</label>
                                    <input type="checkbox" name="seche_linge" id="seche_lingeCheckbox" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout" onclick="toggleCheckbox('lave_vaiselleCheckbox', this)">
                                    <label for="lave_vaiselle" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-utensils"></i>  Lave-vaisselle</label>
                                    <input type="checkbox" name="lave_vaiselle" id="lave_vaiselleCheckbox" value="1">
                                </div>
                            </div>
                        </div>

                        <div class="ajout-card">
                            <h2 class="ajout-mini-titre">Services complémentaires</h2>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('linge_maisonCheckbox', this)">
                                    <label for="linge_maison" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-shirt"></i>  Linge de maison</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('linge_maisonCheckbox')">-</button>
                                        <input type="number" name="linge_maison" id="linge_maisonCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('linge_maisonCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('produit_menageCheckbox', this)">
                                    <label for="produit_menage" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-pump-soap"></i>  Produit de ménage</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('produit_menageCheckbox')">-</button>
                                        <input type="number" name="produit_menage" id="produit_menageCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('produit_menageCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('navetteCheckbox', this)">
                                    <label for="navette" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-bus"></i>  Navette</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('navetteCheckbox')">-</button>
                                        <input type="number" name="navette" id="navetteCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('navetteCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('petit_dejeunerCheckbox', this)">
                                    <label for="petit_dejeuner" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-mug-saucer"></i>  Petit Déjeuner</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('petit_dejeunerCheckbox')">-</button>
                                        <input type="number" name="petit_dejeuner" id="petit_dejeunerCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('petit_dejeunerCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('autre_serviceCheckbox', this)">
                                    <label for="autre_service" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-location-dot"></i>  Autre Service</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('autre_serviceCheckbox')">-</button>
                                        <input type="number" name="autre_service" id="autre_serviceCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('autre_serviceCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ajout-card">
                            <h2 class="ajout-mini-titre">Charges Additionelles</h2>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('menageCheckbox', this)">
                                    <label for="menage" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-broom"></i>  Ménage</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('menageCheckbox')">-</button>
                                        <input type="number" name="menage" id="menageCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('menageCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('taxe_sejourCheckbox', this)">
                                    <label for="taxe_sejour" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-sack-dollar"></i>  Taxe de séjour</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('taxe_sejourCheckbox')">-</button>
                                        <input type="number" name="taxe_sejour" id="taxe_sejourCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('taxe_sejourCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('animauxCheckbox', this)">
                                    <label for="animaux" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-paw"></i>  Animaux</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('animauxCheckbox')">-</button>
                                        <input type="number" name="animaux" id="animauxCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('animauxCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('visiteursCheckbox', this)">
                                    <label for="visiteurs" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-person"></i>  Visiteurs</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('visiteursCheckbox')">-</button>
                                        <input type="number" name="visiteurs" id="visiteursCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('visiteursCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-ajout-number" onclick="toggleCheckbox('autre_chargeCheckbox', this)">
                                    <label for="autre_charge" onselectstart="return false;" ondblclick="preventLabelSelection()"><i class="fa-solid fa-location-dot"></i>  Autre Charge</label>
                                    <div class="number-controls">
                                        <button type="button" class="decrement" onclick="decrementValue('autre_chargeCheckbox')">-</button>
                                        <input type="number" name="autre_charge" id="autre_chargeCheckbox" value="0" min="0" max="999">
                                        <div class="currency-symbol">€</div>
                                        <button type="button" class="increment" onclick="incrementValue('autre_chargeCheckbox')">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="background_svg_bottom bs_bottom">
                    <div class="back_bottom_svg ">
                        <svg class="bottom_svg svg-1" width="1920" height="622" viewBox="0 0 1920 622" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 250.162C174.446 280.415 568.777 309.678 750.534 184.705C977.729 28.489 1178.66 -6.71464 1357.92 0.986156C1501.33 7.14679 1792.39 69.1931 1920 99.4462V622L0 606.048V250.162Z" fill="#FFB74C"/>
                        </svg>

                        <svg class="bottom_svg svg-2" width="2337" height="766" viewBox="0 0 2337 766" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.5" d="M0 308.077C212.333 345.334 692.308 381.372 913.54 227.466C1190.08 35.0845 1434.65 -8.26915 1652.84 1.21446C1827.4 8.80135 2181.68 85.2121 2337 122.469V766L0 746.355V308.077Z" fill="#FFB74C"/>
                        </svg>

                        <svg class="bottom_svg svg-3" width="3253" height="890" viewBox="0 0 3253 890" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.3" d="M0 357.949C295.559 401.237 963.662 443.108 1271.61 264.289C1656.54 40.764 1996.97 -9.60776 2300.69 1.41106C2543.66 10.2261 3036.8 99.0063 3253 142.294V890L0 867.175V357.949Z" fill="#FFB74C"/>
                        </svg>

                        <svg class="bottom_svg svg-4" width="3495" height="1056" viewBox="0 0 3495 1056" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.2" d="M0 424.712C317.546 476.074 1035.35 525.755 1366.21 313.583C1779.77 48.3671 2145.53 -11.3998 2471.84 1.67424C2732.89 12.1335 3262.72 117.473 3495 168.835V1056L0 1028.92V424.712Z" fill="#FFB74C"/>
                        </svg>

                        <svg class="bottom_svg svg-5" width="3692" height="1176" viewBox="0 0 3692 1176" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.1" d="M0 472.975C335.445 530.174 1093.71 585.5 1443.21 349.217C1880.09 53.8634 2266.46 -12.6952 2611.17 1.8645C2886.93 13.5123 3446.62 130.822 3692 188.021V1176L0 1145.84V472.975Z" fill="#FFB74C"/>
                        </svg>
                    </div>     
                </div>
            </form>
        </main>

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

        function incrementValue(fieldId) {
            var field = document.getElementById(fieldId);
            var value = parseInt(field.value, 10);
            value = isNaN(value) ? 0 : value;
            value = value < 1000 ? value + 1 : 999;
            field.value = value;
        }

        function decrementValue(fieldId) {
            var field = document.getElementById(fieldId);
            var value = parseInt(field.value, 10);
            value = isNaN(value) ? 0 : value;
            value = value > 1 ? value - 1 : 0;
            field.value = value;
        }
    </script>
</html>