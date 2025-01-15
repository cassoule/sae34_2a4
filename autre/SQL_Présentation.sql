DROP SCHEMA IF EXISTS sae CASCADE;

CREATE SCHEMA sae;
SET SCHEMA 'sae';


CREATE TABLE _adresse(
  id_adresse		        SERIAL,
  code_postal           	INT            NOT NULL,
  adresse               	VARCHAR(50)    NOT NULL,
  complement_adresse		VARCHAR(50)    ,
  ville                 	VARCHAR(20)    NOT NULL,
  CONSTRAINT _adresse_pk PRIMARY KEY (id_adresse)
);


CREATE TABLE _compte(
  id_compte   		SERIAL,
  nom		  		VARCHAR(20)   NOT NULL,
  prenom			VARCHAR(20)	  NOT NULL,
  civilite			VARCHAR(10)   NOT NULL,
  email				VARCHAR(50)   NOT NULL,
  telephone			VARCHAR(20)   NOT NULL,
  mot_de_passe		VARCHAR(30)   NOT NULL,
  pseudo			VARCHAR(20)   NOT NULL,
  id_adresse      	INT           NOT NULL,
  CONSTRAINT _compte_pk PRIMARY KEY (id_compte),
  CONSTRAINT _compte_fk__adresse FOREIGN KEY (id_adresse) REFERENCES _adresse(id_adresse)
);


CREATE TABLE _client(
  id_client   			    INT   NOT NULL UNIQUE,
  validation_conditions		BOOL  NOT NULL,
  CONSTRAINT _client_pk PRIMARY KEY (id_client),
  CONSTRAINT _client_fk__compte FOREIGN KEY (id_client) REFERENCES _compte(id_compte)
);


CREATE TABLE _proprietaire(
  id_proprietaire 			INT   NOT NULL UNIQUE,
  validation_conditions		BOOL   NOT NULL,
  nom_banque 			    VARCHAR(20)    NOT NULL,
  code_banque    		    INT           NOT NULL,
  code_guichet 			    INT           NOT NULL,
  numero_compte 			VARCHAR(15)           NOT NULL,
  cle_rib                   INT           NOT NULL,
  iban                      VARCHAR(30)   NOT NULL,
  bic                       VARCHAR(20)   NOT NULL,
  CONSTRAINT _proprietaire_pk PRIMARY KEY (id_proprietaire),
  CONSTRAINT _proprietaire_fk__compte FOREIGN KEY (id_proprietaire) REFERENCES _compte(id_compte)
);


CREATE TABLE _langue(
  nom_langue		VARCHAR(20)   NOT NULL,
  CONSTRAINT _langue_pk PRIMARY KEY (nom_langue)
);


CREATE TABLE _parle(
  id_proprietaire		INT   NOT NULL,
  nom_langue			VARCHAR(20)   NOT NULL,
  CONSTRAINT _parle_pk PRIMARY KEY (id_proprietaire, nom_langue),
  CONSTRAINT _parle_fk__proprietaire FOREIGN KEY (id_proprietaire) REFERENCES _proprietaire(id_proprietaire),
  CONSTRAINT _parle_fk__langue FOREIGN KEY (nom_langue) REFERENCES _langue(nom_langue)
);


CREATE TABLE _administrateur(
  id_administrateur		INT   NOT NULL,
  CONSTRAINT _administrateur_pk PRIMARY KEY (id_administrateur),
  CONSTRAINT _administrateur_fk__compte FOREIGN KEY (id_administrateur) REFERENCES _compte(id_compte)
);


