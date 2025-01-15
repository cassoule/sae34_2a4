


CREATE TABLE _cleCalendrier(
  id_cleCal SERIAL,
  token VARCHAR(17) NOT NULl,
  nom_cal VARCHAR(20),
  id_proprietaire INT,
  reservation BOOL,
  demandeReservation BOOL,
  indisponibilite BOOL,
  debut DATE,
  fin DATE,
  logement INTEGER[],
  CONSTRAINT _cleCal_pk PRIMARY KEY (id_cleCal),
  CONSTRAINT _cleCAL_fk_proprietaire FOREIGN KEY (id_proprietaire) REFERENCES _proprietaire(id_proprietaire)
);




INSERT INTO _cleCalendrier(token, nom_cal, id_proprietaire, reservation, demandeReservation, indisponibilite, debut, fin, logement) VALUES ('test', 'cal 1', 3, true, true, false, '2024-04-10', '2024-04-20', ARRAY[1]);
--INSERT INTO _cleCalendrier(token, id_proprietaire, reservation, demandeReservation, indisponibilite, debut, fin, logement) VALUES ('test2', 3, true, false, false, '2024-04-10', '2024-04-20', ARRAY[1, 3, 5]);
