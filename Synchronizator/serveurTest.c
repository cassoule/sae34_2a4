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

// Fonction pour formater la date (remplace '/' par '-')
void formatDateString(const char *date, char *formattedDate) {
    int i;
    for (i = 0; i < 10; i++) {
        if (date[i] == '/') {
            formattedDate[i] = '-'; // Remplacer les barres obliques par des tirets
        } else {
            formattedDate[i] = date[i]; // Conserver les autres caractères
        }
    }
    formattedDate[10] = '\0'; // Terminer la chaîne avec un caractère nul
}

// Fonction pour valider le format de la date (YYYY-MM-DD)
int valideDateFormat(const char *date) {
    // Vérifier la longueur de la date
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

// Fonction pour extraire la valeur associée à une clé spécifique dans le JSON
char* extractValue(const char *jsonData, const char *key) {
    // Chercher la position de la clé dans le JSON
    const char *keyPos = strstr(jsonData, key);
    if (keyPos == NULL) {
        printf("La clé \"%s\" n'a pas été trouvée dans le JSON.\n", key);
        return NULL;
    }

    // Chercher la position du début de la valeur associée à la clé
    const char *valueStart = strchr(keyPos, ':');
    if (valueStart == NULL) {
        printf("Impossible de trouver la valeur associée à la clé \"%s\".\n", key);
        return NULL;
    }

    // Avancer jusqu'au début de la valeur (après le caractère ':')
    valueStart++;
    while (*valueStart == ' ' || *valueStart == '\"') {
        valueStart++; // Ignorer les espaces et les guillemets éventuels
    }

    // Trouver la fin de la valeur
    const char *valueEnd = valueStart;
    while (*valueEnd != ',' && *valueEnd != '}' && *valueEnd != '\0') {
        valueEnd++;
    }

    // Calculer la longueur de la valeur
    size_t valueLength = valueEnd - valueStart;

    // Allouer de la mémoire pour la valeur extraite
    char *value = (char *)malloc((valueLength + 1) * sizeof(char));
    if (value == NULL) {
        printf("Erreur lors de l'allocation de mémoire pour la valeur de la clé \"%s\".\n", key);
        return NULL;
    }

    // Copier la valeur extraite dans la nouvelle chaîne de caractères
    strncpy(value, valueStart, valueLength);
    value[valueLength] = '\0'; // Terminer la chaîne avec un caractère nul

    return value;
}

// Fonction pour transformer une période au format NX en une date
char* calculerDate(char* periode) {
    // Extraire la valeur numérique et l'unité de la période
    int valeur;
    char unite;
    sscanf(periode, "%d%c", &valeur, &unite);

    // Obtenir l'instant T actuel
    time_t maintenant = time(NULL);
    struct tm *dateActuelle = localtime(&maintenant);

    // Ajouter la période à la date actuelle
    switch (unite) {
        case 'D':
            dateActuelle->tm_mday += valeur;
            break;
        case 'W':
            dateActuelle->tm_mday += valeur * 7;
            break;
        case 'M':
            dateActuelle->tm_mon += valeur;
            break;
        default:
            printf("Unité de période invalide.\n");
            return NULL;
    }

    // Convertir la nouvelle date en une chaîne de caractères
    char* nouvelleDate = (char*)malloc(11 * sizeof(char)); // Format AAAA-MM-JJ
    strftime(nouvelleDate, 11, "%Y-%m-%d", dateActuelle);

    return nouvelleDate;
}

// Fonction pour obtenir l'horodatage actuel au format DD_MM_YYYY_hh_mm_ss
void getCurrentTimestamp(char *timestamp) {
    time_t rawtime;
    struct tm *info;

    time(&rawtime);
    info = localtime(&rawtime);

    strftime(timestamp, 20, "%d_%m_%Y_%H_%M_%S", info);
}

// Fonction pour obtenir l'horodatage actuel au format DD_MM_YYYY
void getCurrentDate(char *date) {
    time_t rawtime;
    struct tm *info;

    time(&rawtime);
    info = localtime(&rawtime);

    strftime(date, 11, "%d_%m_%Y", info);
}

// Fonction pour obtenir le chemin complet du fichier journal
void getLogFilePath(char *logFilePath, const char *logDirectory) {
    char currentDate[11];
    getCurrentDate(currentDate);
    sprintf(logFilePath, "%slogs-%s.log", logDirectory, currentDate);
}

// Fonction pour écrire dans le fichier journal
void writeToLog(const char *logMessage, const char *logDirectory) {
    char logFilePath[100];
    getLogFilePath(logFilePath, logDirectory);

    FILE *logFile = fopen(logFilePath, "a"); // Ouvre le fichier en mode append
    if (logFile == NULL) {
        fprintf(stderr, "Erreur lors de l'ouverture du fichier journal.\n");
        return;
    }

    // Obtient l'horodatage actuel au format HH:MM:SS
    time_t rawtime;
    struct tm *timeinfo;
    time(&rawtime);
    timeinfo = localtime(&rawtime);
    char timestamp[9];
    strftime(timestamp, sizeof(timestamp), "%H:%M:%S", timeinfo);

    // Écrit le message dans le fichier journal avec l'horodatage
    fprintf(logFile, "[%s] %s\n", timestamp, logMessage);

    fclose(logFile);
}

int main(int argc, char *argv[]) {
    // Configuration serveur pour ecouter les requetes
    int port = atoi(argv[1]);
    char adresse_ip[16];
    strcpy(adresse_ip, argv[2]);
    int sock, ret;
    struct sockaddr_in addr;
    int size, cnx;
    struct sockaddr_in conn_addr;

    sock = socket(AF_INET, SOCK_STREAM, 0);

    addr.sin_addr.s_addr = inet_addr(adresse_ip);
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
    char string_logs[MAX_STRING_LOGS];
    int api = 0;

    // Initialisation des variables utilisé pour récupérer les privilèges du token
    int privilegeUn = 0;
    int privilegeDeux = 0;

    // Recupere la date et l'heure
    char dateTimeStr[20];
    getDateHeure(dateTimeStr);

    // Initialisation des variables necessaires pour faire des requêtes à la base de données
    char query[8192]; // String stockant les requetes faites pour la base de données
    int numRows, numCols; // Entier permettant de parcourir les résultats de chaque requêtes faites à la base de données
    int i, j; // Entier permettant de boucler sur les résultats de chaque requêtes faites à la base données

    // Initialisation des variables stockant les informations sur ce que le client veut faire
    char commande[20];
    char id_logement[2056];
    char date_debut[11];
    char date_fin[11];

    // Initialisation des variable pour apirator
    char chemin[MAX_BUFFER_SIZE];
    char duree[MAX_BUFFER_SIZE];

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

        // Lecture du buffer envoyé par le client / stocker dans la variable buffer
        ssize_t buffer_len = read(cnx, buffer, sizeof(buffer) - 1);
        if (buffer_len <= 0) {
            perror("read");
            break;
        }

        // Supprime le caractère de nouvelle ligne à la fin du buffer, s'il est présent
        while (buffer_len > 0 && (buffer[buffer_len - 1] == '\n' || buffer[buffer_len - 1] == '\r')) {
            buffer_len--;
        }

        // Ajoute le caractère de fin de chaîne
        buffer[buffer_len] = '\0';
        
        // Utiliser strtok pour séparer les données en fonction des tirets "-"
        char *caractere = strtok(buffer, "-");
        while (caractere != NULL) {
            // Recherche de l'espace dans le caractere
            char *space_pos = strchr(caractere, ' ');

            if (space_pos != NULL) {
                // Calcul de la longueur de la partie avant l'espace
                int part1_len = space_pos - caractere;

                // Allocation d'une mémoire pour stocker la partie avant l'espace
                char part1[part1_len + 1];

                // Copie de la partie avant l'espace dans la variable part1
                strncpy(part1, caractere, part1_len);
                part1[part1_len] = '\0'; // Ajout du caractère de fin de chaîne

                // Avancer jusqu'au caractère suivant après l'espace
                char *value = space_pos + 1;

                ssize_t longueur;

                if (strcmp(part1, "a") == 0) {
                    strcpy(commande, value);
                    
                    longueur = strlen(commande);
                    if (longueur <= 0) {
                        perror("longueur est egal a 0");
                        break;
                    }

                    // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
                    while (longueur > 0 && (commande[longueur - 1] == '\n' || commande[longueur - 1] == '\r')) {
                        longueur--;
                    }

                    // Ajoute le caractère de fin de chaîne
                    commande[longueur-1] = '\0';
                }
                else if (strcmp(part1, "l") == 0) {
                    strcpy(id_logement, value);

                    longueur = strlen(id_logement);
                    if (longueur <= 0) {
                        perror("longueur est egal a 0");
                        break;
                    }

                    // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
                    while (longueur > 0 && (id_logement[longueur - 1] == '\n' || id_logement[longueur - 1] == '\r')) {
                        longueur--;
                    }

                    // Ajoute le caractère de fin de chaîne
                    id_logement[longueur-1] = '\0';
                }
                else if (strcmp(part1, "d") == 0) {
                    char date_debut_tmp[11];

                    strcpy(date_debut_tmp, value);

                    formatDateString(date_debut_tmp, date_debut);
                }
                else if (strcmp(part1, "f") == 0) {
                    char date_fin_tmp[11];

                    strcpy(date_fin_tmp, value);

                    formatDateString(date_fin_tmp, date_fin);
                }
                else if (strcmp(part1, "u") == 0) {
                    strcpy(token, value);

                    longueur = strlen(token);
                    if (longueur <= 0) {
                        perror("longueur est egal a 0");
                        break;
                    }

                    // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
                    while (longueur > 0 && (token[longueur - 1] == '\n' || token[longueur - 1] == '\r')) {
                        longueur--;
                    }

                    // Ajoute le caractère de fin de chaîne
                    token[longueur-1] = '\0';
                }
                else if (strcmp(part1, "./apirator") == 0) {
                    api = 1;
                }
                else if (strcmp(part1, "c") == 0) {
                    strcpy(chemin, value);

                    longueur = strlen(chemin);
                    if (longueur <= 0) {
                        perror("longueur est egal a 0");
                        break;
                    }

                    // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
                    while (longueur > 0 && (chemin[longueur - 1] == '\n' || chemin[longueur - 1] == '\r')) {
                        longueur--;
                    }

                    // Ajoute le caractère de fin de chaîne
                    chemin[longueur-1] = '\0';
                }
                else if (strcmp(part1, "n") == 0) {
                    strcpy(duree, value);

                    longueur = strlen(duree);
                    if (longueur <= 0) {
                        perror("longueur est egal a 0");
                        break;
                    }

                    // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
                    while (longueur > 0 && (duree[longueur - 1] == '\n' || duree[longueur - 1] == '\r')) {
                        longueur--;
                    }

                    // Ajoute le caractère de fin de chaîne
                    duree[longueur] = '\0';
                } 
            }
            
            // Avancer au prochain caractere
            caractere = strtok(NULL, "-");
        }

        if (api == 0) {
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
                snprintf(string_logs, sizeof(string_logs), "Connexion d'un client, Echec : token invalide, Heure : %s, IP : %s, Token : %s, Port : %d\n", dateTimeStr, adresse_ip, token, port);
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

            // Ecriture de l'en-tete dans verdose.txt lorsqu'un client se connecte sur le serveur
            snprintf(string_logs, sizeof(string_logs), "Connexion d'un client, Succes, Heure : %s, IP : %s, Token : %s, Port : %d\n", dateTimeStr, adresse_ip, token, port);
            if (fputs(string_logs, inputFile) == EOF) {
                perror("Erreur lors de l'écriture dans le fichier");
                exit(0);
            }
            
            // Vide le contenue de inputFile
            fflush(inputFile);

            // Réinitialiser le contenu de string_logs
            memset(string_logs, 0, sizeof(string_logs));
        }
        else if (api == 1) {
            // Ouvrir le fichier JSON
            FILE *file = fopen(chemin, "r");
            if (file == NULL) {
                perror("Impossible d'ouvrir le fichier");
                return 1;
            }

            // Lire le contenu du fichier JSON
            fseek(file, 0, SEEK_END);
            long fileSize = ftell(file);
            fseek(file, 0, SEEK_SET);

            char *jsonData = (char *)malloc(fileSize + 1);
            if (jsonData == NULL) {
                perror("Erreur lors de l'allocation de mémoire");
                fclose(file);
                return 1;
            }

            fread(jsonData, 1, fileSize, file);
            fclose(file);

            // Ajouter le caractère de fin de chaîne
            jsonData[fileSize] = '\0';

            // Extraire les valeurs des clés spécifiées
            char* cle_api = extractValue(jsonData, "\"cle_api\"");
            int longueur = strlen(cle_api);
            if (longueur <= 0) {
                perror("longueur est egal a 0");
                break;
            }
            // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
            while (longueur > 0 && (cle_api[longueur - 1] == '\n' || cle_api[longueur - 1] == '\r')) {
                longueur--;
            }
            // Ajoute le caractère de fin de chaîne
            cle_api[longueur-1] = '\0';


            char* bien_cible = extractValue(jsonData, "\"bien_cible\"");
            longueur = strlen(bien_cible);
            if (longueur <= 0) {
                perror("longueur est egal a 0");
                break;
            }
            // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
            while (longueur > 0 && (bien_cible[longueur - 1] == '\n' || bien_cible[longueur - 1] == '\r')) {
                longueur--;
            }
            // Ajoute le caractère de fin de chaîne
            bien_cible[longueur-1] = '\0';


            char* chemin_logs = extractValue(jsonData, "\"chemin_logs\"");
            longueur = strlen(chemin_logs);
            if (longueur <= 0) {
                perror("longueur est egal a 0");
                break;
            }
            // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
            while (longueur > 0 && (chemin_logs[longueur - 1] == '\n' || chemin_logs[longueur - 1] == '\r')) {
                longueur--;
            }
            // Ajoute le caractère de fin de chaîne
            chemin_logs[longueur-1] = '\0';


            char* chemin_donnees_api = extractValue(jsonData, "\"chemin_donnees_api\"");
            longueur = strlen(chemin_donnees_api);
            if (longueur <= 0) {
                perror("longueur est egal a 0");
                break;
            }
            // Supprime le caractère de nouvelle ligne à la fin de la commande, s'il est présent
            while (longueur > 0 && (chemin_donnees_api[longueur - 1] == '\n' || chemin_donnees_api[longueur - 1] == '\r')) {
                longueur--;
            }
            // Ajoute le caractère de fin de chaîne
            chemin_donnees_api[longueur-4] = '\0';

            // Prepare la requete SQL permettant de recuperer l'id_compte du client se connectant au serveur
            snprintf(query, sizeof(query), "SELECT privilege_3 FROM sae._cle WHERE token = '%s'", cle_api);

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
                    if (PQgetvalue(res, i, j)[0] == 't') {
                        privilegeUn = 1;
                    }
                }
            }

            if (privilegeUn == 1) {
                char* date_fin = calculerDate(duree);

                // Prépare la requête permettant de récupérer tous les logements
                snprintf(query, sizeof(query), "SELECT date_jour FROM sae._jour WHERE id_logement = '%s' AND date_jour BETWEEN '%s' AND '%s' AND disponible = true", bien_cible, date_debut, date_fin);

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

                // Création de la chaîne JSON
                char jsonStr[4096] = ""; // Taille suffisamment grande pour stocker la chaîne JSON
                strcat(jsonStr, "[\n");

                // Parcours de res pour récupérer toutes les valeurs en résultat
                for (i = 0; i < numRows; i++) {
                    strcat(jsonStr, "{\n");
                    for (j = 0; j < numCols; j++) {
                        // Récupérer le nom de la colonne
                        char* columnName = PQfname(res, j);
                        // Récupérer la valeur de chaque colonne
                        char* value = PQgetvalue(res, i, j);
                        // Ajouter la paire clé-valeur à l'objet JSON
                        strcat(jsonStr, "\"");
                        strcat(jsonStr, columnName);
                        strcat(jsonStr, "\":\"");
                        strcat(jsonStr, value);
                        strcat(jsonStr, "\",\n");
                    }
                    // Supprimer la virgule après le dernier élément
                    jsonStr[strlen(jsonStr) - 2] = '\n';
                    strcat(jsonStr, "},\n");
                }
                // Supprimer la virgule après le dernier élément
                jsonStr[strlen(jsonStr) - 2] = '\n';
                strcat(jsonStr, "]\n");

                // Stocker la chaîne JSON dans un fichier
                char nom_fichier_json[MAX_BUFFER_SIZE];

                // Obtenir l'horodatage actuel
                char timestamp[20];
                getCurrentTimestamp(timestamp);

                // Construire le nom du fichier JSON
                sprintf(nom_fichier_json, "%sapirator-%s.json", chemin_donnees_api, timestamp);

                FILE* fichier = fopen(nom_fichier_json, "w");
                if (fichier != NULL) {
                    fputs(jsonStr, fichier);
                    fclose(fichier);

                    // Ecriture des informations dans le journal de log
                    writeToLog(adresse_ip, chemin_logs);
                    writeToLog(cle_api, chemin_logs);
                    writeToLog(bien_cible, chemin_logs);
                    writeToLog(chemin_donnees_api, chemin_logs);
                    writeToLog(nom_fichier_json, chemin_logs);
                    writeToLog("Enregistrement réussi de la requete", chemin_logs);
                } else {
                    writeToLog(adresse_ip, chemin_logs);
                    writeToLog(cle_api, chemin_logs);
                    writeToLog(bien_cible, chemin_logs);
                    writeToLog(chemin_donnees_api, chemin_logs);
                    writeToLog("Erreur lors de l'ouverture du fichier json", chemin_logs);
                }
            }
            else {
                writeToLog(adresse_ip, chemin_logs);
                writeToLog(cle_api, chemin_logs);
                writeToLog(bien_cible, chemin_logs);
                writeToLog(chemin_donnees_api, chemin_logs);
                writeToLog("Utilisateur n'ayant pas les droits pour utiliser l'API", chemin_logs);
            }

            // Libérer la mémoire allouée pour les valeurs
            free(jsonData);
            free(cle_api);
            free(bien_cible);
            free(chemin_logs);
            free(chemin_donnees_api);
        }

        if (strcmp(commande, "vosBiens") == 0) {
            // Si le client a les droits pour afficher tous ses logements
            if (privilegeUn == 1) {
                // Enregistre dans verdose.txt l'action faites par le client
                snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage des biens du proprietaire, Succès, id_compte : %s\n", dateTimeStr, adresse_ip, id_compte);

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

                // Envoyer la chaîne résultante une seule fois
                write(cnx, resultBuffer, strlen(resultBuffer));

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));
            }
            // Si le client n'a pas les droits d'afficher tous les logements
            else {
                // Enregistre dans verdose.txt l'action faites par le client
                snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage des biens du proprietaire, Echec, id_compte : %s\n", dateTimeStr, adresse_ip, id_compte);

                // Ajouter le message d'erreur pour le client
                strcat(resultBuffer, "\nVous ne possedez pas les permissions pour afficher tous les logements\n");

                // Ajouter une ligne vide pour séparer les enregistrements du menu
                strcat(resultBuffer, "\n");

                // Envoyer la chaîne résultante une seule fois
                write(cnx, resultBuffer, strlen(resultBuffer));

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));
            }
        }
        else if (strcmp(commande, "tousBiens") == 0) {
            // Si le client a les droits pour afficher les logements
            if (privilegeDeux == 1) {
                // Enregistre dans verdose.txt l'action faites par le client
                snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de tous les biens, Succes\n", dateTimeStr, adresse_ip);

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

                // Envoyer la chaîne résultante une seule fois
                write(cnx, resultBuffer, strlen(resultBuffer));

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));
            }
            // Si le client n'a pas le droit d'afficher ses logements
            else {
                // Enregistre dans verdose.txt l'action faites par le client
                snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de tous les biens, Echec\n", dateTimeStr, adresse_ip);

                // Ajouter le message d'erreur pour le client
                strcat(resultBuffer, "\nVous ne possedez pas les permissions pour afficher tous les logements\n");

                // Ajouter une ligne vide pour séparer les enregistrements du menu
                strcat(resultBuffer, "\n");

                // Envoyer la chaîne résultante une seule fois
                write(cnx, resultBuffer, strlen(resultBuffer));

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));
            }
        }
        else if (strcmp(commande, "consulterCalendrier") == 0) {
            // Si le client n'a pas donne de date de debut au bon format
            if (!valideDateFormat(date_debut)) {
                strcat(resultBuffer, "\nLe format de la date de debut n'est pas bon (YYYY-MM-DD)\n\n");

                // Envoyer la chaîne résultante une seule fois
                write(cnx, resultBuffer, strlen(resultBuffer));

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));

                // Enregistre dans verdose.txt l'action faites par le client
                snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de la disponibilite d'un bien sur une periode, Echec, id_logement : %s\n", dateTimeStr, adresse_ip, id_logement);
            }
            else {
                // Si le client n'a pas donne de date de fin au bon format
                if (!valideDateFormat(date_fin)) {
                    strcat(resultBuffer, "\nLe format de la date de fin n'est pas bon (YYYY-MM-DD)\n\n");

                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));

                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de la disponibilite d'un bien sur une periode, Echec, id_logement : %s, date_debut : %s\n", dateTimeStr, adresse_ip, id_logement, date_debut);
                }
                else {
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
                    strcat(resultBuffer, "\nVoici le calendrier des disponibilité : \n\n");

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

                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));
                    
                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Affichage de la disponibilite d'un bien sur une periode, Succes, id_logement : %s, date_debut : %s, date_fin : %s\n", dateTimeStr, adresse_ip, id_logement, date_debut, date_fin);
                }
            }
        }
        else if (strcmp(commande, "modifierCalendrier") == 0) {
            // Si le client n'a pas donne de date de debut au bon format
            if (!valideDateFormat(date_debut)) {
                strcat(resultBuffer, "\nLe format de la date de debut n'est pas bon (YYYY/MM/DD)\n\n");

                // Envoyer la chaîne résultante une seule fois
                write(cnx, resultBuffer, strlen(resultBuffer));

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));

                // Enregistre dans verdose.txt l'action faites par le client
                snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mise en indisponibilite d'un bien sur une periode, Echec, id_logement : %s\n", dateTimeStr, adresse_ip, id_logement);
            }
            else {
                // Si le client n'a pas donne de date de fin au bon format
                if (!valideDateFormat(date_fin)) {
                    strcat(resultBuffer, "\nLe format de la date de fin n'est pas bon (YYYY/MM/DD)\n\n");

                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));

                    // Enregistre dans verdose.txt l'action faites par le client
                    snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mise en indisponibilite d'un bien sur une periode, Echec, id_logement : %s, date_debut : %s\n", dateTimeStr, adresse_ip, id_logement, date_debut);
                }
                else {
                    // Prépare la requête permettant de récupérer tous les logements
                    snprintf(query, sizeof(query), "UPDATE sae._jour SET disponible = false WHERE id_logement = '%s' AND date_jour BETWEEN '%s' AND '%s';", id_logement, date_debut, date_fin);

                    // Lance la requete et stock sont resultat dans la variable res 
                    res = PQexec(connexion, query);

                    // Vérifier le résultat de la requête
                    if (PQresultStatus(res) != PGRES_COMMAND_OK) {
                        fprintf(stderr, "Échec de la requête : %s", PQerrorMessage(connexion));

                        // Enregistre dans verdose.txt l'action faites par le client
                        snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mise en indisponibilite d'un bien sur une periode, Echec, id_logement : %s, date_debut : %s, date_fin : %s\n", dateTimeStr, adresse_ip, id_logement, date_debut, date_fin);
                    }
                    else {
                        // La mise à jour a réussi
                        printf("Mise à jour réussie.\n");

                        // Enregistre dans verdose.txt l'action faites par le client
                        snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mise en indisponibilite d'un bien sur une periode, Succes, id_logement : %s, date_debut : %s, date_fin : %s\n", dateTimeStr, adresse_ip, id_logement, date_debut, date_fin);
                    }

                    // Envoyer la chaîne résultante une seule fois
                    write(cnx, resultBuffer, strlen(resultBuffer));

                    // Réinitialiser le contenu du buffer
                    memset(buffer, 0, sizeof(buffer));
                }
            }
        }
        else {
            strcat(resultBuffer, "\n\nVeuillez entrer une action entre 1 et 5\n\n");

            // Envoyer la chaîne résultante une seule fois
            write(cnx, resultBuffer, strlen(resultBuffer));

            // Réinitialiser le contenu du buffer
            memset(buffer, 0, sizeof(buffer));

            // Enregistre dans verdose.txt l'action faites par le client
            snprintf(string_logs, sizeof(string_logs), "    -%s, %s : Mauvaise choix d'action ou erreur de saisis\n", dateTimeStr, adresse_ip);
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

        // Fermer la connexion avec le client
        close(cnx);
    }

    // Fermeture du fichier contenant les logs
    fclose(inputFile);

    // Libération des résultats et fermeture de la connexion a la base de données
    PQclear(res);
    PQfinish(connexion);

    return 0;
}