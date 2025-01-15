-- Insertion d'exemples d'adresses
INSERT INTO _adresse (code_postal, adresse, complement_adresse, ville)
VALUES
  (00000, 'adresse_visiteur', 'adresse_visiteur', 'adresse_visiteur'), --Compte pour les visiteur
  (12345, '20 Rue de la Patrie', 'Appartement 4B', 'Lorient'),
  (54321, '15 Rue Saint-Vincent', null, 'Vannes'),
  (67890, 'Quai Eric Tabarly', 'Appartement 5C', 'Lorient'),
  (87654, '8 Rue Leo le Bourgo', null, 'Lorient'),
  (98765, '9 Rue Saint-Guillaume', 'Appartement 6D', 'Saint-Brieuc'),
  (23456, '40 Rue de Kermaria', null, 'Brest'),
  (34567, '11 Rue Elie Freron', 'Appartement 3A', 'Quimper'),
  (45678, '6 Rue du Pre Botte', null, 'Rennes');

-- Insertion d'exemples de comptes
INSERT INTO _compte (nom, prenom, civilite, email, telephone, mot_de_passe, pseudo, id_adresse)
VALUES
  ('compte_visiteur', 'compte_visiteur', 'M', 'compte_visiteur', 'compte_visiteur', 'compte_visiteur', 'compte_visiteur', 1),
  ('Dupont', 'Jean', 'M', 'jean.dupont@email.com', '06 99 79 37 54', 'motdepasse1', 'jdupont', 1),
  ('Martin', 'Sophie', 'Mme', 'sophie.martin@email.com', '06 39 48 57 21', 'motdepasse2', 'smart', 2),
  ('Lefebvre', 'Pierre', 'M', 'pierre.lefebvre@gmail.com', '07 17 82 30 94', 'T5mP8qH7sK', 'plefebvre', 3),
  ('Carlier', 'Isabelle', 'Mme', 'isabelle.carlier@gmail.com', '06 65 21 09 83', 'L9bZ3gY2aW', 'icarlier', 4),
  ('Girard', 'Marie', 'Mme', 'marie.girard@gmail.com', '07 46 58 92 07', 'D4nX6cR1vF', 'mgirard', 5),
  ('Lefebvre', 'Pierre', 'M', 'pierre.lefebvre@gmail.com', '07 91 37 40 28', 'G8jM2kP5sN', 'plefebvre', 6),
  ('MARTIN', 'Maxime', 'M', 'maxime.martin2704@gmail.com', '07 83 74 46 30', 'azerty123456', 'MaxouPapou', 1);


-- Insertion d'exemples de clients
INSERT INTO _client (id_client, validation_conditions)
VALUES
  (2, True),
  (5, False);


-- Insertion d'exemples de propriétaires
INSERT INTO _proprietaire (id_proprietaire, validation_conditions, nom_banque, code_banque, code_guichet, numero_compte, cle_rib, iban, bic)
VALUES
  (3, True, 'Boursorama', 19406, 12345, '67890', 123, 'FR1234567890123456789012345', 'BICFR123'),
  (6, False, 'Credit agricole', 19406, 54321, '98765', 321, 'FR5432198765321098765432109', 'BICFR543');


-- Insertion d'exemples d'administrateurs
INSERT INTO _administrateur (id_administrateur)
VALUES
  (4),
  (7);


-- Insertion d'exemples de langues
INSERT INTO _langue (nom_langue)
VALUES
  ('Français'),
  ('Anglais'),
  ('Espagnol');


INSERT INTO _parle (nom_langue, id_proprietaire)
VALUES
  ('Français', 3),
  ('Anglais', 3),
  ('Français', 6),
  ('Espagnol', 6);


