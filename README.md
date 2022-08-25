# Zenfit

Zenfit is the #1 tool for personal trainers & online coaches.

Built with 💪 using Symfony5.3 (PHP8.0) & React16.8.6 (Node 14.18.0).

## Installation

Install docker & docker-compose. Then run:

```bash
## Project
docker-compose up -d

## PHP
composer install

## Javascript
node version 14.18.0
yarn && yarn run webpack-dev

## Database
docker-compose exec app bin/console doctrine:database:create
docker-compose exec app bin/console doctrine:schema:update --force
docker-compose exec app bin/console zf:fixtures:load

## JWT
php bin/console lexik:jwt:generate-keypair --skip-if-exists

When you have created the database, go to `/sign-up` and create your trainer account
Finally, open you database client, find you user in `users` table and flag `activated = 1`
```

## HTTPS

How to run HTTPS locally.

```bash
composer global require laravel/valet
valet install

# Go to project and write (project folder should be named 'zenfit')
valet link
# Now run
valet secure zenfit
# Assuming your local Zenfit dev environment is available at http://zenfit.test:8888
# As defined in /etc/hosts: (127.0.0.1       zenfit.test)
valet proxy zenfit http://zenfit.test:8888
# Enjoy HTTPS
```

## NGROK
```bash
ngrok http http://zenfit.test:8888
```
