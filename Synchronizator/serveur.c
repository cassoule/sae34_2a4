#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <string.h>
#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <postgresql/libpq-fe.h>

#define MAX_BUFFER_SIZE 8192
#define MAX_STRING_LOGS 8192
#define MAX_LEN_SIZE 2056

const char *verdose = "./verdose.txt";

// Fonction pour récupérer l'adresse IP de la personne qui se connecte
const char *get_client_ip(struct sockaddr_in *client_addr) {
    return inet_ntoa(client_addr->sin_addr);
}

// Procedure pour récupérer la date et l'heure
void getDateHeure(char *dateTimeStr) {
    time_t t;
    struct tm *tm_info;

    time(&t);
    tm_info = localtime(&t);

    strftime(dateTimeStr, 20, "%Y-%m-%d %H:%M:%S", tm_info);
}

// Fonction pour valider le format de la date (YYYY-MM-DD)
int valideDateFormat(const char *date) {
    if (strlen(date) != 10) {
        return 0; // Longueur incorrecte
    }

    // Vérifier les caractères aux positions spécifiques
    if (date[4] != '-' || date[7] != '-') {
        return 0; // Mauvais emplacement des tirets
    }

    // Vérifier que les caractères avant et après les tirets sont des chiffres
    for (int i = 0; i < 10; i++) {
        if (i != 4 && i != 7 && (date[i] < '0' || date[i] > '9')) {
            return 0; // Caractères incorrects
        }
    }

    return 1; // Format correct
}

