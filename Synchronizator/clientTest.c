#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/select.h>
#include <stdbool.h>

#define MAX_BUFFER_SIZE 2056

const char *verdose = "./verdose.txt";

// Procedure gerant l'affichage de la documentation du programme
void affichageAide(char *programName) {
    printf("Utilisation : %s -p <port> -i <adresse_ip> -u <token> -a <commande> -l <id_logement> -d <date_debut> -f <date_fin>\n", programName);
    printf("    --help       Documentation du programme\n");
    printf("    --verdose    Journal des logs\n");
}

int main(int argc, char *argv[]) {
    // Déclaration des variables
    int opt;
    int port = 0;
    char adresse_ip[16];
    char *token = NULL;
    char *pseudoProprietaire = NULL;
    char *action = NULL;
    bool connexion = true;

    char buffer[MAX_BUFFER_SIZE];
    ssize_t bytesRead;

    // Recherche les options specifiques dans la ligne de commande entre par le client
    for (int i=1; i<argc; i++) {
        // Si le client a mis en parametre --verdose alors on affiche le journal des logs
        if (strcmp(argv[i], "--verdose") == 0) {
            // Ouverture de verdose.txt pour enregistrer les logs
            FILE *inputFile = NULL;
            inputFile = fopen("verdose.txt", "r");

            // Si il y a un probleme a l'ouverture du fichier
            if (inputFile == NULL) {
                printf("Le fichier %s ne peut pas etre ouvert\n", verdose);
                exit(0);
            }
            // Sinon on peut lire le contenu de verdose.txt
            else {
                int c;
                
                while ((c = fgetc(inputFile)) != EOF) {
                    putchar(c);
                }
            }
            
            // On ferme verdose.txt
            fclose(inputFile);

            return 0;
        }
        // Si le client a mis en parametre --help alors on affiche la documentation
        else if (strcmp(argv[i], "--help") == 0) {
            affichageAide(argv[0]);
            return 0;
        }
    }

    // Initialisation des variables stockant les informations liés aux actions
    char commande[20];
    char id_logement[2056];
    char dateDebut[11];
    char dateFin[11];

    // Utilisation de getopt() pour parcourir les arguments de la ligne de commande
    while ((opt = getopt(argc, argv, "p:i:u:a:l:d:f:h")) != -1) {
        // Traitement de chaque parametres donne par le client
        switch (opt) {
            // Permet d'affecter a la variable port la valeur de -p / enregistre le port de connexion pour se connecter au serveur
            case 'p':
                port = atoi(optarg);
                break;
            // Permet d'affecter a la variable token la valeur de -u / enregistre le token d'identification pour se connecter a compte
            case 'u':
                token = optarg;
                break;
            case 'h':
                connexion = false;
                affichageAide(argv[0]);
                break;
            case 'i':
                strcpy(adresse_ip, optarg);
                break;
            case 'a':
                strcpy(commande, optarg);
                break;
            case 'l':
                strcpy(id_logement, optarg);
                break;
            case 'd':
                strcpy(dateDebut, optarg);
                break;
            case 'f':
                strcpy(dateFin, optarg);
                break;
            case '?':
                connexion = false;
                printf("Option inconnue: -%c\n", optopt);
                affichageAide(argv[0]);
                break;
            case ':':
                connexion = false;
                printf("Option -%c requiert un argument.\n", optopt);
                affichageAide(argv[0]);
                break;
            default:
                connexion = false;
                affichageAide(argv[0]);
                break;
        }
    }

    // Si il y a pas assez d'argument dans la ligne de commande
    if ((argc < 2) && (connexion == true)) {
        connexion = false;
        affichageAide(argv[0]);
    }

    // Si il n'y a pas eu de probleme lorsque le client a rentre la commande
    if (connexion == true) {
        // Création du socket
        int cnx = socket(AF_INET, SOCK_STREAM, 0);
        if (cnx == -1) {
            perror("socket");
            exit(EXIT_FAILURE);
        }

        // Configuration de l'adresse du serveur
        struct sockaddr_in server_address;
        server_address.sin_family = AF_INET;
        server_address.sin_port = htons(port);
        if (inet_pton(AF_INET, adresse_ip, &server_address.sin_addr) <= 0) {
            perror("inet_pton");
            close(cnx);
            exit(EXIT_FAILURE);
        }

        // Connexion au serveur
        if (connect(cnx, (struct sockaddr *)&server_address, sizeof(server_address)) == -1) {
            perror("connect");
            close(cnx);
            exit(EXIT_FAILURE);
        }

        // Déterminer la taille totale nécessaire pour stocker le contenu de argv
        int total_length = 0;
        for (int i = 0; i < argc; i++) {
            total_length += strlen(argv[i]) + 1; // +1 pour le caractère de séparation (espace ou null-terminator)
        }

        // Allouer de la mémoire pour la variable contenant tout le contenu de argv
        char *content = (char *)malloc(total_length);
        if (content == NULL) {
            fprintf(stderr, "Erreur d'allocation de mémoire.\n");
            return 1;
        }

        // Construire la chaîne contenant tout le contenu de argv
        int index = 0;
        for (int i = 0; i < argc; i++) {
            strcpy(content + index, argv[i]); // Copie de chaque argument
            index += strlen(argv[i]); // Mise à jour de l'index
            content[index++] = ' '; // Ajout d'un espace
        }
        content[index - 1] = '\0'; // Remplacer le dernier espace par un null-terminator

        // Envoi du contenu au serveur
        ssize_t bytes_sent = write(cnx, content, strlen(content));

        // Lecture des données du serveur
        bytesRead = read(cnx, buffer, sizeof(buffer) - 1);
        if (bytesRead == -1) {
            perror("read");
            close(cnx);
            exit(EXIT_FAILURE);
        }

        if (bytesRead == 0) {
            // Le serveur a fermé la connexion
            printf("La connexion avec le serveur a été fermée.\n");
        }

        // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
        buffer[bytesRead] = '\0';

        // Affichage des données reçues du serveur
        printf("%s", buffer);

        // Réinitialiser le contenu du buffer
        memset(buffer, 0, sizeof(buffer));

        // Libérer la mémoire allouée
        free(content);

        // Fermeture du socket
        close(cnx);
    }

    return 0;
}
