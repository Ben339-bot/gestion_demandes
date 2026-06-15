# Gestion des Demandes Étudiantes — ESI Bobo-Dioulasso

Application web PHP/MySQL de gestion des demandes administratives étudiantes.

## Accès en ligne

Application déployée sur : **http://gestiondemandes.alwaysdata.net/login.php**

### Compte ADMIN (démonstration)
```
Email        : admin@email.com
Mot de passe : admin123
```

### Compte ÉTUDIANT
Créez votre propre compte via le lien **"S'inscrire"** sur la page de connexion,
ou utilisez un compte étudiant déjà créé par l'équipe.

## Structure du projet

```
project/
├── connexion.php         ← TRAORE Mohamed (partagé par tous)
├── login.php             ← TRAORE Mohamed
├── inscription.php       ← TRAORE Mohamed
├── logo_esi.png
├── etudiant/
│   ├── accueil.php       ← Sankara Adjara
│   ├── nouvelle_demande.php ← Soma Rahim
│   └── suivi.php         ← Soma Rahim
├── admin/
│   ├── dashboard.php     ← Bazongo Anicet
│   └── traitement.php    ← Bazongo Anicet
├── css/
│   └── style.css
├── js/
│   └── script.js
└── uploads/              ← Pièces jointes des demandes
```

## Technologies

- PHP 8.x (mysqli)
- MySQL / MariaDB
- HTML5 / CSS3
- JavaScript Vanilla
- Hébergement : alwaysdata

## Base de données

Nom : `gestiondemandes_db`

Tables : `etudiants`, `admins`, `demandes`

Statuts possibles d'une demande : `en attente`, `en cours`, `acceptee`, `refusee`

## Sessions communes

| Variable | Valeur |
|---|---|
| `$_SESSION['user_id']` | ID utilisateur |
| `$_SESSION['user_role']` | `etudiant` ou `admin` |
| `$_SESSION['user_nom']` | Nom de l'utilisateur |
| `$conn` | Connexion MySQL (dans connexion.php) |

## Installation locale (optionnel)

1. Copier le dossier `project/` dans `C:\xampp\htdocs\` (Windows) ou `/var/www/html/` (Linux)
2. Démarrer Apache et MySQL
3. Importer la base de données via phpMyAdmin
4. Accéder à `http://localhost/project/login.php`

## Équipe

- TRAORE Mohamed — Connexion + Inscription + Base de données + Hébergement
- Soma Rahim — Nouvelle demande + Suivi étudiant
- Sankara Adjara — Accueil étudiant
- Bazongo Anicet — Dashboard admin + Traitement
