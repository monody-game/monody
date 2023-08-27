# Monody
Monody is an online werewolf game.  
Every roles and mechanics are explained [here (in french)](https://docs.monody.fr)

[![Deployment](https://github.com/monody-game/monody/actions/workflows/cd.yml/badge.svg?event=release)](https://github.com/monody-game/monody/actions/workflows/cd.yml)

**If you read this after January 2024, monody.fr is not up anymore. I don't want to pay a domain name just for a side project that I made to learn, even though I have access to free hosting through Github Student Pack.**

This repo contains code for the API (made with Laravel), the WS server (made with socket.io in typescript, in the websockets/ folder) and the front (made with Vue).  

## If you want to run the project locally :
Install the deps : 
```shell
composer install && yarn install
```
Fill the .env :
```shell
cp .env.example .env
php artisan key:generate --ansi
```
Make a self-signed certificate ([More infos here](https://gist.github.com/cecilemuller/9492b848eb8fe46d462abeb26656c4f8))  

And then just run ``docker-compose up -d --build`` to start local servers.  
You may want to run migrations and fill up the database : 
```shell
docker exec [[monody or project directory name]-php-1 or container id] make seed 
```

Coded and designed with ♥️ by [moon250](https://github.com/moon250)  
Backgrounds and role's icons drawn with ♥️ [Bawa](https://instagram.com/bawadraw)  
Musics and sound effects made with ♥️ by [Julien Pardo](https://instagram.com/_julienprd_)
