# Documentation de l'Apirator

## Fichier de configuration

Ce script JSON joue un rôle crucial dans la configuration et l'exécution d'un de nos scripts. Il définit les paramètres nécessaires au bon fonctionnement du programme, notamment en fournissant des valeurs clés pour des variables importantes. Voici une explication détaillée des éléments présents dans ce fichier :

- **cle_api :** Cette clé API est utilisée pour authentifier les requêtes envoyées à une API externe. Elle permet d'accéder aux fonctionnalités de l'API et de garantir que seules les requêtes autorisées sont acceptées. On peut récupérer des clés sur le site web.

- **bien_cible :** Cette variable spécifie le bien ciblé par le script. Il peut s'agir d'un identifiant unique. Cette identifiant se trouve sous la forme d'un id tel que 11 ou 662.

- **chemin_logs :** Ce chemin indique l'emplacement où les fichiers de log doivent être enregistrés. Les fichiers de log contiennent des informations détaillées sur l'exécution du programme, y compris les erreurs rencontrées, les requêtes effectuées et d'autres événements importants.

- **chemin_donnees_api :** Ce chemin spécifie l'emplacement où les données récupérées à partir de l'API doivent être stockées. Ces données peuvent être utilisées ultérieurement pour diverses analyses, traitements ou affichages.

En résumé, ce fichier JSON configure les paramètres nécessaires au bon fonctionnement du script, notamment en fournissant des clés d'API pour l'authentification, des chemins pour l'enregistrement des fichiers de log et des données API, ainsi que des identifiants pour le bien ciblé par le script.

Exemple de fichier de configuration JSON

```json
{
    "cle_api": "vf4qFvKjfpoxcpZ4",
    "bien_cible": "2",
    "chemin_logs": "./",
    "chemin_donnees_api": "./"
  }
```


## Préparation et éxecution des scripts

Les 2 scripts qui vont être utilisé sont necessaires pur faire fonctionner l'apirator. L'ordre d'éxécution ainsi que le contenue de chaque requête est important

### Script du Synchronizator

Tout d'abord il faudrat compiler un serveur afin qu'il puisse traiter les requête faites par l'apirator et nous renvoyer toutes les fichiers voulus

```bash
gcc serveur.c -o serveur -lpq
```

Ensuite il faut le lancer

```bash
./serveur <port> <adresse_ip>
```

Cette commande permet de lancer le serveur

- **port :** Correspond au port sur lequel va tourner le serveur (par exemple entre 8000 et 8080)

- **adresse_ip :**^Correspond à l'adresse IP que le serveur va utiliser (par exemple 127.0.0.1)

### Script de l'apirator

Le script de l'apirator va servir à se connecter au serveur puis à donner les différentes informations utile au serveur. On va donc d'abord compiler le script

```bash
gcc apirator.c -o apirator
```

Et on va exécuter le programme

```bash
./apirator -p <port> -i <adresse_ip> -c <fichier.json> -d <YYYY/MM/DD> -n <NX>
```

Cette commande va alors lancer l'apirator qui va se connecter au serveur et vous restituer le résultat de votre demande

- **port :** Correspond au port sur lequel va tourner l'apirator et qui doit être le même que celui du serveur

- **adresse_ip :** Correspond à l'adresse IP que l'apirator va utiliser et qui doit être le même que celui du serveur

- **fichier.json :** Correspond au fichier .json configurer comme au début (par exemple coucou.json)

- **YYYY/MM/DD :** Correspond à la date à partir de laquel vous souhaiter observer un logement (par exemple 2024/04/05)

- **NX :** Correspond au nombre de jour/mois/an que vous souhaiter observé en partant de la date donnée plutôt (par exemple 14D)

Le serveur doit d'abord être lancer avant de pouvoir faire une demande avec l'apirator

### CRON

Le CRON va vous permettre de lancer une commande à intervalle régulier et de manière automatiser. Pour le configurer il faut faudrat faire cette suite de commande

```bash
crontab -e
```

Et ensuite écrire dans l'éditeur 

```txt
<temps> /docker/sae/data/html/Synchronizator/apirator -p <port> -i <adresse_ip> -c <fichier.json> -d <YYYY/MM/DD> -n <NX>
```

Içi la ligne va enregistrer la requête à effectuer et tout les combiens de temps

- **temps :** Correspond à l'intervalle de temps entre chaque lancement de requête (par exemple */3 * * * *)

Un exemple pour le CRON serait

```txt
*/3 * * * * /docker/sae/data/html/Synchronizator/apirator -p 8080 -i 127.0.0.1 -c coucou.json -d 2024/04/04 -n 14D
```