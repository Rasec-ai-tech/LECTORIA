# LECTORIA - Documentation technique et d'exécution

## Présentation du projet
LECTORIA est une application web de bibliothèque en ligne développée en PHP 8 orienté objet avec PDO/MySQL, en gardant une séparation stricte entre la couche publique et la couche métier.

### Objectifs fonctionnels du projet :
- rechercher des livres,
- consulter une fiche détail,
- gérer une liste de lecture personnelle,
- se connecter en tant que lecteur,
- accéder au back-office administrateur pour gérer les livres.

### Architecture du projet :
- public/ : point d’entrée HTTP, rendu HTML, orchestration des pages et endpoints.
- src/ : modèles, services, helpers et accès PDO.
- config/ : configuration locale de la base de données.
- sql/ : schéma SQL et jeux de données de test.

## Stack technique

- PHP 8.x
- PDO avec MySQL 8.x
- HTML5 sémantique
- CSS3 natif (variables, flexbox, grid)
- JavaScript ES6 natif, sans framework
- Session PHP sécurisée
- password_hash() / password_verify() avec ARGON2ID
- jetons CSRF côté serveur

## Principes de sécurité déjà appliqués

- Toutes les requêtes SQL passent par PDO préparé.
- Les connexions PDO utilisent PDO::ERRMODE_EXCEPTION et PDO::ATTR_EMULATE_PREPARES = false.
- Les données affichées dans le HTML sont échappées avec htmlspecialchars(..., ENT_QUOTES, 'UTF-8').
- Les mots de passe sont hachés avec password_hash() et vérifiés avec password_verify().
- Les formulaires sensibles contiennent un token CSRF.
- Les accès admin sont vérifiés côté serveur via AuthService::exigerAdministrateur().

## Arborescence du projet

```
    public/
      - index.php
      - results.php
      - details.php
      - wishlist.php
      - login.php
      - register.php
      - logout.php
      - add_to_wishlist.php
      - remove_from_wishlist.php
      - admin/
        - dashboard.php
        - add_livre.php
        - edit_livre.php
        - delete_livre.php
        - _admin_guard.php
    - src/
      - Database/
      - Models/
      - Services/
      - Helpers/
    - sql/
      - schema.sql
    - config/
      - config.example.php
      - config.php

```

## Ce qui a déjà été fait

Les éléments implementés dans le code actuel incluent :

- La structure MVC légère du projet.
- La connexion PDO centralisée.
- Les modèles Livre, Lecteur et ListeLecture.
- Le service AuthService pour la connexion, la déconnexion et la vérification des droits.
- La page d'accueil publique.
- La page catalogue avec recherche et pagination.
- La page détail d'un livre.
- La liste de lecture avec ajout et retrait.
- La page login et la page register.
- Le back-office admin avec dashboard, ajouter un livre, éditer un livre et supprimer un livre.
- Le système de CSRF.
- La validation des formulaires côté serveur.
- Le chargement AJAX côté catalogue et wishlist.

## Ce qui reste à faire / à finaliser

Le projet est fonctionnel dans son ensemble sur la base de l'architecture demandée, mais plusieurs points doivent encore être peaufinés ou validés de manière stricte avant de considérer le projet comme complètement stabilisé :

- Vérifier et stabiliser définitivement le flux d'accès au back-office admin.
- Confirmer le parcours complet avec un compte admin réel dans le navigateur.
- Finaliser la cohérence de redirection après authentification selon l'URL demandée.
- Vérifier les cas d'erreurs métier sur les stock et les suppressions.
- Sécuriser complètement les redirections et les contrôles côté serveur sur toutes les routes publiques et admin.
- Valider le comportement sur des scénarios de navigation avec session active, session expirée et rôle non autorisé.

## Base de données

Le schéma SQL est disponible dans :
- sql/schema.sql

Le schéma contient :
- table Livres
- table Lecteurs
- table Liste_lecture