CREATE TABLE _logement(
  id_logement			    SERIAL,
  libelle_logement		    VARCHAR(50)  NOT NULL,
  accroche				    VARCHAR(200)   NOT NULL,
  description_detaille		VARCHAR(1000)   NOT NULL,
  max_personnes			    INT   NOT NULL,
  nature_logement         	VARCHAR(50) NOT NULL,
  type_logement 			VARCHAR(20)   NOT NULL,
  surface				    INT   NOT NULL,
  nb_chambres			    INT   NOT NULL,
  nb_lits_simple		    INT   NOT NULL,
  nb_lits_double		    INT   NOT NULL,
  nb_salle_de_bain		    INT   NOT NULL,
  tarif_nuit_HT           	FLOAT NOT NULL,
  avis_logement_total           FLOAT NOT NULL,
  est_actif	      		BOOLEAN   NOT NULL,
  id_proprietaire		    INT   NOT NULL,
  id_adresse		        INT   NOT NULL,
  CONSTRAINT _logement_pk PRIMARY KEY (id_logement),
  CONSTRAINT _logement_fk__proprietaire FOREIGN KEY (id_proprietaire) REFERENCES _proprietaire(id_proprietaire),
  CONSTRAINT _logement_fk__adresse FOREIGN KEY (id_adresse) REFERENCES _adresse(id_adresse)
);


CREATE TABLE _image( 
  id_image  						SERIAL,
  nom_image    						VARCHAR(50)    NOT NULL,
  lien_image						VARCHAR(100)   NOT NULL,
  id_compte     					INT,
  id_logement_image   				INT,
  CONSTRAINT _image_pk PRIMARY KEY (id_image),
  CONSTRAINT _image_fk__compte FOREIGN KEY (id_compte) REFERENCES _compte(id_compte),
  CONSTRAINT _image_fk__logement FOREIGN KEY (id_logement_image) REFERENCES _logement(id_logement)
);


CREATE TABLE _amenagement(
  nom_amenagement		VARCHAR(20)   NOT NULL,
  CONSTRAINT _amenagement_pk PRIMARY KEY (nom_amenagement)
);


CREATE TABLE _contient(
  id_logement			INT   NOT NULL,
  nom_amenagement		VARCHAR(20)   NOT NULL,
  CONSTRAINT _contient_pk PRIMARY KEY (id_logement, nom_amenagement),
  CONSTRAINT _contient_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement),
  CONSTRAINT _contient_fk__amenagement FOREIGN KEY (nom_amenagement) REFERENCES _amenagement(nom_amenagement)
);


CREATE TABLE _installation(
  nom_installation		VARCHAR(20)   NOT NULL,
  CONSTRAINT _installation_pk PRIMARY KEY (nom_installation)
);


CREATE TABLE _possede(
  id_logement			INT   NOT NULL,
  nom_installation		VARCHAR(20)   NOT NULL,
  CONSTRAINT _possede_pk PRIMARY KEY (id_logement, nom_installation),
  CONSTRAINT _possede_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement),
  CONSTRAINT _possede_fk__installation FOREIGN KEY (nom_installation) REFERENCES _installation(nom_installation)
);


CREATE TABLE _equipement(
  nom_equipement		VARCHAR(20)   NOT NULL,
  CONSTRAINT _equipement_pk PRIMARY KEY (nom_equipement)
);


CREATE TABLE _equipe(
  id_logement		  INT   NOT NULL,
  nom_equipement      VARCHAR(20)   NOT NULL,
  CONSTRAINT _equipe_pk PRIMARY KEY (id_logement, nom_equipement),
  CONSTRAINT _equipe_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement),
  CONSTRAINT _equipe_fk__equipement FOREIGN KEY (nom_equipement) REFERENCES _equipement(nom_equipement)
);


CREATE TABLE _service(
  id_service    SERIAL NOT NULL,
  nom_service			VARCHAR(50)   NOT NULL,
  prix_service_HT		FLOAT NOT NULL,
  id_logement		  INT   NOT NULL,
  CONSTRAINT _services_pk PRIMARY KEY (id_service),
  CONSTRAINT _equipe_fk__service FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);




CREATE TABLE _charge(
  id_charge SERIAL,
  nom_charge			VARCHAR(20)   NOT NULL,
  prix_charge_HT		FLOAT NOT NULL,
  id_logement		  INT   NOT NULL,
  CONSTRAINT _charges_pk PRIMARY KEY (id_charge),
  CONSTRAINT _equipe_fk__charge FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);




