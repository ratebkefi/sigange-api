ARG PHP_VERSION=7.4
ARG APP_ENV=prod

# Step 1 : Build Vendor in intermediate image

FROM composer as builder

WORKDIR /app/

COPY composer.* ./
RUN composer install --no-dev --ignore-platform-reqs


# Step 2 : Create PHP-FPM Image

FROM php:${PHP_VERSION}-fpm-alpine

# Create www-data group
#RUN set -x \
#	&& addgroup -g 82 -S www-data \
#	&& adduser -u 82 -D -S -G www-data www-data \
#    && exit 0 ; exit 1 \
#    && echo 0 \
#;

# Add required packages
RUN apk add --no-cache \
#    openssh-client \
#    postfix \
#    ffmpeg \
#    openssl \
#    ca-certificates
#    acl \
#    fcgi \
#    file \
#    gettext \
#    git \
#    jq \
    libxslt-dev \
;

# Add required PHP extensions
RUN docker-php-ext-install \
#    bz2 \
#    curl \
#    ffi \
#    ftp \
#    fileinfo \
#    gd2 \
#    gettext \
#    gmp \
#    intl \
#    imap \
#    ldap \
#    mbstring \
#    exif \     # Must be after mbstring as it depends on it
#    mysqli \
#    oci8_12c \ # Use with Oracle Database 12c Instant Client
#    odbc \
#    openssl \
#    pdo_firebird \
    pdo_mysql \
#    pdo_oci \
#    pdo_odbc \
#    pdo_pgsql \
#    pdo_sqlite \
#    pgsql \
#    shmop \
    xsl \
;

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app

# Copy Sources
COPY . .

# Add build vendor dependencies
COPY --from=builder /app/vendor ./vendor

# Create var dir
RUN mkdir var
RUN chown www-data: var
#RUN chmod a+w var

CMD php-fpm

EXPOSE 9000