Des données de test sont déjà injectées dans le script SQL :
- livre de test en base,
- 2 lecteurs de test,
- 1 compte administrateur et 1 compte lecteur.

Comptes de test connus dans le schéma fourni :
- email : claire.dubois@lectoria.test
  role : lecteur
  mot de passe : Password123!

- email : alex.martin@lectoria.test
  role : administrateur
  mot de passe : Password123!

Important : les mots de passe présents dans le schéma sont déjà hashés en ARGON2ID et ne doivent pas être recalculés côté SQL.

## Configuration locale

Avant de lancer le projet, il faut créer un fichier de configuration locale :
- copier config/config.example.php vers config/config.php

Le fichier config.php doit contenir au minimum :
- DB_HOST
- DB_NAME
- DB_USER
- DB_PASS
- APP_ENV

Exemple de configuration locale prévue :
- DB_HOST = localhost
- DB_NAME = lectoria
- DB_USER = root
- DB_PASS = ''
- APP_ENV = development

## Exécution du projet dans Laragon

Pré-requis :
- Laragon installé,
- Apache démarré,
- MySQL démarré,
- le projet placé dans le dossier racine Laragon : C:\laragon\www\LECTORIA

Étapes recommandées :

### 1. Importer le schéma SQL
   - ouvrir phpMyAdmin ou un client SQL,
   - créer la base lectoria si nécessaire,
   - importer le fichier sql/schema.sql.

### 2. Créer la configuration locale
   - copier config/config.example.php en config/config.php,
   - adapter les paramètres de connexion MySQL.

### 3. Vérifier le point d'entrée public
   - La racine du projet doit être servie via le dossier public/.
   - Sous Laragon, l'URL attendue est :
     http://lectoria.test/public/

### 4. Démarrer le site
   - lancer Apache et MySQL dans Laragon,
   - ouvrir l'URL http://lectoria.test/public/.

### 5. Vérifier le point d'entrée de l'accueil
   - l'adresse d'accueil doit être la page index.php dans public/.

## Exécution alternative via localhost

Si l'hôte virtuel Laragon n'est pas activé ou n'est pas disponible, on peut aussi utiliser la configuration de serveur locale de Laragon sur :
- http://127.0.0.1:8080/

Si le projet est bien configuré pour servir le dossier public/ comme racine du site, alors l'URL de travail la plus directe est :
- http://127.0.0.1:8080/index.php

## Parcours utilisateur recommandé

### 1. Ouvrir la page d'accueil.
### 2. Faire une recherche dans le catalogue.
### 3. Ouvrir une fiche détail.
### 4. Ajouter un livre à la liste de lecture.
### 5. Consulter la liste de lecture.
### 6. Se connecter avec un compte lecteur.
### 7. Accéder au back-office avec un compte administrateur.

## Points à surveiller lors d'un test manuel

- l'URL redirect après login,
- la présence du token CSRF sur les formulaires,
- la redirection vers login si l'accès admin est demandé sans session,
- la bonne gestion du compte lecteur vs compte administrateur,
- le comportement des stock lors des ajouts et retraits à la liste de lecture,
- la cohérence des messages flash et des redirections.

## Résumé rapide de l'état actuel

Statut général :
- la base du projet est en place,
- la couche PHP est structurée,
- les pages publiques principales sont opérationnelles,
- l'authentification et le rôle utilisateur existent,
- le back-office est présent mais sa validation finale de flux demande encore une stabilisation complète.

## Remarques finales

Ce projet doit rester conforme à la séparation suivante :
- public/ : uniquement la logique d'orchestration, le rendu et les endpoints HTTP.
- src/ : toute la logique métier, l'accès aux données et les services.

En production ou en environnement partagé, il faudra aussi vérifier les paramètres de sécurité des cookies PHP, le contexte HTTPS, les règles de redirection et la protection des entrées côté serveur.
