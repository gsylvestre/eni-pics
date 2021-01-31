### Instructions d'installation
Dans le terminal : 

```
cd /wamp64/www/
git clone https://github.com/gsylvestre/eni-pics.git  
cd eni-pics/  
composer install
```

Dans PHPStorm : 
Ouvrez le projet et configurez le plugin Symfony.  
Créer le fichier `.env.local` en s'inspirant du `.env`  
Y configurer la connexion à la base de donnée, puis dans le terminal : 

```
php bin/console doctrine:database:create
php bin/console app:import-data
```