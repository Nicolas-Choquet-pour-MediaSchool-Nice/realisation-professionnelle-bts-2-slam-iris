# realisation-professionnelle-bts-2-slam-iris
Projet de réalisation professionnelle pour les BTS 2 SLAM IRIS réalisé par mes soins

## Installation du projet

```bash
git clone https://github.com/Nicolas-Choquet-pour-MediaSchool-Nice/realisation-professionnelle-bts-2-slam-iris.git
cd realisation-professionnelle-bts-2-slam-iris
# remplire le fichier .env au moins avec l'url de connexion à la bdd
composer install
php bin/console doctrine:migration:migrate
php bin/console doctrine:fixtures:load
symfony server:start
```


## Déploiement production avec Docker Compose

1. Copier le fichier d'environnement dédié :
    ```bash
    cp .env.prod.docker .env.prod
    ```

2. Modifier les valeurs sensibles dans `.env.prod` (`APP_SECRET`, `POSTGRES_PASSWORD`, etc.).

3. Lancer la stack en production :
    ```bash
    docker compose --env-file .env.prod -f docker-compose.prod.yml up -d --build
    ```

4. Vérifier les logs :
    ```bash
    docker compose --env-file .env.prod -f docker-compose.prod.yml logs -f
    ```

5. Arrêter la stack :
    ```bash
    docker compose --env-file .env.prod -f docker-compose.prod.yml down
    ```

### Envoi de l'image sur GitHub Packages (GHCR)

Pour envoyer l'image sur le registre GitHub :

1. **Connexion au registre** (nécessite un Personal Access Token avec les droits `write:packages`) :
    ```bash
    echo $CR_PAT | docker login ghcr.io -u VOTRE_NOM_UTILISATEUR --password-stdin
    ```

2. **Construction et Tagging** :
    ```bash
    docker build -t ghcr.io/nicolas-choquet-pour-mediaschool-nice/realisation-professionnelle-bts-2-slam-iris/app:latest .
    ```

3. **Push sur le registre** :
    ```bash
    docker push ghcr.io/nicolas-choquet-pour-mediaschool-nice/realisation-professionnelle-bts-2-slam-iris/app:latest
    ```

> Remplacez `VOTRE_NOM_UTILISATEUR` par votre identifiant GitHub.

> Le conteneur `app` exécute automatiquement les migrations Doctrine au démarrage (désactivable avec `RUN_MIGRATIONS=0`).

## Diagrammes

### Diagramme de Relation (ERD)

```mermaid
erDiagram
    user {
        int id PK
        string email
        string firstname
        string lastname
        string password
    }
    coordinator {
        int id PK
        int user_id FK
    }
    admin {
        int id PK
        int user_id FK
    }
    studient {
        int id PK
        int user_id FK
        int class_id FK
    }
    school_class {
        int id PK
        string name
    }
    coordinator_school_class {
        int id PK
        int coordinator_id FK
        int class_id FK
    }
    reservation {
        int id PK
        int room_id FK
        int user_id FK
        datetime reservation_start
        datetime reservation_end
        string status
    }
    room {
        int id PK
        string name
        int capacity
    }
    equipment {
        int id PK
        string name
    }
    equipment_room {
        int equipment_id PK,FK
        int room_id PK,FK
    }

    user ||--|| coordinator : "est"
    user ||--|| admin : "est"
    user ||--|| studient : "est"
    user ||--o{ reservation : "effectue"
    coordinator ||--o{ coordinator_school_class : "gère"
    school_class ||--o{ coordinator_school_class : "est gérée par"
    school_class ||--o{ studient : "contient"
    room ||--o{ reservation : "est réservée"
    room ||--o{ equipment_room : "contient"
    equipment ||--o{ equipment_room : "contient"
```

### Diagramme d'activité Etudiant

```mermaid
flowchart TD
    Coord((User)) --> B(Login)
    B --> C{Success}
    C --> | No | B
    C --> | Yes | D(home)
    D --> E(Consult rooms)
    E --> F(Choose room)
    F --> G{Booked}
    G --> | Yes | H(Unbook room)
    G --> | No | I(Book room)
    H --> E
    I --> E
```

### Diagramme d'activité Coordinateur

```mermaid
flowchart TD
    Coord((Coordinator)) --> Login[login]
    Login --> Success{success}
    Success -- No --> Login
    Success -- Yes --> Home[home]

    Home --> AdminPage[Administration page]
    AdminPage --> ConsultClasses[Consult classes]
    ConsultClasses --> ChoseClass[Chose class]
    ChoseClass --> ListStudents[List students]

    ListStudents --> ChoseStudent[Chose studient]
    ChoseStudent --> DeleteStudent[Delete studient]
    DeleteStudent --> ListStudents

    ListStudents --> CreateStudent[Create studient]
    CreateStudent --> AddToClass[Add studient to class]
    
    ListStudents --> AddToClass
    AddToClass --> ListStudents

    Home --> ConsultRooms[Consult rooms]
    ConsultRooms --> ChoseRoom[Chose room]
    ChoseRoom --> Booked{booked}
    
    Booked -- Yes --> Unbook[Unbook room]
    Booked -- No --> Book[Book room]
    
    Unbook --> ConsultRooms
    Book --> ConsultRooms
```

### Diagramme d'activité Administrateur
    
```mermaid
flowchart TD
    Admin((Admin)) --> Login[login]
    Login --> Success{success}
    Success -- No --> Login
    Success -- Yes --> Home[home]

    Home --> AdminPage[Adminitration page]
    
    AdminPage --> ConsultRoomsAdmin[Consult rooms]
    ConsultRoomsAdmin --> ChoseRoomAdmin[Chose room]
    ChoseRoomAdmin --> DeleteRoom[Delete room]
    DeleteRoom --> ConsultRoomsAdmin
    ConsultRoomsAdmin --> CreateRoom[Create room]
    CreateRoom --> ConsultRoomsAdmin

    AdminPage --> ConsultClasses[Consult classes]
    ConsultClasses --> ChoseClass[Chose class]
    ChoseClass --> ListStudents[List students]

    ListStudents --> ChoseStudent[Chose studient]
    ChoseStudent --> DeleteStudent[Delete studient]
    DeleteStudent --> ListStudents

    ListStudents --> CreateStudent[Create studient]
    CreateStudent --> AddToClass[Add studient to class]
    
    ListStudents --> AddToClass
    AddToClass --> ListStudents

    Home --> ConsultRooms[Consult rooms]
    ConsultRooms --> ChoseRoom[Chose room]
    ChoseRoom --> Booked{booked}
    
    Booked -- Yes --> Unbook[Unbook room]
    Booked -- No --> Book[Book room]
    
    Unbook --> ConsultRooms
    Book --> ConsultRooms
```