-- Insertion d'exemples de logements
INSERT INTO _logement (libelle_logement, accroche, description_detaille, max_personnes, nature_logement, type_logement, surface, nb_chambres, nb_lits_simple, nb_lits_double, nb_salle_de_bain, tarif_nuit_HT, avis_logement_total, est_actif, id_proprietaire, id_adresse)
VALUES
  ('Maison de vacances', 'Belle maison près de la plage', 'Une belle maison spacieuse pour des vacances en famille.', 8, 'Maison', 'Vacances', 200, 4, 2, 2, 2, 100, 5, true, 3, 8),
  ('Appartement en centre-ville', 'Emplacement idéal pour les touristes', 'Un appartement confortable au cœur de la ville.', 4, 'Appartement', 'Ville', 75, 2, 1, 1, 1, 8, 1, true, 6, 9);


-- Insertion d'exemples d'images
INSERT INTO _image (nom_image, lien_image, id_compte, id_logement_image)
VALUES
  ('Image 1_neutre', 'img/photo_de_profil_neutre.png', 3, null),
  ('Image 1_neutre', 'img/photo_de_profil_neutre.png', 6, null),
  ('Image 2', 'img/appartement.jpg', null, 2),
  ('Image 3', 'img/appartement 2.jpg', null, 2),
  ('Image 4', 'img/maison de vacances.jpg', null, 1),
  ('Image 5', 'img/maison de vacances 2.jpg', null, 1);


-- Insertion d'exemples d'aménagements
INSERT INTO _amenagement (nom_amenagement)
VALUES
  ('Jardin'),
  ('Balcon'),
  ('Terrasse'),
  ('Parking prive'),
  ('Parking public');


-- Insertion d'exemples de contenu de logement
INSERT INTO _contient (id_logement, nom_amenagement)
VALUES
  (1, 'Jardin'),
  (1, 'Balcon'),
  (2, 'Terrasse'),
  (2, 'Parking prive'),
  (2, 'Jardin');

-- Insertion dexemples dinstallations
INSERT INTO _installation (nom_installation)
VALUES
  ('Climatisation'),
  ('Piscine'),
  ('Jacuzzi'),
  ('Hammam'), 
  ('Sauna');

-- Insertion d'exemples de possession d'installations
INSERT INTO _possede (id_logement, nom_installation)
VALUES
  (1, 'Climatisation'),
  (1, 'Piscine'),
  (2, 'Climatisation'),
  (2, 'Hammam');


-- Insertion d'exemples d'équipements
INSERT INTO _equipement (nom_equipement)
VALUES
  ('Television'),
  ('Wifi'),
  ('Lave-linge'),
  ('Barbecue'),
  ('Seche-linge'),
  ('Lave-vaisselle');


-- Insertion d'exemples d'équipe
INSERT INTO _equipe (id_logement, nom_equipement)
VALUES
  (1, 'Television'),
  (1, 'Wifi'),
  (1, 'Barbecue'),
  (2, 'Television'),
  (2, 'Wifi');

-- Insertion d'exemples de services
INSERT INTO _service (nom_service, prix_service_HT, id_logement)
VALUES
  ('Service de ménage', 30.00, 1),
  ('Service de petit déjeuner', 10.00, 1),
  ('Service de navette aéroport', 50.00, 2);



-- Insertion d'exemples de charges
INSERT INTO _charge (nom_charge, prix_charge_HT, id_logement)
VALUES
  ('Frais de nettoyage', 20.00, 1),
  ('Dépôt de garantie', 100.00, 2);





-- Insertion d'exemples d'avis
INSERT INTO _avis (titre_avis, note_avis, contenu_avis, id_compte, id_logement)
VALUES
  ('Excellent séjour', 5, 'Nous avons passé un excellent séjour dans cette maison.', 2, 1),
  ('Bon rapport qualité-prix', 4, 'Lappartement était propre et bien situé, bon rapport qualité-prix.', 5, 2);




