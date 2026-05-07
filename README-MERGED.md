# DigiWork Hub - Projet Fusionné

## Description
DigiWork Hub est une plateforme intelligente pour entrepreneurs digitaux, freelances et créateurs de contenu. Ce projet est le résultat de la fusion des dossiers `digiwork-hub` et `diji2` pour créer une application complète et fonctionnelle.

## Fonctionnalités
- Gestion des missions
- Optimisation des profils avec l'IA
- Analyse des performances
- Recommandation de services
- Tableau de bord administrateur
- Système d'abonnement
- Gestion des événements
- Système de mailing

## Structure du projet

### Fichiers principaux
- `index.html` - Page d'accueil principale
- `index.php` - Point d'entrée PHP
- `admin_login.php` - Connexion administrateur
- `quick_login.php` - Connexion rapide
- `setup.php` - Script d'installation

### Dossiers importants
- `config/` - Configuration de la base de données
- `controller/` - Contrôleurs PHP
- `model/` - Modèles de données
- `view/` - Vues frontend et backend
- `assets/` - Fichiers statiques (CSS, JS, images)
- `database/` - Scripts de base de données

## Installation

### Prérequis
- XAMPP/WAMP/MAMP (serveur web avec PHP et MySQL)
- PHP 7.4+
- MySQL

### Étapes d'installation

1. **Placer le projet dans le répertoire web**
   ```
   Copier le dossier "digiwork-hub" dans htdocs (XAMPP) ou www (WAMP)
   ```

2. **Lancer le script d'installation**
   ```
   Ouvrir: http://localhost/digiwork-hub/setup.php
   ```

3. **Configuration manuelle (alternative)**
   - Importer `database.sql` dans phpMyAdmin
   - Importer `seed_admin.sql` pour les données admin
   - Importer `seed_abonnement_data.sql` pour les données d'abonnement

## Accès à l'application

### Page d'accueil
```
http://localhost/digiwork-hub/index.html
```

### Administration
```
http://localhost/digiwork-hub/admin_login.php
```

### Tableau de bord admin
```
http://localhost/digiwork-hub/view/back/dashboard.php
```

## Configuration de la base de données

Le fichier `config/config.php` contient les paramètres de connexion:
- Hôte: 127.0.0.1
- Base de données: digiwork-hub
- Utilisateur: root
- Mot de passe: (vide)

## Fonctionnalités fusionnées

### Frontend (digiwork-hub original)
- Interface utilisateur moderne avec Bootstrap 5
- Page d'accueil responsive
- Sections de présentation

### Backend (diji2)
- Système d'authentification
- Tableau de bord administrateur
- Gestion des abonnements
- Gestion des événements
- Système de mailing

## Technologies utilisées
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Base de données**: MySQL
- **Architecture**: MVC (Model-View-Controller)

## Dépannage

### Problèmes courants
1. **Erreur de connexion BDD**: Vérifier que MySQL est démarré
2. **Page blanche**: Activer l'affichage des erreurs PHP
3. **Permissions**: S'assurer que les dossiers ont les bonnes permissions

### Logs
- `php-server.log` - Logs du serveur PHP
- Logs MySQL dans XAMPP

## Équipe de développement
- Maram Mechergui
- Tasnim Khediri
- Yassine Jeddey
- Siwar Balloum
- Mariem Hammami
- Med Ali Zouaoui

## Licence
Ce projet est sous licence MIT.
