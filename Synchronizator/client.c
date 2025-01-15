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
    printf("Utilisation : %s -p <port> -u <token>\n", programName);
    printf("    --help       Documentation du programme\n");
    printf("    --verdose    Journal des logs\n");
}

int main(int argc, char *argv[]) {
    // Déclaration des variables
    int opt;
    int port = 0;
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

    // Utilisation de getopt() pour parcourir les arguments de la ligne de commande
    while ((opt = getopt(argc, argv, "p:u:h")) != -1) {
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
        if (inet_pton(AF_INET, "127.0.0.1", &server_address.sin_addr) <= 0) {
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

        // Envoi du nom d'utilisateur au serveur
        ssize_t bytes_sent = write(cnx, token, strlen(token));
        if (bytes_sent == -1) {
            perror("send");
            close(cnx);
            exit(EXIT_FAILURE);
        }

        if (action != NULL) {
            // Envoi de l'action au serveur
            bytes_sent = write(cnx, action, strlen(action));
            if (bytes_sent == -1) {
                perror("send");
                close(cnx);
                exit(EXIT_FAILURE);
            }

            // Envoi du pseudo propriétaire au serveur
            if (pseudoProprietaire != NULL) {
                // Envoi du pseudo propriétaire au serveur
                bytes_sent = write(cnx, pseudoProprietaire, strlen(pseudoProprietaire));
                if (bytes_sent == -1) {
                    perror("send");
                    close(cnx);
                    exit(EXIT_FAILURE);
                }
            }
        }

        // Section pour lire la réponse envoyer par le serveur
        while (1) {
            fd_set read_fds;
            struct timeval timeout;

            // Initialiser le descripteur de fichier et le timeout
            FD_ZERO(&read_fds);
            FD_SET(cnx, &read_fds);
            timeout.tv_sec = 2;  // Timeout de 2 secondes
            timeout.tv_usec = 0;

            // Utiliser select pour gérer le timeout
            int select_result = select(cnx + 1, &read_fds, NULL, NULL, &timeout);

            if (select_result == -1) {
                perror("select");
                close(cnx);
                exit(EXIT_FAILURE);
            }
            // Timeout, le serveur n'a pas répondu dans le délai spécifié
            else if (select_result == 0) {
                fprintf(stderr, "Le serveur n'a pas répondu dans le délai spécifié.\n");
                close(cnx);
                exit(EXIT_FAILURE);
            }

            // Partie lisant la réponse du serveur
            if (FD_ISSET(cnx, &read_fds)) {
                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));
                
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
                    break;
                }

                // Ajout d'un caractère nul à la fin pour assurer que la chaîne de caractères est terminée
                buffer[bytesRead] = '\0';

                // Affichage des données reçues du serveur
                printf("%s", buffer);

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));

                // Partie permettant au client d'écrire ce qu'il veut faire
                fgets(buffer, sizeof(buffer), stdin);
                buffer[strcspn(buffer, "\n")] = '\0';  // Suppression du retour à la ligne

                // Envoi de la commande au serveur
                ssize_t bytesSent = write(cnx, buffer, strlen(buffer));
                if (bytesSent == -1) {
                    perror("write");
                    break;
                }

                // Réinitialiser le contenu du buffer
                memset(buffer, 0, sizeof(buffer));
            }
        }

        // Fermeture du socket
        close(cnx);
    }

    return 0;
}