-- Insertion d'exemples de planning
INSERT INTO _jour(date_jour, disponible, raison, id_logement)
VALUES
('2023-11-01', true,'', 1),
('2023-11-02', true,'', 1),
('2023-11-03', true,'', 1),
('2023-11-04', true,'', 1),
('2023-11-05', true,'', 1),
('2023-11-06', true,'', 1),
('2023-11-07', true,'', 1),
('2023-11-08', true,'', 1),
('2023-11-09', true,'', 1),
('2023-11-10', true,'', 1),
('2023-11-11', true,'', 1),
('2023-11-12', true,'', 1),
('2023-11-13', true,'', 1),
('2023-11-14', true,'', 1),
('2023-11-15', true,'', 1),
('2023-11-16', true,'', 1),
('2023-11-17', true,'', 1),
('2023-11-18', true,'', 1), 
('2023-11-19', true,'', 1), 
('2023-11-20', true,'', 1), 
('2023-11-21', true,'', 1), 
('2023-11-22', true,'', 1), 
('2023-11-23', true,'', 1), 
('2023-11-24', true,'', 1), 
('2023-11-25', true,'', 1), 
('2023-11-26', true,'', 1), 
('2023-11-27', true,'', 1), 
('2023-11-28', true,'', 1), 
('2023-11-29', true,'', 1), 
('2023-11-30', true,'', 1), 
('2023-12-01', true,'', 1), 
('2023-12-02', true,'', 1), 
('2023-12-03', true,'', 1), 
('2023-12-04', true,'', 1), 
('2023-12-05', true,'', 1), 
('2023-12-06', true,'', 1), 
('2023-12-07', true,'', 1), 
('2023-12-08', true,'', 1), 
('2023-12-09', true,'', 1), 
('2023-12-10', true,'', 1), 
('2023-12-11', true,'', 1), 
('2023-12-12', true,'', 1), 
('2023-12-13', true,'', 1), 
('2023-12-14', true,'', 1), 
('2023-12-15', true,'', 1), 
('2023-12-16', true,'', 1), 
('2023-12-17', true,'', 1), 
('2023-12-18', true,'', 1), 
('2023-12-19', true,'', 1), 
('2023-12-20', true,'', 1), 
('2023-12-21', true,'', 1), 
('2023-12-22', true,'', 1), 
('2023-12-23', true,'', 1), 
('2023-12-24', true,'', 1), 
('2023-12-25', true,'', 1), 
('2023-12-26', true,'', 1), 
('2023-12-27', true,'', 1), 
('2023-12-28', true,'', 1), 
('2023-12-29', true,'', 1), 
('2023-12-30', true,'', 1), 
('2023-12-31', true,'', 1), 
('2023-11-01', true,'', 2),
('2023-11-02', true,'', 2),
('2023-11-03', true,'', 2),
('2023-11-04', true,'', 2),
('2023-11-05', true,'', 2),
('2023-11-06', true,'', 2),
('2023-11-07', true,'', 2),
('2023-11-08', true,'', 2),
('2023-11-09', true,'', 2),
('2023-11-10', true,'', 2),
('2023-11-11', true,'', 2),
('2023-11-12', true,'', 2),
('2023-11-13', true,'', 2),
('2023-11-14', true,'', 2),
('2023-11-15', true,'', 2),
('2023-11-16', true,'', 2),
('2023-11-17', true,'', 2),
('2023-11-18', true,'', 2), 
('2023-11-19', true,'', 2), 
('2023-11-20', true,'', 2), 
('2023-11-21', true,'', 2), 
('2023-11-22', true,'', 2), 
('2023-11-23', true,'', 2), 
('2023-11-24', true,'', 2), 
('2023-11-25', true,'', 2), 
('2023-11-26', true,'', 2), 
('2023-11-27', true,'', 2), 
('2023-11-28', true,'', 2), 
('2023-11-29', true,'', 2), 
('2023-11-30', true,'', 2), 
('2023-12-01', true,'', 2), 
('2023-12-02', true,'', 2), 
('2023-12-03', true,'', 2), 
('2023-12-04', true,'', 2), 
('2023-12-05', true,'', 2), 
('2023-12-06', true,'', 2), 
('2023-12-07', true,'', 2), 
('2023-12-08', true,'', 2), 
('2023-12-09', true,'', 2), 
('2023-12-10', true,'', 2), 
('2023-12-11', true,'', 2), 
('2023-12-12', true,'', 2), 
('2023-12-13', true,'', 2), 
('2023-12-14', true,'', 2), 
('2023-12-15', true,'', 2), 
('2023-12-16', true,'', 2), 
('2023-12-17', true,'', 2), 
('2023-12-18', true,'', 2), 
('2023-12-19', true,'', 2), 
('2023-12-20', true,'', 2), 
('2023-12-21', true,'', 2), 
('2023-12-22', true,'', 2), 
('2023-12-23', true,'', 2), 
('2023-12-24', true,'', 2), 
('2023-12-25', true,'', 2), 
('2023-12-26', true,'', 2), 
('2023-12-27', true,'', 2), 
('2023-12-28', true,'', 2), 
('2023-12-29', true,'', 2), 
('2023-12-30', true,'', 2), 
('2023-12-31', true,'', 2);


