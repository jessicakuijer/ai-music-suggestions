
# ai-Music-suggestions-ChatGPT

Symfony 6.2 webApp utilisant l'API d'[OpenAi ChatGPT-3.5-turbo](https://openai.com/product/gpt-4) et [HTMX](https://htmx.org/) pour générer une liste d'autres artistes similaires à partir d'un artiste donné.

## API Reference

#### Utilisation de l'API Tectalic OpenAI REST API Client

  https://github.com/tectalichq/public-openai-client-php

## Demo

DEMO : https://ai-music.herokuapp.com/
Accès protégé par mot de passe. Si vous voulez tester, faites-moi signe ICI: https://linkedin.com/in/jessicakuijer ou par email: [@jessicakuijer](mailto:jessicakuijer@me.com)
  
Description: Générateur d'artistes similaires à partir d'un artiste donné.

## Installation
Pré-requis:  
- PHP v.8 ou supérieur.
- NodeJS v.14 ou supérieur (--lts)

Installer les dépendances du projet à l'aide de composer.  
```
composer install

```  
Démarrage du projet avec le serveur symfony.  
```
symfony serve -d (ou symfony server:stop ou start, ici on lance le serveur en arrière-plan).
```  
ou  
```
php -S localhost -t public
```  
Compiler les assets et utilisation de TailwindCSS via Webpack Encore:  
```
npm run watch (ou run build pour déployer en production)
```  

Créer le fichier .env.local avec vos paramètres pour la variable d'environnement ci-dessous.  
```
cp .env .env.local

```  
## Compiler les assets

```
npm run watch (ou run build pour déployer en production)
```
Le projet utilise Webpack Encore pour compiler les assets.

## Environment Variables

Pour utiliser l'application, vous avez le choix:  
1- Ajouter la variable d'environnement dans un fichier .env.local (en copiant .env) que vous trouverez dans vos paramètres de compte d'Open-AI.

`OPENAI_API_KEY="sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"`

ainsi que dans services.yaml
```
parameters:
    OPENAI_API_KEY: '%env(OPENAI_API_KEY)%'
```

## Support

Si questions, email jessicakuijer@me.com .

