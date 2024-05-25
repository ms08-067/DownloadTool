FROM php:8.1-fpm

ARG NODE_VERSION=16
ENV TZ=Asia/Ho_Chi_Minh

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && mkdir -p /var/run/php/ \
    && mkdir -p /var/www/src/alpine \
    && apt-get update \
    && apt-get upgrade -y \
    && apt-get install libxslt1-dev libicu-dev libxml2-dev icu-devtools libonig-dev libpng-dev libzip-dev zlib1g-dev libldap2-dev libgmp-dev cron libcurl4-openssl-dev -y \
    && apt-get install unzip zip curl awscli pdftk git supervisor wait-for-it bash nano aria2 iputils-ping pv rsync tree mariadb-client sudo -y \
    && docker-php-ext-install curl intl gd zip xml mbstring ldap gmp \
    && docker-php-ext-install xsl bcmath \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && curl -sLS https://deb.nodesource.com/setup_$NODE_VERSION.x | bash \
    && apt-get install -y nodejs \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/src/alpine

ARG CONTAINER_ENV
ARG AWS_ACCESS_KEY_ID
ARG AWS_SECRET_ACCESS_KEY
ARG LOCAL_UID
ARG LOCAL_GID

COPY supervisord-$CONTAINER_ENV.conf /etc/supervisord.conf
COPY startup.sh /opt/startup.sh

RUN echo "Running environment $CONTAINER_ENV" \
    && whoami

RUN mkdir -p /var/www/src/alpine/storage \
    && mkdir -p /var/www/src/alpine/database \
    && touch /var/www/src/alpine/database/database.sqlite \
    && touch already_ran \
    && chmod 777 -R /var/www/src/alpine/storage

RUN if [ "$CONTAINER_ENV" = "local" ]; \
        then echo "change user to local UID $LOCAL_UID"; \
        usermod -u $LOCAL_UID www-data; \
        echo "change user group to local GID $LOCAL_GID"; \
        groupmod -g $LOCAL_GID www-data; \
        chown -R root:www-data /var/www/src/alpine; \
    else \
        chown -R www-data:www-data /var/www/src/alpine/storage; \
    fi;

RUN mkdir -p /var/www/src/alpine/storage/app/data_sdb_jobfolder \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_archivefolder \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_temp \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_temp \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_temp/job \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_temp/xml \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_temp_upload \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_temp_zip \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_unzipfolder \
    && mkdir -p /var/www/src/alpine/storage/app/manual/data_sdb_jobfolder \
    && mkdir -p /var/www/src/alpine/storage/app/manual/data_sdb_temp \
    && mkdir -p /var/www/src/alpine/storage/app/manual/data_sdb_temp/job \
    && mkdir -p /var/www/src/alpine/storage/app/manual/data_sdb_temp/xml \
    && mkdir -p /var/www/src/alpine/storage/app/manual/data_sdb_temp_upload \
    && mkdir -p /var/www/src/alpine/storage/app/manual/data_sdb_temp_zip \
    && mkdir -p /var/www/src/alpine/storage/app/manual/data_sdb_unzipfolder \
    && mkdir -p /var/www/src/alpine/storage/app/home/itadmin/data/webroot/jobfolder \
    && mkdir -p /var/www/src/alpine/storage/logs/authentication \
    && mkdir -p /var/www/src/alpine/storage/logs/bitrixAPIinfo \
    && mkdir -p /var/www/src/alpine/storage/logs/bitrixAPI \
    && mkdir -p /var/www/src/alpine/storage/logs/console_joblogs \
    && mkdir -p /var/www/src/alpine/storage/logs/crontab_joblogs \
    && mkdir -p /var/www/src/alpine/storage/logs/data_sdb \
    && mkdir -p /var/www/src/alpine/storage/logs/manual \
    && mkdir -p /var/www/src/alpine/storage/logs/manual/data_sdb \
    && mkdir -p /var/www/src/alpine/storage/logs/manual_download_freshdownload \
    && mkdir -p /var/www/src/alpine/storage/logs/manual_download_redownload \
    && mkdir -p /var/www/src/alpine/storage/logs/openrc \
    && mkdir -p /var/www/src/alpine/storage/logs/rocketchat_reminder \
    && mkdir -p /var/www/src/alpine/storage/logs/task_downloads \
    && mkdir -p /var/www/src/alpine/storage/logs/task_manual_downloads \
    && mkdir -p /var/www/src/alpine/storage/logs/task_uploads \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_asia \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_germany \
    && mkdir -p /var/www/src/alpine/storage/app/data_sdb_manual \
    && mkdir -p /var/www/src/alpine/public \
    && chmod 777 -R /var/www/src/alpine/public \
    && chmod 777 -R /var/www/src/alpine/storage \
    && echo $RANDOM > /var/www/src/alpine/lastrev-parse