-- Insertion d'exemples de messagerie
INSERT INTO _messagerie (acces_admin, id_client, id_proprietaire)
VALUES
  (true, 2, 3),
  (true, 5, 3),
  (false, 5, 6);


-- Insertion d'exemples de messages
INSERT INTO _message (contenu, date_envoi, id_emetteur, id_contient_client, id_contient_proprietaire)
VALUES
  ('Bonjour, je suis intéressé par votre logement.', '2023-10-15 00:00:00', 2, 2, 3),
  ('Bonjour, quelles sont les dates disponibles ?', '2023-10-16 1:10:10', 6, 5, 6),
  ('demande_devis', '2023-10-15 2:20:20', 2, 2, 3),
  ('devis1', '2023-10-15 3:25:25', 3, 2, 3),
  ('devis2', '2023-10-15 4:58:20', 3, 2, 3),
  ('devis3', '2023-10-15 4:58:20', 3, 2, 3),
  ('Bonjour, ceci est un message type.', '2023-10-15 5:50:50', 6, 5, 6);

-- Insertion d'exemples de messages de demande de devis
INSERT INTO _message_demande_devis (id_message_demande_devis, nb_personnes, date_debut, date_fin, id_logement)
VALUES
  (3, 4, '2023-11-01', '2023-11-07', 1);


-- Insertion d'exemples de messages de devis
INSERT INTO _message_devis (id_message_devis, nb_personnes, date_debut, date_fin, condition_annulation_reservation, nb_jours_valide, taxe_sejour, id_logement)
VALUES
  (4, 4, '2023-11-01', '2023-11-07', 'Standard', 7, 1, 1),
  (5, 9, '2023-11-18', '2023-11-25', 'Standard', 4, 1, 1),
  (6, 9, '2023-11-28', '2023-12-04', 'Standard', 4, 1, 1);



-- Insertion d'exemples de paiements
INSERT INTO _paiement (numero_carte, date_validite, cryptogramme)
VALUES
  ('1234567890123456', '2025-12-31', '123');


-- Insertion d'exemples de réservations
INSERT INTO _reservation (acceptation_CGV, type_annulation, est_paye, id_message_devis, id_paiement)
VALUES
  (true, 'Standard', true, 4, 1),
  (true, 'Standard', true, 5, 1),
  (true, 'Standard', true, 6, 1);


-- Insertion d'exemples de types de messages
INSERT INTO _message_type (id_message_type)
VALUES
  (6);


-- Insertion d'exemples de signalements
INSERT INTO _signalement (motif_signalement, type_signalement, date_signalement, id_compte, id_logement, id_message, id_avis, id_administrateur)
VALUES
  ('message inaproprié', 'compte', '2023-10-17', null, null, 1, null, 4);


INSERT INTO _signalement (motif_signalement, type_signalement, date_signalement, id_compte, id_logement, id_message, id_avis, id_administrateur)
VALUES
  ('Contenu trompeur', 'message', '2023-10-18', null, 1, null, null, 7);
 
