# Tun-arche
# Tun-arche
## Description du Projet

Tun'Arche Web est la composante du projet artistique **Tun'Arche**, développée avec Symfony 6.4.  
Elle permet la gestion des utilisateurs, artistes, œuvres, événements, formations, concours et publications à travers une API RESTful et une interface web d’administration.

Projet réalisé dans le cadre de PIDEV 3A à **Esprit School of Engineering**.

## Table des Matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contribution](#contribution)
- [Licence](#licence)

## Installation

1. **Cloner le repository**

```bash
git clone https://github.com/your-org/tunarche-backend.git
cd tunarche-backend
```

2. **Installer les dépendances**

```bash
composer install
```

3. **Configurer l’environnement**

```bash
cp .env .env.local
# Modifier .env.local avec les informations de connexion à la base de données
```

4. **Créer la base de données et exécuter les migrations**

```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

5. **Lancer le serveur local**

```bash
symfony server:start
```

## Utilisation

- Connexion avec identifiants
- Consultation des galeries d’œuvres
- Participation à des concours et événements
- Suivi et inscription à des formations
- Suivi des blog
- Suivi et participation a des événements 

### Interface d'administration

- Accessible sur : `http://localhost:8000/login`
- Gestion : utilisateurs, événements, formations, blogs, concours, œuvres

## Contribution

Merci de respecter les étapes suivantes :

1. Fork du projet
2. Créer une branche `feature/NomDeLaFeature`
3. Soumettre une *pull request* claire avec description
4. Suivre la structure de code Symfony (PSR-12, services, etc.)
### Contributeurs

Ce projet a été réalisé par l'équipe **Euphoris** dans le cadre du module PIDEV à **Esprit School of Engineering** :

- Ouji Boughanmi Sahar  : gestion de concours
- Belkhir Ameni : gestion de formation 
- Benmanaa Yasmine  : gestion blog
- Azouzi Marwen  : gestion user
- Mahdi Yassine  : gestion de gallerie
- Mediouni Moemen : gestion événement

## Licence

Ce projet est open-source sous licence MIT.  
Vous êtes libres de l'utiliser, le modifier et le distribuer avec attribution.

---
Développé par l’équipe Euphoris dans le cadre de PIDEV 3A –  
**Esprit School of Engineering**