RUN if [ "$CONTAINER_ENV" = "production" ]; \
        then echo "prod" > .env; \
    fi;

RUN cd ~ \
    && mkdir -p /run/nginx \
    && mkdir -p /var/lib/nginx/ \
    && rm -f /etc/nginx/sites-enabled/default \
    && rm -f /etc/nginx/sites-available/default \
    && sed -i 's/listen = 127.0.0.1.*/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/post_max_size.*/post_max_size = 20G/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize.*/upload_max_filesize = 20G/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit.*/memory_limit = 1G/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's~;zend_extension=opcache.*~zend_extension=opcache~' "$PHP_INI_DIR/php.ini" \
    && sed -i 's~;opcache.enable=.*~opcache.enable=0~' "$PHP_INI_DIR/php.ini" \
    && sed -i 's~;opcache.enable_cli=.*~opcache.enable_cli=1~' "$PHP_INI_DIR/php.ini" \
    && sed -i 's~;opcache.memory_consumption=.*~opcache.memory_consumption=128~' "$PHP_INI_DIR/php.ini" \
    && sed -i 's~;opcache.max_accelerated_files=.*~opcache.max_accelerated_files=10000~' "$PHP_INI_DIR/php.ini" \
    && sed -i 's~;opcache.revalidate_freq=.*~opcache.revalidate_freq=200~' "$PHP_INI_DIR/php.ini" \
    && mkdir -p /var/www/.aws \
    && printf '[profile default]\n\
region=eu-central-1\n\
output=json\n' > /var/www/.aws/config \
    && printf '[default]\n\
aws_access_key_id=%s\n\
aws_secret_access_key=%s\n' $AWS_ACCESS_KEY_ID $AWS_SECRET_ACCESS_KEY > /var/www/.aws/credentials \
    && chown www-data:www-data -R /var/www/.aws \
    && cd ~ \
    && mkdir -p .aws \
    && printf '[profile default]\n\
region=eu-central-1\n\
output=json\n' > .aws/config \
    && printf '[default]\n\
aws_access_key_id=%s\n\
aws_secret_access_key=%s\n' $AWS_ACCESS_KEY_ID $AWS_SECRET_ACCESS_KEY > .aws/credentials \
    && chown www-data:www-data -R .aws \
    && chown www-data:www-data -R /var/lib/nginx/


COPY . /var/www/src/alpine

RUN cd /var/www/src/alpine \
    && composer install \
    && composer update \
    && composer clear-cache \
    && touch /var/spool/cron/crontabs/www-data \
    && echo "* * * * * /var/www/src/alpine/logging.sh >> /var/www/src/alpine/storage/logs/crontab_joblogs/cronjob-\`date +\%Y-\%m-\%d\`.log 2>&1" >> /var/spool/cron/crontabs/www-data \
    && { crontab -l -u www-data; echo '* * * * * echo test >> /var/www/src/alpine/storage/logs/cron_test'; } | crontab -u www-data - \
    && { crontab -l -u www-data; echo '#ACTIVATE'; } | crontab -u www-data - \
    && php artisan cache:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && composer dump-autoload \
    && php artisan migrate \
    && php artisan up
