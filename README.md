# <u>Greengoodies</u>

## Informations générales

### *<u>Réalisation du site</u>*
GreenGoodies est une boutique physique implantée à Lyon. L’objectif est de
développer le site de notre boutique afin de pouvoir recevoir des commandes de toute
la France.

Le site devra être réalisé sous Symfony, et comprendre :

● une partie accessible à tous : affichage des articles vendus, possibilité de
s’inscrire ou de se connecter.

● une partie accessible aux utilisateurs authentifiés : faire une commande, suivre
les commandes passées.
● une partie API accessible aux utilisateurs authentifiés l’ayant activée : récupérer
la liste des produits de la boutique.

### *<u>Le site réalisé devra</u>* :

● comporter toutes les fonctionnalités demandées dans les spécifications.

● s’appuyer sur les maquettes du designer.

● vérifier l’ensemble des données saisies dans les formulaires avec Symfony (une
vérification en JS peut être faite en plus, mais ce n’est pas obligatoire).

## 📘 Présentation du site Green Goodies

### Document d’introduction technique

### 1. Introduction générale

Green Goodies est une plateforme web développée sous Symfony 7.3, permettant la mise en avant et la vente de produits responsables.
Le site s’appuie sur une architecture simple, performante et sécurisée, intégrant :

Une base de données optimisée

Une mise en page moderne construite en “briques visuelles”

Une gestion d’authentification légère

Un WebService exposant les produits avec gestion de token

Une configuration security.yaml claire assurant la sécurité globale de l’application

## 🗄️ 2. Base de données

La base de données repose sur MySQL et suit une structure simple, centrée autour des entités principales du site :

User

Produit

Panier

LignePanier

### ⚠️ Point important : la table panier

La table panier joue un rôle essentiel dans le fonctionnement du site, car elle représente à la fois les paniers en cours et les commandes validées.

Cela est rendu possible grâce à la colonne :

### mode_panier

Valeur	Signification
0	Panier (brouillon, non finalisé)
1	Commande (validée et enregistrée)

#### Pourquoi ce choix est important ?

Une seule table → structure plus légère

Transition panier → commande : un simple changement de flag

Simplification des relations avec les produits et utilisateurs

## 🎨 3. CSS et mise en page (Design “Briques”)

Le site Green Goodies utilise une mise en page modulaire basée sur des “briques visuelles”, inspirées d’approches modernes (Figma, blocs UI indépendants).

### Principes clés :

#### Architecture CSS

Organisation en fichiers indépendants :
* _variables.css : variables globales
* _briques.css : styles de sections
* _cartes_produit.css : stylisation des cartes produits
* _layout.css : structure générale du site

Utilisation extensive des custom properties CSS pour les couleurs, ombres, polices

Responsive design contrôlé par des breakpoints alignés sur la maquette Figma

#### Briques visuelles

Chaque section de la page est pensée comme une brique réutilisable :

* Brique “Hero”
* Brique “Produits en avant”
* Brique “Convictions / valeurs”
* Brique “Panier résumé”
* Brique “Footer vert clair”

Ces briques sont codées sous forme de composants Twig combinant HTML + CSS modulaires.

### 🔐 4. Gestion du login simple

Le site repose sur un système d’authentification classique basé sur :

Un formulaire de login

Un user provider standard

Une vérification via la base de données

#### Fonctionnement :

L’utilisateur accède au formulaire de connexion

Symfony compare les identifiants au User en base

Après succès, l’utilisateur est redirigé sur la page d’accueil

→ Pas de gestion JWT dans le site principal, uniquement un login “session” via cookies sécurisé.

### 🔌 5. Gestion du WebService (Produits + Token)

Le projet expose également un WebService REST permettant :

✔ Récupération de la liste des produits

#### Route typique :

GET /api/products
Authorization: Bearer <token>

✔ Utilisation d’un Token

Un utilisateur peut activer ou désactiver son accès API via un bouton.
Le token est ensuite utilisé pour autoriser les appels REST.

#### Mécanisme :

L’utilisateur active l’accès → un token unique est attribué

Les requêtes API doivent inclure ce token dans l’en-tête Authorization

Le contrôleur API vérifie le token avant d’autoriser l’accès

### Points clés :

Token stocké dans la table utilisateur

Séparation site web / API

Possibilité pour l’utilisateur de couper son accès API (sécurité)

### 🛡️ 6. Fichier security.yaml : fonctionnement de l’authentification

Le fichier security.yaml de Symfony 7.3 joue un rôle central dans la gestion sécurité de Green Goodies.

#### 🔑 Éléments importants :

✔ 6.1 Firewall principal

Il définit :

les pages publiques

les routes protégées

les contrôles d’accès

la session utilisateur

Exemple simplifié :

```security:
firewalls:
main:
lazy: true
provider: app_user_provider
form_login:
login_path: app.login
check_path: app.login
logout:
path: app.logout

```
#### 6.2 Gestion du User Provider

Il indique comment récupérer un user :

````providers:
app_user_provider:
entity:
class: App\Entity\User
property: email

````
#### 6.3 Access Control

Définit les restrictions :

```access_control:

- { path: ^/admin, roles: ROLE_ADMIN }
- { path: ^/api, roles: ROLE_USER }

```
#### 6.4 Gestion token API

Le token n’est pas géré via JWT mais via un custom authenticator qui vérifie le token dans l’en-tête HTTP.

### Conclusion

Le site Green Goodies est construit autour d’une architecture simple mais robuste :

Une base de données optimisée, notamment grâce au champ mode_panier

Une mise en page modulaire en briques réutilisables

Un système de login basé sur les sessions Symfony

Un WebService externe avec gestion de Token

Une configuration security.yaml claire permettant un contrôle total de l’accès à l’application

Ce document peut servir de base pour une présentation technique, un rapport scolaire ou une documentation interne.