CREATE TABLE _avis(
  id_avis			SERIAL,
  titre_avis		VARCHAR(50) NOT NULL,
  note_avis			INT NOT NULL,
  contenu_avis		VARCHAR(200) NOT NULL,
  id_compte			INT   NOT NULL, /* a voir si pas client */
  id_logement		INT   NOT NULL,
  CONSTRAINT _avis_pk PRIMARY KEY (id_avis),
  CONSTRAINT _avis_fk__compte FOREIGN KEY (id_compte) REFERENCES _compte(id_compte),
  CONSTRAINT _avis_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);



CREATE TABLE _jour(
  id_jour      SERIAL,
  date_jour		 DATE,
  disponible    BOOL   	     NOT NULL, 
  raison      	VARCHAR(20)   NOT NULL,
  id_logement   INT         NOT NULL,
  CONSTRAINT _jour_pk PRIMARY KEY (id_jour),
  CONSTRAINT _jour_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);


CREATE TABLE _messagerie( 
  acces_admin			BOOL  NOT NULL, 
  id_client				INT   NOT NULL ,
  id_proprietaire		INT   NOT NULL,
  CONSTRAINT _messagerie_pk PRIMARY KEY (id_client, id_proprietaire),
  CONSTRAINT _messagerie_fk__client FOREIGN KEY (id_client) REFERENCES _client(id_client),
  CONSTRAINT _messagerie_fk__proprietaire FOREIGN KEY (id_proprietaire) REFERENCES _proprietaire(id_proprietaire)
);


CREATE TABLE _message( 
  id_message					SERIAL, 
  contenu						VARCHAR(1000)	NOT NULL,
  date_envoi					TIMESTAMP  			NOT NULL,
  id_emetteur				INT NOT NULL, /*a modif peut etre pas oblige de faire fk*/
  id_contient_client    		INT   			NOT NULL, 
  id_contient_proprietaire		INT   			NOT NULL,
  CONSTRAINT _message_pk PRIMARY KEY (id_message),
  CONSTRAINT _message_fk__messagerie_contient_client FOREIGN KEY (id_contient_client) REFERENCES _client(id_client),
  CONSTRAINT _message_fk__messagerie_contient_proprietaire FOREIGN KEY (id_contient_proprietaire) REFERENCES _proprietaire(id_proprietaire)
);


CREATE TABLE _message_demande_devis(
  id_message_demande_devis		INT     NOT NULL, 
  nb_personnes			     	INT     NOT NULL,
  date_debut		        	DATE    NOT NULL,
  date_fin		          		DATE    NOT NULL,
  id_logement           INT NOT NULL,
  CONSTRAINT _message_demande_devis_pk PRIMARY KEY (id_message_demande_devis),
  CONSTRAINT _message_demande_devis_fk__message FOREIGN KEY (id_message_demande_devis) REFERENCES _message(id_message)
);


CREATE TABLE _message_devis(
  id_message_devis		  				INT     		NOT NULL, 
  nb_personnes			     			INT     		NOT NULL,
  date_debut		        			DATE    		NOT NULL,
  date_fin		          				DATE    		NOT NULL,
  condition_annulation_reservation		VARCHAR(50)		NOT NULL,
  nb_jours_valide						INT 			NOT NULL,
  taxe_sejour             	FLOAT NOT NULL,/* a verifier*/
  id_logement    						INT 			NOT NULL,
  CONSTRAINT _message_devis_pk PRIMARY KEY (id_message_devis),
  CONSTRAINT _message_devis_fk__message FOREIGN KEY (id_message_devis) REFERENCES _message(id_message),
  CONSTRAINT _message_devis_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);




CREATE TABLE _paiement(
  id_paiement       SERIAL,
  numero_carte			VARCHAR(20)		NOT NULL,
  date_validite  		VARCHAR(10)  			NOT NULL,
  cryptogramme   		VARCHAR(20),
  CONSTRAINT _paiement_pk PRIMARY KEY (id_paiement)
);



