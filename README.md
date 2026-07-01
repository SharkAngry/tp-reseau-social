# Réseau Social Web — Projet Final PHP & AJAX

Application de réseau social inspirée de Facebook, développée en PHP natif (API REST) et JavaScript (Single Page Application avec routage par hash, sans rechargement de page), avec une base de données MySQL.

## Membres du groupe — Groupe [Numéro de groupe]

| Nom | Lot | Responsabilité |
|---|---|---|
| MOUMOUNI Fadeel | Lot 1 | Architecture globale, authentification, profil utilisateur, intégration générale |
| FADOU Léon | Lot 2 | Fil d'articles, likes/dislikes, commentaires |
| HOUNSINOU Doris | Lot 3 | Gestion des amis (invitations, recherche, liste d'amis) |
| KINTOSSOU Duc-Martin | Lot 4 | Chat en temps réel, back-office (administration et modération) |

## Mode de fonctionnement

- **Frontend** : HTML/CSS/JavaScript natif. SPA avec un routeur par hash (`#accueil`, `#profil`, etc.) qui charge les vues en AJAX dans `index.html`, sans aucun rechargement après le chargement initial.
- **Backend** : PHP natif, organisé en API REST sous `/api`, authentification par token (Bearer) stocké en `sessionStorage`.
- **Base de données** : MySQL (script complet dans `database.sql`).
- **Emails** : envoi via PHPMailer (confirmation d'inscription, réinitialisation de mot de passe) au format HTML.

## Installation

1. Cloner le dépôt dans `htdocs` (XAMPP) ou `www` (Laragon).
2. Importer `database.sql` dans phpMyAdmin.
3. Copier `api/config/config.example.php` → `api/config/db.php`, renseigner ses identifiants MySQL locaux.
4. Copier `api/config/mail-config.example.php` → `api/config/mail-config.php`, renseigner les identifiants SMTP (Mailtrap en développement).
5. `composer install` à la racine du projet.
6. Ouvrir `http://localhost/<nom-du-dossier>/index.html`.

## Identifiants de test

**Client**
- Email : `test@test.com`
- Mot de passe : `nouveaumotdepasse123`

**Administration**
- Email : ``
- Mot de passe : ``

## Fonctionnalités principales

- Inscription / connexion / mot de passe oublié (emails HTML)
- Fil d'articles avec likes/dislikes et commentaires sans rechargement
- Gestion des amis (recherche, invitations, acceptation/refus)
- Modification du profil (informations, photo, mot de passe)
- Chat en temps réel entre amis
- Back-office avec rôles administrateur et modérateur