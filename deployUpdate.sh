#!/bin/bash

if [ $# -eq 5 ]; then
    if [ "$1" != "develop" ] && [ "$1" != "production" ]; then
        echo "wrong environment parameter, must be develop or production"
        exit 1
    fi
else
    if [ $# -lt 5 ]; then
        echo "missing environment parameter, must be either develop or production"
    elif [ $# -gt 5 ]; then
        echo "too many environment parameters, must be either develop or production"
    fi
    exit 1
fi


cd /var/www/src/alpine/
file_list=$(find . -type f -maxdepth 1 -mindepth 1)
for file in $file_list
do
    #echo $file;
    if [[ $file == "./artisan" ]]; then
        php artisan down --render="errors::503maintenance"
        break
        #:
    else
        :
    fi
done



BRANCH=$([ "$1" == "production" ] && echo "master" || echo "develop")

git checkout $BRANCH
git fetch origin $BRANCH
git reset --hard origin/$BRANCH

if [ "$1" == "production" ]; then
    echo "prod" > .env
elif [ "$1" == "develop" ]; then
    echo "$1" > .env
fi

composer install --no-interaction


if [ "$1" == "production" ]; then
    php artisan migrate
elif [ "$1" == "develop" ]; then
    php artisan migrate
    #php artisan migrate:fresh --seed
fi

sed -i "s~define('ROCKET_CHAT_INSTANCE', 'rocketchat.lc:3000/');.*~define('ROCKET_CHAT_INSTANCE', 'https://chat.br24.vn/');~" ./app/helpers.php

if [ "$1" == "production" ]; then

    # sed -i "s/LARAVEL_WEBSOCKETS_PORT=.*/LARAVEL_WEBSOCKETS_PORT=$2/" .prod.env
    # sed -i "s/LARAVEL_WEBSOCKETS_PORT_DASHBOARD=.*/LARAVEL_WEBSOCKETS_PORT_DASHBOARD=$3/" .prod.env
    # sed -i "s/6008,.*/$3,/g" ./resources/js/app_echo_bootstrap.js

    sed -i "s~BITRIX24_DUS_WEBHOOK=.*~BITRIX24_DUS_WEBHOOK=$4~" .prod.env
    sed -i "s~BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID=.*~BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID=$5~" .prod.env

elif [ "$1" == "develop" ]; then

    # sed -i "s/LARAVEL_WEBSOCKETS_PORT=.*/LARAVEL_WEBSOCKETS_PORT=$2/" .develop.env
    # sed -i "s/LARAVEL_WEBSOCKETS_PORT_DASHBOARD=.*/LARAVEL_WEBSOCKETS_PORT_DASHBOARD=$3/" .develop.env
    # sed -i "s/6008,.*/$3,/g" ./resources/js/app_echo_bootstrap.js

    sed -i "s~BITRIX24_DUS_WEBHOOK=.*~BITRIX24_DUS_WEBHOOK=$4~" .develop.env
    sed -i "s~BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID=.*~BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID=$5~" .develop.env

fi

php artisan config:clear
php artisan config:cache
whoami
#chmod 775 /var/www/src/alpine/public/mix-manifest.json
chmod 777 -R /var/www/src/alpine/public

npm ci
if [ "$1" == "production" ]; then
    npx mix --production
elif [ "$1" == "develop" ]; then
    npx mix
fi


path_list=$(find . -type d -maxdepth 1 -mindepth 1)
for path in $path_list
do
    #echo $path;
    if [[ $path == "./storage" ]] || [[ $path == "./vendor" ]] || [[ $path == "./node_modules" ]]; then
        #echo " === SKIPPING === "
        :
    else
        if [ "$1" == "develop" ] || [ "$1" == "production" ]; then
            chown -R www-data:www-data $path
        elif [ "$1" == "local" ]; then
            chown -R root:www-data $path && chmod g+w -R $path
        fi
    fi
done

cd /var/www/src/alpine/storage/
path_list=$(find . -type d -maxdepth 1 -mindepth 1)
for path in $path_list
do
    #echo $path;
    if [[ $path == "./app" ]]; then
        #echo " === SKIPPING === "
        :
    else
        if [ "$1" == "develop" ] || [ "$1" == "production" ]; then
            chown -R www-data:www-data $path
        elif [ "$1" == "local" ]; then
            chown -R root:www-data $path && chmod g+w -R $path
        fi
    fi
done

cd /var/www/src/alpine/storage/app/
path_list=$(find . -type d -maxdepth 1 -mindepth 1)
for path in $path_list
do
    #echo $path;
    if [[ $path == "./data_sdb_jobfolder" ]] || [[ $path == "./data_sdb_manual" ]] || [[ $path == "./data_sdb_asia" ]] || [[ $path == "./data_sdb_germany" ]] || [[ $path == "./data_sdb_archivefolder" ]] || [[ $path == "./manual" ]]; then
        #echo " === SKIPPING === "
        :
    else
        if [ "$1" == "develop" ] || [ "$1" == "production" ]; then
            chown -R www-data:www-data $path
        elif [ "$1" == "local" ]; then
            chown -R root:www-data $path && chmod g+w -R $path
        fi
    fi
done

cd /var/www/src/alpine/storage/app/manual/
path_list=$(find . -type d -maxdepth 1 -mindepth 1)
for path in $path_list
do
    #echo $path;
    if [[ $path == "./data_sdb_jobfolder" ]]; then
        #echo " === SKIPPING === "
        :
    else
        if [ "$1" == "develop" ] || [ "$1" == "production" ]; then
            chown -R www-data:www-data $path
        elif [ "$1" == "local" ]; then
            chown -R root:www-data $path && chmod g+w -R $path
        fi
    fi
done


#cd /var/www/src/alpine/storage/logs/
#chown -R root:www-data . && chmod g+w .


cd /var/www/src/alpine/
file_list=$(find . -type f -maxdepth 1 -mindepth 1)
for file in $file_list
do
    #echo $file;
    if [[ $file == "./artisan" ]]; then
        php artisan up
        break
        #:
    else
        :
    fi
done

if [ "$1" == "develop" ] || [ "$1" == "production" ]; then
    chown www-data:www-data /var/www/src/alpine/storage/
    chown www-data:www-data /var/www/src/alpine/storage/app/
elif [ "$1" == "local" ]; then
    chown root:www-data /var/www/src/alpine/storage/ && chmod g+w /var/www/src/alpine/storage/
    chown root:www-data /var/www/src/alpine/storage/app/ && chmod g+w /var/www/src/alpine/storage/app/
fi

git rev-parse --short HEAD > /var/www/src/alpine/lastrev-parse

cat /var/www/src/alpine/lastrev-parse