CREATE TABLE _reservation( /* ajouter id_message_devis en fk je pense*/
  id_reservation		  SERIAL,
  acceptation_CGV		 BOOL  			NOT NULL,
  type_annulation   	VARCHAR(20)		NOT NULL,
  est_paye          	BOOL  			NOT NULL,
  id_message_devis		int				NOT NULL,
  id_paiement		INT 			NOT NULL,
  CONSTRAINT _reservation_pk PRIMARY KEY (id_reservation),
  CONSTRAINT _reservation_fk__message_devis FOREIGN KEY (id_message_devis) REFERENCES _message_devis(id_message_devis),
  CONSTRAINT _reservation_fk__paiement FOREIGN KEY (id_paiement) REFERENCES _paiement(id_paiement)
);



CREATE TABLE _message_type(
  id_message_type		INT   NOT NULL, 
  CONSTRAINT _message_type_pk PRIMARY KEY (id_message_type),
  CONSTRAINT _message_type_fk__message FOREIGN KEY (id_message_type) REFERENCES _message(id_message)
);


CREATE TABLE _signalement(
  id_signalement      	SERIAL,
  motif_signalement		VARCHAR(20)		NOT NULL,
  type_signalement		VARCHAR(20)    	NOT NULL,
  date_signalement		DATE   			NOT NULL,
  id_compte				INT,
  id_logement			INT,
  id_message			INT,
  id_avis				INT,
  id_administrateur		INT NOT NULL,
  CONSTRAINT _signalement_pk PRIMARY KEY (id_signalement),
  CONSTRAINT _signalement_fk__compte FOREIGN KEY (id_compte) REFERENCES _compte(id_compte),
  CONSTRAINT _signalement_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement),
  CONSTRAINT _signalement_fk__message FOREIGN KEY (id_message) REFERENCES _message(id_message),
  CONSTRAINT _signalement_fk__avis FOREIGN KEY (id_avis) REFERENCES _avis(id_avis),
  CONSTRAINT _signalement_fk__administrateur FOREIGN KEY (id_administrateur) REFERENCES _administrateur(id_administrateur)
);


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
  (true, 5, 3),DROP SCHEMA IF EXISTS sae CASCADE;

CREATE SCHEMA sae;
SET SCHEMA 'sae';


CREATE TABLE _adresse(
  id_adresse		        SERIAL,
  code_postal           	INT            NOT NULL,
  adresse               	VARCHAR(50)    NOT NULL,
  complement_adresse		VARCHAR(50)    ,
  ville                 	VARCHAR(20)    NOT NULL,
  CONSTRAINT _adresse_pk PRIMARY KEY (id_adresse)
);


CREATE TABLE _compte(
  id_compte   		SERIAL,
  nom		  		VARCHAR(20)   NOT NULL,
  prenom			VARCHAR(20)	  NOT NULL,
  civilite			VARCHAR(10)   NOT NULL,
  email				VARCHAR(50)   NOT NULL,
  telephone			VARCHAR(20)   NOT NULL,
  mot_de_passe		VARCHAR(30)   NOT NULL,
  pseudo			VARCHAR(20)   NOT NULL,
  id_adresse      	INT           NOT NULL,
  CONSTRAINT _compte_pk PRIMARY KEY (id_compte),
  CONSTRAINT _compte_fk__adresse FOREIGN KEY (id_adresse) REFERENCES _adresse(id_adresse)
);


CREATE TABLE _client(
  id_client   			    INT   NOT NULL UNIQUE,
  validation_conditions		BOOL  NOT NULL,
  CONSTRAINT _client_pk PRIMARY KEY (id_client),
  CONSTRAINT _client_fk__compte FOREIGN KEY (id_client) REFERENCES _compte(id_compte)
);