int main(int argc, char *argv[]) {
    // Configuration serveur pour ecouter les requetes
    int port = atoi(argv[1]);
    int sock, ret;
    struct sockaddr_in addr;
    int size, cnx;
    struct sockaddr_in conn_addr;

    sock = socket(AF_INET, SOCK_STREAM, 0);

    addr.sin_addr.s_addr = inet_addr("127.0.0.1");
    addr.sin_family = AF_INET;
    addr.sin_port = htons(port);
    ret = bind(sock, (struct sockaddr *)&addr, sizeof(addr));
    ret = listen(sock, 1);

    // Configuration de la base de données
    PGconn *connexion;
    PGresult *res;

    const char *paramConnect[] = {"host", "port", "user", "password", NULL};
    const char *paramValeur[] = {"localhost", "5432", "sae", "roh4Xie4Aingahch", NULL};

    // Connexion à la base de données
    connexion = PQconnectdbParams(paramConnect, paramValeur, 0);

    // Vérification de la connexion
    if (PQstatus(connexion) != CONNECTION_OK) {
        fprintf(stderr, "Échec de la connexion : %s", PQerrorMessage(connexion));
        PQfinish(connexion);
        exit(1);
    }

    // Initialisation des variables necessaires pour faire fonctionner le serveur correctement
    char id_compte[10];
    ssize_t token_len;
    char token[256];
    char buffer[MAX_BUFFER_SIZE];
    ssize_t bytesRead;
    char resultBuffer[MAX_BUFFER_SIZE];
    char id_logement[MAX_LEN_SIZE];
    char date_debut[MAX_LEN_SIZE];
    char date_fin[MAX_LEN_SIZE];
    char string_logs[MAX_STRING_LOGS];

    // Initialisation des variables necessaires pour faire des requêtes à la base de données
    char query[8192]; // String stockant les requetes faites pour la base de données
    int numRows, numCols; // Entier permettant de parcourir les résultats de chaque requêtes faites à la base de données
    int i, j; // Entier permettant de boucler sur les résultats de chaque requêtes faites à la base données

    // Ouverture de verdose.txt pour enregistrer les logs
    FILE *inputFile = NULL;
    inputFile = fopen("verdose.txt", "a");
    if (inputFile == NULL) {
        printf("Le fichier %s ne peut pas etre ouvert\n", verdose);
        exit(0);
    }

    while (1) {
        // Permet de faire autant de requête client que l'on souhaite sans que le serveur s'arrête
        size = sizeof(conn_addr);
        cnx = accept(sock, (struct sockaddr *)&conn_addr, (socklen_t *)&size);

        // Si il y a une erreur lors de la connexion d'un client
        if (cnx == -1) {
            perror("accept");
            // Continuez la boucle même en cas d'échec pour accepter une nouvelle connexion
            continue;
        }

        // Lecture du token envoyé par le client / stocker dans la variable token
        token_len = read(cnx, token, sizeof(token) - 1);
        if (token_len <= 0) {
            perror("read");
            break;
        }

        // Supprime le caractère de nouvelle ligne à la fin du pseudo, s'il est présent
        while (token_len > 0 && (token[token_len - 1] == '\n' || token[token_len - 1] == '\r')) {
            token_len--;
        }

        // Ajoute le caractère de fin de chaîne
        token[token_len] = '\0';

        // Prepare la requete SQL permettant de recuperer l'id_compte du client se connectant au serveur
        snprintf(query, sizeof(query), "SELECT id_proprietaire FROM sae._cle WHERE token = '%s'", token);

        // Lance la requete et stock sont resultat dans la variable res 
        res = PQexec(connexion, query);
        
        // Vérification de l'exécution de la requête dans le cas ou elle ne fonctionne pas
        if (PQresultStatus(res) != PGRES_TUPLES_OK) {
            fprintf(stderr, "Échec de l'exécution de la requête : %s", PQerrorMessage(connexion));
            PQclear(res);
            PQfinish(connexion);
            exit(1);
        }

        // Recupere le nombre de colonne et de ligne que la requete nous a renvoyer
        numRows = PQntuples(res);
        numCols = PQnfields(res);

        // Parcours de res pour recuperer l'id_compte du client qui se connecte au serveur
        for (i=0; i<numRows; i++) {
            for (j=0; j<numCols; j++) {
                // Stocke le résultat dans la variable compte
                snprintf(id_compte, sizeof(id_compte), "%s", PQgetvalue(res, i, j));
            }
        }

        // Si le token donne par le client n'existe pas
        if (strcmp(id_compte, "") == 0) {
            // Recupere la date et l'heure
            char dateTimeStr[20];
            getDateHeure(dateTimeStr);

            // Enregistre un message d'erreur dans verdose.txt
            snprintf(string_logs, sizeof(string_logs), "Connexion d'un client, Echec : token invalide, Heure : %s, IP : %s, Token : %s, Port : %d\n", dateTimeStr, /*client_ip*/get_client_ip(&conn_addr), token, port);
            if (fputs(string_logs, inputFile) == EOF) {
                perror("Erreur lors de l'écriture dans le fichier");
                exit(0);
            }
            
            // Vide le contenue de inputFile
            fflush(inputFile);

            // Réinitialiser le contenu de string_logs
            memset(string_logs, 0, sizeof(string_logs));

            // Déconnexion du client
            printf("id_compte invalide\n");
            close(cnx);
            break;
        }

        // Initialisation des variables utilisé pour récupérer les privilèges du token
        int privilegeUn = 0;
        int privilegeDeux = 0;

        // Prepare la requête permettant de voir si le client est un proprietaire
        snprintf(query, sizeof(query), "SELECT privilege_1 FROM sae._cle WHERE token = '%s'", token);

        // Lance la requête et stock le résultat dans la variable res
        res  = PQexec(connexion, query);

        // Vérification de l'exécution de la requête dans le cas ou elle ne fonctionne pas
        if (PQresultStatus(res) != PGRES_TUPLES_OK) {
            fprintf(stderr, "Échec de l'exécution de la requête : %s", PQerrorMessage(connexion));
            PQclear(res);
            PQfinish(connexion);
            exit(1);
        }

        // Recupere le nombre de colonne et de ligne que la requete nous a renvoyer
        numRows = PQntuples(res);
        numCols = PQnfields(res);

        // Parcours de res pour recuperer toutes les valeurs en resultat
        for (i=0; i<numRows; i++) {
            for (j=0; j<numCols; j++) {
                // Stocke le résultat dans la variable compte
                if (PQgetvalue(res, i, j)[0] == 't') {
                    privilegeUn = 1;
                }
            }
        }

        // Prépare la requête permettant de voir si le client est un administrateur
        snprintf(query, sizeof(query), "SELECT privilege_2 FROM sae._cle WHERE token = '%s'", token);

        // lance la requête et stock le résultat dans la variable res
        res = PQexec(connexion, query);

        // Vérification de l'exécution de la requête dans le cas ou elle ne fonctionne pas
        if (PQresultStatus(res) != PGRES_TUPLES_OK) {
            fprintf(stderr, "Échec de l'exécution de la requête : %s", PQerrorMessage(connexion));
            PQclear(res);
            PQfinish(connexion);
            exit(1);
        }

        // Recupere le nombre de colonne et de ligne que la requete nous a renvoyer
        int numRows = PQntuples(res);
        int numCols = PQnfields(res);

        // Parcours de res pour recuperer toutes les valeurs en resultat
        for (i=0; i<numRows; i++) {
            for (j=0; j<numCols; j++) {
                // Stocke le résultat dans la variable compte
                if (PQgetvalue(res, i, j)[0] == 't') {
                    privilegeDeux = 1;
                }
            }
        }

        // Ecriture de la connexion d'un client
        /*char client_ip[INET_ADDRSTRLEN];
        inet_ntop(AF_INET, &(conn_addr.sin_addr), client_ip, INET_ADDRSTRLEN);*/
        // Recupere la date et l'heure
        char dateTimeStr[20];
        getDateHeure(dateTimeStr);

        // Ecriture de l'en-tete dans verdose.txt lorsqu'un client se connecte sur le serveur
        snprintf(string_logs, sizeof(string_logs), "Connexion d'un client, Succes, Heure : %s, IP : %s, Token : %s, Port : %d\n", dateTimeStr, /*client_ip*/get_client_ip(&conn_addr), token, port);
        if (fputs(string_logs, inputFile) == EOF) {
            perror("Erreur lors de l'écriture dans le fichier");
            exit(0);
        }
        
        // Vide le contenue de inputFile
        fflush(inputFile);

        // Réinitialiser le contenu de string_logs
        memset(string_logs, 0, sizeof(string_logs));

        write(cnx, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ", strlen("1) Afficher tous les biens d'un client (si vous etes avez les privileges)\n2) Afficher vos biens ou ceux d'une autre personne (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : "));

        // Lance la boucle qui va gérer les interactions avec le client
        while (1) {
            // Initialisation d'une chaîne de caractères pour stocker les résultats
            resultBuffer[0] = '\0';
            
            bytesRead = read(cnx, buffer, sizeof(buffer) - 1);
            // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
            buffer[bytesRead] = '\0';

            // Recupere la date et l'heure
            char dateTimeStr[20];
            getDateHeure(dateTimeStr);

            // Si le client a taper 1 pour afficher tous ses logements
            if (strcmp(buffer, "1") == 0) {
                // Si le client a les droits pour afficher tous ses logements
                if (privilegeUn == 1) {
                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage des biens du proprietaire, Succès, id_compte : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_compte);

                    // Affichage du format d'un logement
                    strcat(resultBuffer, "\nid_logement   libelle_logement\n\n");

                    // Prépare la requête permettant de récupérer tous les logements
                    snprintf(query, sizeof(query), "SELECT id_logement, libelle_logement FROM sae._logement WHERE id_proprietaire = '%s'", id_compte);

                    // Lance la requete et stock sont resultat dans la variable res 
                    res = PQexec(connexion, query);

                    // Vérification de l'exécution de la requête dans le cas ou elle ne fonctionne pas
                    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
                        fprintf(stderr, "Échec de l'exécution de la requête : %s", PQerrorMessage(connexion));
                        PQclear(res);
                        PQfinish(connexion);
                        exit(1);
                    }

                    // Recupere le nombre de colonne et de ligne que la requete nous a renvoyer
                    numRows = PQntuples(res);
                    numCols = PQnfields(res);

                    // Parcours de res pour récupérer toutes les valeurs en résultat
                    for (i = 0; i < numRows; i++) {
                        for (j = 0; j < numCols; j++) {
                            // Ajouter chaque attribut suivi d'un séparateur à la chaîne résultante
                            strcat(resultBuffer, PQgetvalue(res, i, j));
                            strcat(resultBuffer, "   ");
                        }
                        
                        // Ajouter une nouvelle ligne après chaque enregistrement
                        strcat(resultBuffer, "\n");
                    }

                    // Ajouter une ligne vide pour séparer les enregistrements du menu
                    strcat(resultBuffer, "\n");

                    // Ajouter le menu à la fin de la chaîne résultante
                    strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");

                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));
                }
                // Si le client n'a pas les droits d'afficher tous les logements
                else {
                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage des biens du proprietaire, Echec, id_compte : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_compte);

                    // Ajouter le message d'erreur pour le client
                    strcat(resultBuffer, "\nVous ne possedez pas les permissions pour afficher tous les logements\n");

                    // Ajouter une ligne vide pour séparer les enregistrements du menu
                    strcat(resultBuffer, "\n");

                    // Ajouter le menu à la fin de la chaîne résultante
                    strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");

                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));
                }
            }
            // Si le client a taper 2 pour afficher tous les logements
            else if (strcmp(buffer, "2") == 0) {
                // Si le client a les droits pour afficher les logements
                if (privilegeDeux == 1) {
                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de tous les biens, Succes\n", dateTimeStr, get_client_ip(&conn_addr));

                    // Affichage du format d'un logement
                    strcat(resultBuffer, "\nid_logement   libelle_logement\n\n");

                    // Prépare la requête permettant de récupérer tous les logements
                    snprintf(query, sizeof(query), "SELECT id_logement, libelle_logement FROM sae._logement");

                    // Lance la requete et stock sont resultat dans la variable res 
                    res = PQexec(connexion, query);

                    // Vérification de l'exécution de la requête dans le cas ou elle ne fonctionne pas
                    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
                        fprintf(stderr, "Échec de l'exécution de la requête : %s", PQerrorMessage(connexion));
                        PQclear(res);
                        PQfinish(connexion);
                        exit(1);
                    }

                    // Recupere le nombre de colonne et de ligne que la requete nous a renvoyer
                    numRows = PQntuples(res);
                    numCols = PQnfields(res);

                    // Parcours de res pour récupérer toutes les valeurs en résultat
                    for (i = 0; i < numRows; i++) {
                        for (j = 0; j < numCols; j++) {
                            // Ajouter chaque attribut suivi d'un séparateur à la chaîne résultante
                            strcat(resultBuffer, PQgetvalue(res, i, j));
                            strcat(resultBuffer, "   ");
                        }

                        // Ajouter une ligne vide pour séparer les enregistrements du menu
                        strcat(resultBuffer, "\n");
                    }

                    // Ajouter une ligne vide pour séparer les enregistrements du menu
                    strcat(resultBuffer, "\n");

                    // Ajouter le menu à la fin de la chaîne résultante
                    strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");


                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));
                }
                // Si le client n'a pas le droit d'afficher ses logements
                else {
                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de tous les biens, Echec\n", dateTimeStr, get_client_ip(&conn_addr));

                    // Ajouter le message d'erreur pour le client
                    strcat(resultBuffer, "\nVous ne possedez pas les permissions pour afficher tous les logements\n");

                    // Ajouter une ligne vide pour séparer les enregistrements du menu
                    strcat(resultBuffer, "\n");

                    // Ajouter le menu à la fin de la chaîne résultante
                    strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");


                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));
                }
            }
            // Si le client a taper 3 pour afficher le calendrier de disponibilite d'un logement
            else if (strcmp(buffer, "3") == 0) {
                // Partie pour récupérer l'id du logement que le client ceut consulter
                write(cnx, "\nQuel est l'id du logement que vous souhaitez consulter : ", strlen("\nQuel est l'id du logement que vous souhaitez consulter : "));
                bytesRead = read(cnx, buffer, sizeof(buffer) - 1);
                // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
                buffer[bytesRead] = '\0';
                snprintf(id_logement, sizeof(id_logement), "%s", buffer);

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));

                // Partie pour récupérer la date de début pour la période qui va être consulter
                write(cnx, "\nQuel est la date de debut pour la periode de consultation (format date : YYYY-MM-DD) : ", strlen("\nQuel est la date de debut pour la periode de consultation (format date : YYYY-MM-DD) : "));
                bytesRead = read(cnx, buffer, sizeof(buffer) - 1);
                // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
                buffer[bytesRead] = '\0';

                // Si le client n'a pas donne de date de debut au bon format
                if (!valideDateFormat(buffer)) {
                    strcat(resultBuffer, "\nLe format de la date de debut n'est pas bon (YYYY-MM-DD)\n\n");

                    // Ajouter le menu à la fin de la chaîne résultante
                    strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");

                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));

                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de la disponibilite d'un bien sur une periode, Echec, id_logement : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_logement);
                }
                else {
                    snprintf(date_debut, sizeof(date_debut), "%s", buffer);

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));

                    // Partie pour récupérer la date de fin pour la période qui va être consulter
                    write(cnx, "\nQuel est la date de fin pour la periode de consultation (format date : YYYY-MM-DD) : ", strlen("\nQuel est la date de fin pour la periode de consultation (format date : YYYY-MM-DD) : "));
                    bytesRead = read(cnx, buffer, sizeof(buffer) - 1);
                    // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
                    buffer[bytesRead] = '\0';

                    // Si le client n'a pas donne de date de fin au bon format
                    if (!valideDateFormat(buffer)) {
                        strcat(resultBuffer, "\nLe format de la date de fin n'est pas bon (YYYY-MM-DD)\n\n");

                        // Ajouter le menu à la fin de la chaîne résultante
                        strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");

                        // Envoyer la chaîne résultante une seule fois
                        write(cnx, resultBuffer, strlen(resultBuffer));

                        // Réinitialiser le contenu du buffer
                        memset(buffer, 0, sizeof(buffer));

                        // Enregistre dans verdose.txt l'action faites par le client
                        snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de la disponibilite d'un bien sur une periode, Echec, id_logement : %s, date_debut : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_logement, date_debut);
                    }
                    else {
                        snprintf(date_fin, sizeof(date_fin), "%s", buffer);

                        // Réinitialiser le contenu du buffer
                        memset(buffer, 0, sizeof(buffer));

                        // Prépare la requête permettant de récupérer tous les logements
                        snprintf(query, sizeof(query), "SELECT date_jour FROM sae._jour WHERE id_logement = '%s' AND date_jour BETWEEN '%s' AND '%s' AND disponible = true", id_logement, date_debut, date_fin);

                        // Lance la requete et stock sont resultat dans la variable res 
                        res = PQexec(connexion, query);

                        // Vérification de l'exécution de la requête dans le cas ou elle ne fonctionne pas
                        if (PQresultStatus(res) != PGRES_TUPLES_OK) {
                            fprintf(stderr, "Échec de l'exécution de la requête : %s", PQerrorMessage(connexion));
                            PQclear(res);
                            PQfinish(connexion);
                            exit(1);
                        }

                        // Recupere le nombre de colonne et de ligne que la requete nous a renvoyer
                        numRows = PQntuples(res);
                        numCols = PQnfields(res);

                        // Ajouter une ligne vide pour séparer les enregistrements du menu
                        strcat(resultBuffer, "\nVoici le calndrier des disponibilité : \n\n");

                        // Parcours de res pour récupérer toutes les valeurs en résultat
                        for (i = 0; i < numRows; i++) {
                            for (j = 0; j < numCols; j++) {
                                // Ajouter chaque attribut suivi d'un séparateur à la chaîne résultante
                                strcat(resultBuffer, PQgetvalue(res, i, j));
                                strcat(resultBuffer, "\t");
                            }
                            // Ajouter une nouvelle ligne après chaque enregistrement
                            strcat(resultBuffer, "\n");
                        }

                        // Ajouter une ligne vide pour séparer les enregistrements du menu
                        strcat(resultBuffer, "\n");

                        // Ajouter le menu à la fin de la chaîne résultante
                        strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");


                        // Envoyer la chaîne résultante une seule fois
                        write(cnx, resultBuffer, strlen(resultBuffer));

                        // Réinitialiser le contenu du buffer
                        memset(buffer, 0, sizeof(buffer));
                        
                        // Enregistre dans verdose.txt l'action faites par le client
                        snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de la disponibilite d'un bien sur une periode, Succes, id_logement : %s, date_debut : %s, date_fin : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_logement, date_debut, date_fin);
                    }
                }
            }
            // Si le client a taper 4 pour mettre a jour le calendrier de diponibilite d'un logement
            else if(strcmp(buffer, "4") == 0) {
                // Partie pour récupérer l'id du logement que le client ceut consulter
                write(cnx, "\nQuel est l'id du logement que vous souhaitez consulter : ", strlen("\nQuel est l'id du logement que vous souhaitez consulter : "));
                bytesRead = read(cnx, buffer, sizeof(buffer) - 1);
                // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
                buffer[bytesRead] = '\0';
                snprintf(id_logement, sizeof(id_logement), "%s", buffer);

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));

                // Partie pour récupérer la date de début pour la période qui va être consulter
                write(cnx, "\nQuel est la date de debut pour la periode de consultation (format date : YYYY-MM-DD) : ", strlen("\nQuel est la date de debut pour la periode de consultation (format date : YYYY-MM-DD) : "));
                bytesRead = read(cnx, buffer, sizeof(buffer) - 1);
                // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
                buffer[bytesRead] = '\0';

                // Si le client n'a pas donne de date de debut au bon format
                if (!valideDateFormat(buffer)) {
                    strcat(resultBuffer, "\nLe format de la date de debut n'est pas bon (YYYY-MM-DD)\n\n");

                    // Ajouter le menu à la fin de la chaîne résultante
                    strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");

                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));

                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mise en indisponibilite d'un bien sur une periode, Echec, id_logement : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_logement);
                }
                else {
                    snprintf(date_debut, sizeof(date_debut), "%s", buffer);

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));

                    // Partie pour récupérer la date de fin pour la période qui va être consulter
                    write(cnx, "\nQuel est la date de fin pour la periode de consultation (format date : YYYY-MM-DD) : ", strlen("\nQuel est la date de fin pour la periode de consultation (format date : YYYY-MM-DD) : "));
                    bytesRead = read(cnx, buffer, sizeof(buffer) - 1);
                    // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
                    buffer[bytesRead] = '\0';
                    
                    // Si le client n'a pas donne de date de fin au bon format
                    if (!valideDateFormat(buffer)) {
                        strcat(resultBuffer, "\nLe format de la date de fin n'est pas bon (YYYY-MM-DD)\n\n");

                        // Ajouter le menu à la fin de la chaîne résultante
                        strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");

                        // Envoyer la chaîne résultante une seule fois
                        write(cnx, resultBuffer, strlen(resultBuffer));

                        // Réinitialiser le contenu du buffer
                        memset(buffer, 0, sizeof(buffer));

                        // Enregistre dans verdose.txt l'action faites par le client
                        snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mise en indisponibilite d'un bien sur une periode, Echec, id_logement : %s, date_debut : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_logement, date_debut);
                    }
                    else {
                        snprintf(date_fin, sizeof(date_fin), "%s", buffer);

                        // Réinitialiser le contenu du buffer
                        memset(buffer, 0, sizeof(buffer));

                        // Prépare la requête permettant de récupérer tous les logements
                        snprintf(query, sizeof(query), "UPDATE sae._jour SET disponible = false WHERE id_logement = '%s' AND date_jour BETWEEN '%s' AND '%s';", id_logement, date_debut, date_fin);

                        // Lance la requete et stock sont resultat dans la variable res 
                        res = PQexec(connexion, query);

                        // Vérifier le résultat de la requête
                        if (PQresultStatus(res) != PGRES_COMMAND_OK) {
                            fprintf(stderr, "Échec de la requête : %s", PQerrorMessage(connexion));

                            // Enregistre dans verdose.txt l'action faites par le client
                            snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mise en indisponibilite d'un bien sur une periode, Echec, id_logement : %s, date_debut : %s, date_fin : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_logement, date_debut, date_fin);
                        } else {
                            // La mise à jour a réussi
                            printf("Mise à jour réussie.\n");

                            // Enregistre dans verdose.txt l'action faites par le client
                            snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mise en indisponibilite d'un bien sur une periode, Succes, id_logement : %s, date_debut : %s, date_fin : %s\n", dateTimeStr, get_client_ip(&conn_addr), id_logement, date_debut, date_fin);
                        }

                        // Ajouter le menu à la fin de la chaîne résultante
                        strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");

                        // Envoyer la chaîne résultante une seule fois
                        write(cnx, resultBuffer, strlen(resultBuffer));

                        // Réinitialiser le contenu du buffer
                        memset(buffer, 0, sizeof(buffer));
                    }
                }
            }
            // Si le client a taper 5 pour se deconnecter
            else if (strcmp(buffer, "5") == 0) {
                // Enregistre dans verdose.txt l'action faites par le client
                snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Fin de la connexion entre le client et le serveur\n", dateTimeStr, get_client_ip(&conn_addr));

                // Ecriture de l'action faites par le client dans verdose
                if (fputs(string_logs, inputFile) == EOF) {
                    perror("Erreur lors de l'écriture dans le fichier");
                    exit(0);
                }
                
                // Vide le contenue de inputFile
                fflush(inputFile);

                // Réinitialiser le contenu de string_logs
                memset(string_logs, 0, sizeof(string_logs));

                // Déconnexion du client
                printf("Client deconnecte\n");
                close(cnx);
                break;
            }
            // Si le client a taper autre chose
            else {
                strcat(resultBuffer, "\n\nVeuillez entrer une action entre 1 et 5\n\n");

                // Ajouter le menu à la fin de la chaîne résultante
                strcat(resultBuffer, "1) Afficher tous les biens (si vous etes avez les privileges)\n2) Afficher vos biens (si vous avez les privileges)\n3) Afficher le calendrier de disponibilite d'un bien sur une periode\n4) Mettre en indisponibilite un bien sur une periode\n5) Arreter la connexion avec le serveur\n\nChoisissez votre action : ");

                // Envoyer la chaîne résultante une seule fois
                write(cnx, resultBuffer, strlen(resultBuffer));

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));

                // Enregistre dans verdose.txt l'action faites par le client
                snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mauvaise choix d'action ou erreur de saisis\n", dateTimeStr, get_client_ip(&conn_addr));
            }

            // Ecriture de l'action faites par le client dans verdose
            if (fputs(string_logs, inputFile) == EOF) {
                perror("Erreur lors de l'écriture dans le fichier");
                exit(0);
            }
            
            // Vide le contenue de inputFile
            fflush(inputFile);

            // Réinitialiser le contenu de string_logs
            memset(string_logs, 0, sizeof(string_logs));

            // Réinitialiser le contenu du buffer
            memset(buffer, 0, sizeof(buffer));
        }
    }

    // Fermeture du fichier contenant les logs
    fclose(inputFile);

    // Libération des résultats et fermeture de la connexion a la base de données
    PQclear(res);
    PQfinish(connexion);

    return 0;
}