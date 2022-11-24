# Digital Signage Server

## Get Started

### Development

#### Requirements

* [Composer](https://getcomposer.org/)
* [Symfony CLI](https://symfony.com/download)

#### Install

    git clone https://kvasir.hexaglobe.net/advanced-suite/digital-signage-server.git
    cd digital-signage-server
    composer install
    symfony serve

To override environment variables, use a `.env.local` file in the root directory (this file is git-ignored) 

### Docker

#### Docker architecture

    opt/
    └─ docker/
        ├─ apps/
        |   └─ digital-signage-api/             # APP_DIR
        |       ├─ .env
        |       └─ docker-compose.yml
        ├─ confs/
        |   └─ digital-signage-api/             # CONF_DIR
        |       ├─ nginx/
        |       |   └─ default.conf             # Nginx conf
        |       └─ symfony/
        |           ├─ .env                     # Symfony .env.local file
        |           └─ jwt/                     # JWT certificate directory
        |               ├─ private.pem
        |               └─ publi.pem
        ├─ db
        |   └─ digital-signage-api/             # DB_DIR (Automatically created by docker-compose)
        └─ logs
            └─ digital-signage-api/             # LOG_DIR (Not used for now)

#### ${APP_DIR}/docker-compose.yml

    version: '3'
    
    services:
    
      # Proxy
      web:
        image: nginx:${NGINX_IMAGE_VERSION}
        labels:
          - "traefik.enable=true"
          - "traefik.http.routers.${APP_NAME}.rule=Host(`${APP_HOST}`)"
          - "traefik.http.routers.${APP_NAME}.entrypoints=websecure"
          - "traefik.http.routers.${APP_NAME}.tls.certresolver=letsencryptresolver"
          - "traefik.http.services.${APP_NAME}.loadbalancer.server.port=80"
        volumes:
          - ${CONF_DIR}/nginx:/etc/nginx/conf.d
          - app-public:/var/www/app/public:ro
        # ports:
          # - ${NGINX_PORT}:80
        depends_on:
          - application
        networks:
          - traefik
          - php
    
      # Application
      application:
        image: ${APP_IMAGE}:${APP_IMAGE_VERSION}
        volumes:
          - ${CONF_DIR}/symfony/jwt:/var/www/app/config/jwt
          - ${CONF_DIR}/symfony/.env:/var/www/app/.env.local
          - app-public:/var/www/app/public
        #command: [ "php bin/console lexik:jwt:generate-keypair --skip-if-exists" ]
        depends_on:
          - database
        networks:
          - php
          - db
    
      # Database
      database:
        image: mysql:${MYSQL_IMAGE_VERSION}
        volumes:
          - ${DB_DIR}:/var/lib/mysql
        command: [ "--default-authentication-plugin=mysql_native_password" ]
        environment:
          MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
          MYSQL_DATABASE: ${MYSQL_DATABASE}
          MYSQL_USER: ${MYSQL_USER}
          MYSQL_PASSWORD: ${MYSQL_PASSWORD}
        ports:
          - ${MYSQL_PORT}:3306
        networks:
          - db
    
    networks:
      traefik:
        external: true
      db:
      php:
    
    volumes:
      app-public:

#### ${APP_DIR}/.env

    APP_NAME=digital-signage-api
    APP_HOST=digital-signage.docker.local
    
    # Global
    CONF_DIR=/opt/docker/confs/digital-signage-api
    DB_DIR=/opt/docker/db/digital-signage-api
    LOG_DIR=/opt/docker/logs/digital-signage-api
    
    # Nginx
    NGINX_IMAGE_VERSION=1.19
    NGINX_PORT=8000
    
    # Application
    APP_IMAGE=registry.kvasir.hexaglobe.net/advanced-suite/back/digital-signage-api
    APP_IMAGE_VERSION=
    APP_JWT_PASSPHRASE=
    
    # MySQL
    MYSQL_IMAGE_VERSION=8.0
    MYSQL_PORT=
    MYSQL_ROOT_PASSWORD=
    MYSQL_DATABASE=signage
    MYSQL_USER=signage
    MYSQL_PASSWORD=

#### ${CONF_DIR}/nginx/default.conf

    server {
    server_name digital-signage-api.docker.local;
    root /var/www/app/public;
    
        location / {
            # try to serve file directly, fallback to index.php
            try_files $uri /index.php$is_args$args;
        }
    
        location ~ ^/index\.php(/|$) {
            fastcgi_pass application:9000;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
    
            # Fix ApiPlatform header issues
            fastcgi_buffers 16 16k;
            fastcgi_buffer_size 32k;
    
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $document_root;
            internal;
        }
    
        # return 404 for all other php files not matching the front controller
        # this prevents access to other php files you don't want to be accessible.
        location ~ \.php$ {
            return 404;
        }
    
        # If enabled, logs will be write inside the container
        # If disabled,  logs can be read with docker logs command
        # error_log /var/log/nginx/project_error.log;
        # access_log /var/log/nginx/project_access.log;
    }


### Docker

#### Install Docker on a local machine

    git clone https://kvasir.hexaglobe.net/advanced-suite/digital-signage-server.git
    cd digital-signage-server/resources/docker/
    cp .env.dist .env
    docker-compose up -d
    docker-compose exec php bash
    cd /var/www/signage 
    composer install
    php bin/console doctrine:schema:update --dump-sql
    php bin/console doctrine:schema:update --force

### local test link

    http://localhost:8001/api/


### to install on a development server

use : 

    docker-compose -f docker-compose.dev.yml up -d
    
instant of :

    docker-compose up -d

and change the data in the .env file