CREATE TABLE _proprietaire(
  id_proprietaire 			INT   NOT NULL UNIQUE,
  validation_conditions		BOOL   NOT NULL,
  nom_banque 			    VARCHAR(20)    NOT NULL,
  code_banque    		    INT           NOT NULL,
  code_guichet 			    INT           NOT NULL,
  numero_compte 			VARCHAR(15)           NOT NULL,
  cle_rib                   INT           NOT NULL,
  iban                      VARCHAR(30)   NOT NULL,
  bic                       VARCHAR(20)   NOT NULL,
  CONSTRAINT _proprietaire_pk PRIMARY KEY (id_proprietaire),
  CONSTRAINT _proprietaire_fk__compte FOREIGN KEY (id_proprietaire) REFERENCES _compte(id_compte)
);


CREATE TABLE _langue(
  nom_langue		VARCHAR(20)   NOT NULL,
  CONSTRAINT _langue_pk PRIMARY KEY (nom_langue)
);


CREATE TABLE _parle(
  id_proprietaire		INT   NOT NULL,
  nom_langue			VARCHAR(20)   NOT NULL,
  CONSTRAINT _parle_pk PRIMARY KEY (id_proprietaire, nom_langue),
  CONSTRAINT _parle_fk__proprietaire FOREIGN KEY (id_proprietaire) REFERENCES _proprietaire(id_proprietaire),
  CONSTRAINT _parle_fk__langue FOREIGN KEY (nom_langue) REFERENCES _langue(nom_langue)
);


CREATE TABLE _administrateur(
  id_administrateur		INT   NOT NULL,
  CONSTRAINT _administrateur_pk PRIMARY KEY (id_administrateur),
  CONSTRAINT _administrateur_fk__compte FOREIGN KEY (id_administrateur) REFERENCES _compte(id_compte)
);


CREATE TABLE _logement(
  id_logement			    SERIAL,
  libelle_logement		    VARCHAR(50)  NOT NULL,
  accroche				    VARCHAR(200)   NOT NULL,
  description_detaille		VARCHAR(1000)   NOT NULL,
  max_personnes			    INT   NOT NULL,
  nature_logement         	VARCHAR(50) NOT NULL,
  type_logement 			VARCHAR(20)   NOT NULL,
  surface				    INT   NOT NULL,
  nb_chambres			    INT   NOT NULL,
  nb_lits_simple		    INT   NOT NULL,
  nb_lits_double		    INT   NOT NULL,
  nb_salle_de_bain		    INT   NOT NULL,
  tarif_nuit_HT           	FLOAT NOT NULL,
  avis_logement_total           FLOAT NOT NULL,
  est_actif	      		BOOLEAN   NOT NULL,
  id_proprietaire		    INT   NOT NULL,
  id_adresse		        INT   NOT NULL,
  CONSTRAINT _logement_pk PRIMARY KEY (id_logement),
  CONSTRAINT _logement_fk__proprietaire FOREIGN KEY (id_proprietaire) REFERENCES _proprietaire(id_proprietaire),
  CONSTRAINT _logement_fk__adresse FOREIGN KEY (id_adresse) REFERENCES _adresse(id_adresse)
);


CREATE TABLE _image( 
  id_image  						SERIAL,
  nom_image    						VARCHAR(50)    NOT NULL,
  lien_image						VARCHAR(100)   NOT NULL,
  id_compte     					INT,
  id_logement_image   				INT,
  CONSTRAINT _image_pk PRIMARY KEY (id_image),
  CONSTRAINT _image_fk__compte FOREIGN KEY (id_compte) REFERENCES _compte(id_compte),
  CONSTRAINT _image_fk__logement FOREIGN KEY (id_logement_image) REFERENCES _logement(id_logement)
);


CREATE TABLE _amenagement(
  nom_amenagement		VARCHAR(20)   NOT NULL,
  CONSTRAINT _amenagement_pk PRIMARY KEY (nom_amenagement)
);


CREATE TABLE _contient(
  id_logement			INT   NOT NULL,
  nom_amenagement		VARCHAR(20)   NOT NULL,
  CONSTRAINT _contient_pk PRIMARY KEY (id_logement, nom_amenagement),
  CONSTRAINT _contient_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement),
  CONSTRAINT _contient_fk__amenagement FOREIGN KEY (nom_amenagement) REFERENCES _amenagement(nom_amenagement)
);


