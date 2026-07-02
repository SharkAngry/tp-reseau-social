# Réseau Social Web — Projet Final PHP & AJAX

Application de réseau social inspirée de Facebook, développée en PHP natif (API REST) et JavaScript (Single Page Application, routage par hash, sans rechargement de page), avec base de données MySQL.

## Membres du groupe

| Nom | Lot | Responsabilité |
|---|---|---|
| MOUMOUNI Fadeel | Lot 1 | Architecture, authentification, profil |
| FADOU Léon | Lot 2 | Fil d'articles, likes/dislikes, commentaires |
| HOUNSINOU Doris | Lot 3 | Gestion des amis |
| KINTOSSOU Duc-Martin | Lot 4 | Chat, back-office |

## Mode de fonctionnement

- **SPA** : une seule page `index.html`, routage par hash (`#accueil`, `#profil`...), vues chargées en AJAX sans rechargement.
- **API REST** en PHP natif sous `/api`, authentification par token Bearer stocké en `sessionStorage`.
- **Base de données** MySQL (script complet dans `database.sql`).
- **Emails HTML** via PHPMailer (inscription, réinitialisation de mot de passe).

## Installation

1. Cloner le dépôt dans `htdocs` (XAMPP) ou `www` (Laragon).
2. Importer `database.sql` dans phpMyAdmin.
3. Copier `api/config/config.example.php` → `api/config/db.php` et renseigner les identifiants MySQL locaux.
4. Copier `api/config/mail-config.example.php` → `api/config/mail-config.php` et renseigner les identifiants SMTP.
5. Lancer `composer install` à la racine.
6. Ouvrir `http://localhost/<nom-du-dossier>/index.html`.

## Identifiants de test

**Client**
- Email : `test@test.com`
- Mot de passe : `nouveaumotdepasse123`

**Administration**
- URL : `http://localhost/<nom-du-dossier>/vues/back-office/login.php`
- Email : `admin@test.com`
- Mot de passe : `password`