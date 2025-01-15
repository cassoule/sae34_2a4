# Contenu des fichiers de données générés par le script

## Contenu du fichier JSON

Le fichier JSON contient les résultats de la requête de disponibilité sous forme de tableau d'objets, où chaque objet représente une date de disponibilité d'un logement spécifique.

Chaque objet contient une clé `date_jour` associée à la date de disponibilité au format `YYYY-MM-DD` qui est donc une date qu'un client prendre pour réserver le logement.

Exemple de contenu du fichier JSON :

```json
[
    {
        "date_jour": "2024-04-04"
    },
    {
        "date_jour": "2024-04-05"
    },
    {
        "date_jour": "2024-04-06"
    },
    ...
]
```

## Contenu du fichier LOG

Le fichier journal enregistre des informations sur l'exécution de la requête, y compris les détails sur les résultats obtenus et tout problème survenu lors du processus. Chaque ligne du fichier journal peut contenir des informations telles que l'adresse IP de l'utilisateur, la clé API utilisée, le bien ciblé, le chemin vers le répertoire des données API, le nom du fichier JSON généré, ainsi que des messages indiquant le succès ou l'échec de l'enregistrement de la requête.

Exemple de contenu du fichier LOG

```ruby
[HH:MM:SS] Adresse IP: 192.168.1.100
[HH:MM:SS] Clé API: votre_cle_api
[HH:MM:SS] Bien ciblé: nom_du_bien
[HH:MM:SS] Chemin vers les données API: /chemin/vers/dossier/donnees_api
[HH:MM:SS] Nom du fichier JSON: apirator-DD_MM_YYYY_hh_mm_ss.json
[HH:MM:SS] Enregistrement réussi de la requête
```

Dans cet exemple, HH:MM:SS représente l'horodatage de l'événement dans le fichier journal.