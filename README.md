# DentaFlow

Application web PHP/MySQL de démonstration pour la gestion d'un cabinet dentaire.

## Fonctionnalités

- Tableau de bord avec statistiques, rendez-vous du jour et patients récents
- Recherche et filtre des patients
- Création et modification des patients
- Profil patient
- Carte dentaire FDI interactive (32 dents)
- États dentaires, traitements, observations et date d'enregistrement
- Notes cliniques avec ajout, modification et suppression
- Pièces jointes JPG/JPEG/PNG/PDF/DOC/DOCX avec ouverture, téléchargement et suppression
- Gestion des rendez-vous avec création, changement de statut et suppression
- Rapport PDF téléchargeable sans dépendance externe de police
- Installation/reset de la base via `install.php`
- Interface responsive avec sidebar mobile

## Installation locale

1. Placez le dossier dans votre serveur PHP local.
2. Vérifiez `includes/config.php` : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
3. Ouvrez `http://localhost/chemin-vers-dentist_demo/install.php`.
4. Cliquez sur **Installer / réinitialiser la démo**.
5. Ouvrez `index.php`.

PHP 8.1+ et MySQL/MariaDB sont recommandés. L'extension PDO MySQL et Fileinfo sont nécessaires.

## Sécurité / production

Le projet est une démo sans authentification utilisateur. Ajoutez une authentification, une autorisation par rôle, une protection CSRF, une journalisation et une politique de sauvegarde avant toute utilisation avec de vraies données patients.