CREATE TABLE _installation(
  nom_installation		VARCHAR(20)   NOT NULL,
  CONSTRAINT _installation_pk PRIMARY KEY (nom_installation)
);


CREATE TABLE _possede(
  id_logement			INT   NOT NULL,
  nom_installation		VARCHAR(20)   NOT NULL,
  CONSTRAINT _possede_pk PRIMARY KEY (id_logement, nom_installation),
  CONSTRAINT _possede_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement),
  CONSTRAINT _possede_fk__installation FOREIGN KEY (nom_installation) REFERENCES _installation(nom_installation)
);


CREATE TABLE _equipement(
  nom_equipement		VARCHAR(20)   NOT NULL,
  CONSTRAINT _equipement_pk PRIMARY KEY (nom_equipement)
);


CREATE TABLE _equipe(
  id_logement		  INT   NOT NULL,
  nom_equipement      VARCHAR(20)   NOT NULL,
  CONSTRAINT _equipe_pk PRIMARY KEY (id_logement, nom_equipement),
  CONSTRAINT _equipe_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement),
  CONSTRAINT _equipe_fk__equipement FOREIGN KEY (nom_equipement) REFERENCES _equipement(nom_equipement)
);


CREATE TABLE _service(
  id_service    SERIAL NOT NULL,
  nom_service			VARCHAR(50)   NOT NULL,
  prix_service_HT		FLOAT NOT NULL,
  id_logement		  INT   NOT NULL,
  CONSTRAINT _services_pk PRIMARY KEY (id_service),
  CONSTRAINT _equipe_fk__service FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);




CREATE TABLE _charge(
  id_charge SERIAL,
  nom_charge			VARCHAR(20)   NOT NULL,
  prix_charge_HT		FLOAT NOT NULL,
  id_logement		  INT   NOT NULL,
  CONSTRAINT _charges_pk PRIMARY KEY (id_charge),
  CONSTRAINT _equipe_fk__charge FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);




CREATE TABLE _avis(
  id_avis			SERIAL,
  titre_avis		VARCHAR(50) NOT NULL,
  note_avis			INT NOT NULL,
  contenu_avis		VARCHAR(200) NOT NULL,
  id_compte			INT   NOT NULL, /* a voir si pas client */
  id_logement		INT   NOT NULL,
  CONSTRAINT _avis_pk PRIMARY KEY (id_avis),
  CONSTRAINT _avis_fk__compte FOREIGN KEY (id_compte) REFERENCES _compte(id_compte),
  CONSTRAINT _avis_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);



CREATE TABLE _jour(
  id_jour      SERIAL,
  date_jour		 DATE,
  disponible    BOOL   	     NOT NULL, 
  raison      	VARCHAR(20)   NOT NULL,
  id_logement   INT         NOT NULL,
  CONSTRAINT _jour_pk PRIMARY KEY (id_jour),
  CONSTRAINT _jour_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);


CREATE TABLE _messagerie( 
  acces_admin			BOOL  NOT NULL, 
  id_client				INT   NOT NULL ,
  id_proprietaire		INT   NOT NULL,
  CONSTRAINT _messagerie_pk PRIMARY KEY (id_client, id_proprietaire),
  CONSTRAINT _messagerie_fk__client FOREIGN KEY (id_client) REFERENCES _client(id_client),
  CONSTRAINT _messagerie_fk__proprietaire FOREIGN KEY (id_proprietaire) REFERENCES _proprietaire(id_proprietaire)
);


CREATE TABLE _message( 
  id_message					SERIAL, 
  contenu						VARCHAR(1000)	NOT NULL,
  date_envoi					TIMESTAMP  			NOT NULL,
  id_emetteur				INT NOT NULL, /*a modif peut etre pas oblige de faire fk*/
  id_contient_client    		INT   			NOT NULL, 
  id_contient_proprietaire		INT   			NOT NULL,
  CONSTRAINT _message_pk PRIMARY KEY (id_message),
  CONSTRAINT _message_fk__messagerie_contient_client FOREIGN KEY (id_contient_client) REFERENCES _client(id_client),
  CONSTRAINT _message_fk__messagerie_contient_proprietaire FOREIGN KEY (id_contient_proprietaire) REFERENCES _proprietaire(id_proprietaire)
);


