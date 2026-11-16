
# TP PHP - Application Web « Gestion des étudiants »

Application web développée en PHP natif permettant de gérer une liste d'étudiants (authentification, ajout, modification, suppression, recherche, tri et téléchargement de photo de profil) sans base de données SQL, en utilisant un fichier texte plat (`data/etudiants.txt`).

- **Réalisé par :** MIDI OTHMAN
- **Filière / Établissement :** GI ENSA
- **Technologies :** PHP 8, HTML5, CSS3, JavaScript

---

## Fonctionnalités implémentées

- **Authentification & Sécurité :** Connexion par identifiants (`$_SESSION`), protection des pages internes, protection XSS (`htmlspecialchars`) et mémorisation du dernier utilisateur via cookie (`$_COOKIE`).
- **Gestion CRUD complète :** Ajout, modification et suppression d'étudiants enregistrés dans `data/etudiants.txt` au format `id; nom; prenom; filiere; note; photo`.
- **Gestion des photos & Avatars :** Upload sécurisé d'images (`.jpg`, `.jpeg`, `.png`, max 1 Mo) dans `uploads/` avec renommage unique (`uniqid()`), suppression automatique des anciens fichiers et images par défaut disponibles dans `avatars/`.
- **Recherche, Tri & Statistiques :** Filtrage par nom/prénom, tri dynamique (ordre d'ajout, nom, filière, note décroissante), calcul de la moyenne et de la meilleure note, plus recherche instantanée côté client en JavaScript.

---

## Structure du projet

```text
gestion_etudiants/
├── avatars/               # Images et avatars par défaut
├── data/
│   └── etudiants.txt      # Base de données fichier (séparateur ;)
├── uploads/               # Photos de profil téléchargées
├── index.php              # Page de connexion
├── liste.php              # Affichage, recherche, tri et statistiques
├── ajouter.php            # Formulaire d'ajout et de modification
├── supprimer.php          # Traitement de la suppression
├── logout.php             # Déconnexion et destruction de session
├── functions.php          # Constantes, sessions et fonctions de lecture/écriture
├── header.php             # En-tête HTML et menu de navigation
├── footer.php             # Pied de page HTML
├── style.css              # Feuille de style
└── script.js              # Confirmation de suppression et filtre instantané
```

---

## Installation et lancement

1. Copier le dossier `gestion_etudiants` dans le répertoire `htdocs` de XAMPP.
2. Démarrer Apache depuis le panneau de contrôle XAMPP.
3. Ouvrir un navigateur web.
4. Accéder à l'adresse suivante :

   `http://localhost/gestion_etudiants/`

5. La page de connexion s'affiche. Saisir les identifiants ci-dessous pour accéder à l'application.

---

## Identifiants de connexion

Deux comptes sont disponibles pour accéder à l'application :

| Rôle | Nom d'utilisateur | Mot de passe |
|---|---|---|
| Administrateur | `admin` | `1234` |
| Professeur | `prof` | `ensa2026` |

**Connexion :**
- Choisir l'un des deux comptes.
- Saisir le nom d'utilisateur et le mot de passe correspondants.
- Cliquer sur le bouton **Se connecter**.
- Après authentification, accéder à l'espace de gestion des étudiants.
