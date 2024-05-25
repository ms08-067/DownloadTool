#!/bin/bash
php artisan down --render="errors::503maintenance"

git fetch origin master
git reset --hard origin/master
echo "prod" > .env
composer install --no-interaction
php artisan config:clear
php artisan config:cache
php artisan migrate

sed -i "s~define('ROCKET_CHAT_INSTANCE', 'rocketchat.lc:3000/');.*~define('ROCKET_CHAT_INSTANCE', 'https://chat.br24.vn/');~" ./app/helpers.php

sed -i 's/AWS_ACCESS_KEY_ID=.*/AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID/' .prod.env
sed -i 's/AWS_SECRET_ACCESS_KEY=.*/AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY/' .prod.env
sed -i 's/AWS_DEFAULT_REGION=.*/AWS_DEFAULT_REGION=$AWS_DEFAULT_REGION/' .prod.env
sed -i 's/AWS_BUCKET=.*/AWS_BUCKET=$AWS_BUCKET/' .prod.env

chown -R www-data:www-data .
php artisan up