CREATE TABLE _message_demande_devis(
  id_message_demande_devis		INT     NOT NULL, 
  nb_personnes			     	INT     NOT NULL,
  date_debut		        	DATE    NOT NULL,
  date_fin		          		DATE    NOT NULL,
  id_logement           INT NOT NULL,
  CONSTRAINT _message_demande_devis_pk PRIMARY KEY (id_message_demande_devis),
  CONSTRAINT _message_demande_devis_fk__message FOREIGN KEY (id_message_demande_devis) REFERENCES _message(id_message)
);


CREATE TABLE _message_devis(
  id_message_devis		  				INT     		NOT NULL, 
  nb_personnes			     			INT     		NOT NULL,
  date_debut		        			DATE    		NOT NULL,
  date_fin		          				DATE    		NOT NULL,
  condition_annulation_reservation		VARCHAR(50)		NOT NULL,
  nb_jours_valide						INT 			NOT NULL,
  taxe_sejour             	FLOAT NOT NULL,/* a verifier*/
  id_logement    						INT 			NOT NULL,
  CONSTRAINT _message_devis_pk PRIMARY KEY (id_message_devis),
  CONSTRAINT _message_devis_fk__message FOREIGN KEY (id_message_devis) REFERENCES _message(id_message),
  CONSTRAINT _message_devis_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement)
);




CREATE TABLE _paiement(
  id_paiement       SERIAL,
  numero_carte			VARCHAR(20)		NOT NULL,
  date_validite  		VARCHAR(10)  			NOT NULL,
  cryptogramme   		VARCHAR(20),
  CONSTRAINT _paiement_pk PRIMARY KEY (id_paiement)
);



CREATE TABLE _reservation( /* ajouter id_message_devis en fk je pense*/
  id_reservation		  SERIAL,
  acceptation_CGV		 BOOL  			NOT NULL,
  type_annulation   	VARCHAR(20)		NOT NULL,
  est_paye          	BOOL  			NOT NULL,
  id_message_devis		int				NOT NULL,
  id_paiement		INT 			NOT NULL,
  CONSTRAINT _reservation_pk PRIMARY KEY (id_reservation),
  CONSTRAINT _reservation_fk__message_devis FOREIGN KEY (id_message_devis) REFERENCES _message_devis(id_message_devis),
  CONSTRAINT _reservation_fk__paiement FOREIGN KEY (id_paiement) REFERENCES _paiement(id_paiement)
);



CREATE TABLE _message_type(
  id_message_type		INT   NOT NULL, 
  CONSTRAINT _message_type_pk PRIMARY KEY (id_message_type),
  CONSTRAINT _message_type_fk__message FOREIGN KEY (id_message_type) REFERENCES _message(id_message)
);


CREATE TABLE _signalement(
  id_signalement      	SERIAL,
  motif_signalement		VARCHAR(20)		NOT NULL,
  type_signalement		VARCHAR(20)    	NOT NULL,
  date_signalement		DATE   			NOT NULL,
  id_compte				INT,
  id_logement			INT,
  id_message			INT,
  id_avis				INT,
  id_administrateur		INT NOT NULL,
  CONSTRAINT _signalement_pk PRIMARY KEY (id_signalement),
  CONSTRAINT _signalement_fk__compte FOREIGN KEY (id_compte) REFERENCES _compte(id_compte),
  CONSTRAINT _signalement_fk__logement FOREIGN KEY (id_logement) REFERENCES _logement(id_logement),
  CONSTRAINT _signalement_fk__message FOREIGN KEY (id_message) REFERENCES _message(id_message),
  CONSTRAINT _signalement_fk__avis FOREIGN KEY (id_avis) REFERENCES _avis(id_avis),
  CONSTRAINT _signalement_fk__administrateur FOREIGN KEY (id_administrateur) REFERENCES _administrateur(id_administrateur)
);
