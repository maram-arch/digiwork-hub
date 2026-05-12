# DigiWork Hub

Plateforme intelligente d'accompagnement des entrepreneurs digitaux, freelances et créateurs de contenu. Développée en PHP MVC pur (sans framework), elle centralise la gestion de projets, d'événements, d'offres d'emploi, d'abonnements et d'un forum communautaire.

---

## Table des matières

- [Aperçu](#aperçu)
- [Fonctionnalités](#fonctionnalités)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Structure du projet](#structure-du-projet)
- [Rôles utilisateurs](#rôles-utilisateurs)
- [Technologies](#technologies)
- [Membres du groupe](#membres-du-groupe)

---

## Aperçu

DigiWork Hub est une application web full-stack PHP qui propose :

- Un **frontoffice public** (vitrine + pages dynamiques) accessible via `index.php`
- Un **backoffice** (dashboard multi-rôles) accessible via `view/backoffice/index.php`
- Une **authentification sécurisée** avec vérification OTP par SMS (Twilio)
- Un **système de rôles** : admin, candidat, entreprise, sponsor

---

## Fonctionnalités

### Authentification & Sécurité
- Inscription avec vérification OTP par SMS (Twilio)
- Connexion avec bcrypt (politique de mot de passe forte : 10+ caractères, maj/min/chiffre/symbole)
- Réinitialisation de mot de passe par SMS OTP
- Indicateur de force du mot de passe en temps réel
- Gestion des sessions (heartbeat, déconnexion automatique à la fermeture)

### Frontoffice (public)
| Page | URL |
|------|-----|
| Accueil | `index.php` |
| Événements | `index.php?page=events` |
| Inscription événement | `index.php?page=inscription&id_event=X` |
| Packs | `index.php?page=packs` |
| Projets | `index.php?page=projets` |
| Explorer projets | `index.php?page=explore` |
| Offres d'emploi | `index.php?page=offres` |
| Forum | `index.php?page=forum` |
| Abonnements | `index.php?page=abonnement` |

### Backoffice (dashboard)
| Page | URL |
|------|-----|
| Dashboard | `view/backoffice/index.php` |
| Gestion utilisateurs | `?page=users` |
| Gestion événements | `?page=events` |
| Inscriptions | `?page=inscriptions` |
| Packs | `?page=packs` |
| Abonnements | `?page=abonnements` |
| Projets | `?page=projects` |
| Offres d'emploi | `?page=offres` |
| Forum (modération) | `?page=forum` |
| Mailing | `?page=mailing` |

### Modules métier
- **Projets** : CRUD complet, partage sur réseaux sociaux (WhatsApp, Facebook, X, Email)
- **Événements** : liste, filtres, carte Google Maps, formulaire de contact
- **Packs & Abonnements** : souscription en ligne, gestion admin
- **Offres d'emploi** : CRUD (admin/entreprise), candidature (candidat), gestion des statuts
- **Forum** : publications avec catégories, commentaires threaded, likes, favoris, filtre/tri
- **Mailing** : envoi groupé d'emails aux utilisateurs/abonnés

---

## Architecture

```
MVC pur PHP — sans framework
├── index.php              ← Routeur frontoffice (entry point)
├── config/
│   ├── config.php         ← Connexion PDO + migrations automatiques
│   └── sms.php            ← Credentials Twilio
├── controller/            ← Contrôleurs (logique métier)
├── model/                 ← Modèles (accès base de données)
├── view/
│   ├── frontoffice/
│   │   └── modules/       ← Pages frontoffice (output buffering)
│   └── backoffice/
│       ├── index.php      ← Routeur backoffice
│       ├── layouts/       ← Sidebar, layouts
│       └── modules/       ← Pages backoffice
├── assets/                ← CSS, JS, images (frontoffice)
└── database/
    └── digiwork-hub.sql   ← Schéma complet + seeds
```

### Routing

Le frontoffice utilise un **routeur par output buffering** : `index.php` capture le rendu de chaque module via `ob_start()`, extrait le contenu après la navbar du module, et l'injecte dans le layout principal (navbar hamburger + modals auth toujours présents).

Les AJAX calls (forum, offres, auth) sont tous routés via `index.php?action=...`.

---

## Prérequis

- **PHP** 8.0+ (testé sur 8.2)
- **MySQL** 5.7+ / MariaDB 10.4+
- **Apache** avec `mod_rewrite` (XAMPP recommandé)
- Compte **Twilio** (pour les SMS OTP)

---

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/maram-arch/digiwork-hub.git
cd digiwork-hub
```

### 2. Placer dans le répertoire web

```
C:\xampp\htdocs\projectttttttt\
```

### 3. Créer la base de données

```sql
CREATE DATABASE `digiwork-hub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Puis importer le schéma :

```bash
mysql -u root digiwork-hub < database/digiwork-hub.sql
```

### 4. Configurer la connexion DB

Éditer `config/config.php` si nécessaire (les valeurs par défaut correspondent à XAMPP) :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'digiwork-hub');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Configurer Twilio (SMS OTP)

Éditer `config/sms.php` avec vos credentials Twilio :

```php
define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('TWILIO_AUTH_TOKEN',  'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('TWILIO_FROM_NUMBER', '+1XXXXXXXXXX');
```

> **Note** : En compte Twilio Trial, les SMS ne peuvent être envoyés qu'aux numéros vérifiés dans votre console Twilio.

### 6. Accéder à l'application

| Interface | URL |
|-----------|-----|
| Frontoffice | `http://localhost/projectttttttt/` |
| Backoffice | `http://localhost/projectttttttt/view/backoffice/index.php` |

### Compte admin par défaut

| Email | Mot de passe |
|-------|-------------|
| `admin@gmail.com` | `Admin@1234` |

---

## Configuration

### Migrations automatiques

`config/config.php` exécute des migrations automatiques à chaque démarrage :
- Ajout des colonnes manquantes (`is_online`, `last_activity`, `is_verified`, etc.)
- Création des tables auxiliaires (`otp_verification`, `publication_likes`, `commentaire_likes`, `favoris`, `candidature`, etc.)
- Seeds des données de démonstration (packs, offres, entreprises)

### Politique de mot de passe

- Minimum 10 caractères
- Au moins 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial
- Aucun espace

---

## Structure du projet

```
controller/
├── UserController.php        ← Auth, CRUD utilisateurs, OTP, sessions
├── EventController.php       ← Gestion événements
├── InscriptionController.php ← Inscriptions aux événements
├── PackController.php        ← CRUD packs
├── AbonnementController.php  ← Abonnements utilisateurs
├── PackEventController.php   ← Relations pack-événement
├── projectController.php     ← CRUD projets
├── OfferController.php       ← CRUD offres + candidatures
├── ForumController.php       ← Publications + commentaires + likes
├── MailingController.php     ← Envoi emails groupés
└── PublicationController.php ← (legacy)

model/
├── UserModel.php             ← Requêtes utilisateurs
├── OtpModel.php              ← Gestion codes OTP
├── SmsService.php            ← Envoi SMS via Twilio
├── publication.php           ← Modèle forum (table `forums`)
├── commentaire.php           ← Modèle commentaires
├── Pack.php                  ← Modèle packs
├── Abonnement.php            ← Modèle abonnements
├── Event.php                 ← Modèle événements
├── Inscription.php           ← Modèle inscriptions
└── project.php               ← Modèle projets

view/frontoffice/modules/
├── event.php                 ← Page événements
├── inscription.php           ← Formulaire inscription événement
├── cancelInscription.php     ← Annulation inscription
├── packs.php                 ← Page packs
├── abonnement.php            ← Page abonnements
├── projets.php               ← Page projets (aperçu)
├── exploreProjects.php       ← Explorer tous les projets
├── forum.php                 ← Forum communautaire
└── offres.php                ← Offres d'emploi
```

---

## Rôles utilisateurs

| Rôle | Accès frontoffice | Accès backoffice |
|------|-------------------|------------------|
| **admin** | Tout | Dashboard complet (tous les modules) |
| **condidat** | Tout + postuler aux offres | Mes inscriptions, mes projets, mon abonnement |
| **entreprise** | Tout + créer des offres | Mes projets, mon abonnement |
| **sponsor** | Tout + créer des offres | Mes projets, mon abonnement |

---

## Technologies

| Couche | Technologie |
|--------|-------------|
| Backend | PHP 8.2 (MVC pur) |
| Base de données | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript (ES6+) |
| CSS Framework | Bootstrap 5 |
| SMS | Twilio REST API |
| PDF | FPDF (lib/fpdf) |
| Serveur local | XAMPP (Apache + PHP + MySQL) |

---

## Membres du groupe

| Nom | Rôle |
|-----|------|
| Maram Mechergui | Chef de projet, intégration |
| Tasnim Khediri | Backend, modèles |
| Yassine Jeddey | Frontend, projets |
| Siwar Balloum | Auth, OTP, sécurité |
| Mariem Hammami | Événements, inscriptions |
| Med Ali Zouaoui | Packs, abonnements |

---

## Licence

Ce projet est sous licence **MIT**.
