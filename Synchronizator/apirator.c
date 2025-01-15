#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/select.h>
#include <stdbool.h>

#define MAX_BUFFER_SIZE 2056

// Procedure gerant l'affichage de la documentation du programme
void affichageAide(char *programName) {
    printf("Utilisation : %s -p <port> -i <adresse_ip> -c <chemin JSON> -d <date_debut> -n <duree>\n", programName);
    printf("    --help       Documentation du programme\n");
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
        // Si le client a mis en parametre --help alors on affiche la documentation
        if (strcmp(argv[i], "--help") == 0) {
            affichageAide(argv[0]);
            return 0;
        }
    }

    // Initialisation des variables stockant les informations liés aux actions
    char chemin[MAX_BUFFER_SIZE];
    char date_debut[11];
    char duree[10];

    // Utilisation de getopt() pour parcourir les arguments de la ligne de commande
    while ((opt = getopt(argc, argv, "p:i:c:d:n:h")) != -1) {
        // Traitement de chaque parametres donne par le client
        switch (opt) {
            // Permet d'affecter a la variable port la valeur de -p / enregistre le port de connexion pour se connecter au serveur
            case 'p':
                port = atoi(optarg);
                break;
            case 'h':
                connexion = false;
                affichageAide(argv[0]);
                break;
            case 'i':
                strcpy(adresse_ip, optarg);
                break;
            case 'c':
                strcpy(chemin, optarg);
                break;
            case 'd':
                strcpy(date_debut, optarg);
                break;
            case 'n':
                strcpy(duree, optarg);
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
    if ((argc < 5) && (connexion == true)) {
        connexion = false;
        affichageAide(argv[0]);
    }

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

        // Réinitialiser le contenu du buffer
        memset(buffer, 0, sizeof(buffer));

        // Libérer la mémoire allouée
        free(content);

        // Fermeture du socket
        close(cnx);
    }

    return 0;
}