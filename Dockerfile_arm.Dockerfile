FROM php:8.0.1-fpm-buster

# Replace shell with bash so we can source files
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

# Set environment variables
RUN mkdir /usr/local/nvm

ENV appDir /var/www/html
ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 14.18.0

ARG ZF_DATABASE_HOST=db
ARG ZF_DATABASE_PORT=3306
ARG ZF_DATABASE_NAME=zenfit
ARG ZF_DATABASE_USER=root
ARG ZF_DATABASE_PASSWORD=root
ARG ZF_STRIPE_PUBLISHABLE_KEY=pk_test_7vZW4vcLV2pCWfaJsJSMWVXA
ARG ZF_STRIPE_SECRET_KEY=sk_test_GGFRHKtc7fNHZiKpsaUhUgrd
ARG ZF_STRIPE_CLIENT_ID=ca_CUqvjJAYkTZafYjZtdbetdbGZRgYXQ7S
ARG ZF_APP_HOSTNAME=https://zenfit.test
ARG ASSETS_VERSION=test
ARG MAILER_USER=test
ARG MAILER_PASSWORD=test
ARG JWT_SECRET=test
ARG SQS_PDF_URL=https://sqs.eu-central-1.amazonaws.com/175976746192/zenfit_pdf_generation_dev
ARG SQS_MEDIA_COMPRESSED_URL=https://sqs.eu-central-1.amazonaws.com/175976746192/zenfit_video_compressed_dev
ARG SQS_CHAT_MULTIPLE_URL=https://sqs.eu-central-1.amazonaws.com/175976746192/zenfit_chat_multiple_dev
ARG SQS_VIDEO_URL=https://sqs.eu-central-1.amazonaws.com/175976746192/zenfit_video_compress_dev
ARG SQS_VOICE_URL=https://sqs.eu-central-1.amazonaws.com/175976746192/zenfit_voice_message_dev
ARG AWS_MEDIA_CONVERT_ROLE=test
ARG APP_ENV=prod
ARG MYFITNESSPAL_CLIENT_ID=test
ARG MYFITNESSPAL_CLIENT_SECRET=test
ARG AWS_ACCESS_KEY_ID=test
ARG AWS_SECRET_ACCESS_KEY=test
ARG SENTRY_DSN=
ARG STRIPE_DK_TAX_RATE_ID=
ARG STRIPE_NO_TAX_RATE_ID=
ARG IMAGE_MAGICK_CONFIG=/etc/ImageMagick-6/policy.xml

ENV ZF_DATABASE_HOST ${ZF_DATABASE_HOST}
ENV ZF_DATABASE_PORT ${ZF_DATABASE_PORT}
ENV ZF_DATABASE_NAME ${ZF_DATABASE_NAME}
ENV ZF_DATABASE_USER ${ZF_DATABASE_USER}
ENV ZF_DATABASE_PASSWORD ${ZF_DATABASE_PASSWORD}
ENV ZF_STRIPE_PUBLISHABLE_KEY ${ZF_STRIPE_PUBLISHABLE_KEY}
ENV ZF_STRIPE_SECRET_KEY ${ZF_STRIPE_SECRET_KEY}
ENV ZF_STRIPE_CLIENT_ID ${ZF_STRIPE_CLIENT_ID}
ENV ZF_APP_HOSTNAME ${ZF_APP_HOSTNAME}
ENV ASSETS_VERSION ${ASSETS_VERSION}
ENV MAILER_USER ${MAILER_USER}
ENV MAILER_PASSWORD ${MAILER_PASSWORD}
ENV JWT_SECRET ${JWT_SECRET}
ENV RABBITMQ_DEFAULT_HOST ${RABBITMQ_DEFAULT_HOST}
ENV RABBITMQ_DEFAULT_USER ${RABBITMQ_DEFAULT_USER}
ENV RABBITMQ_DEFAULT_PASS ${RABBITMQ_DEFAULT_PASS}
ENV RABBITMQ_DEFAULT_VHOST ${RABBITMQ_DEFAULT_VHOST}
ENV SQS_PDF_URL ${SQS_PDF_URL}
ENV SQS_MEDIA_COMPRESSED_URL ${SQS_MEDIA_COMPRESSED_URL}
ENV SQS_VIDEO_URL ${SQS_VIDEO_URL}
ENV SQS_VOICE_URL ${SQS_VOICE_URL}
ENV SQS_CHAT_MULTIPLE_URL ${SQS_CHAT_MULTIPLE_URL}
ENV AWS_MEDIA_CONVERT_ROLE ${AWS_MEDIA_CONVERT_ROLE}
ENV MEMCACHED_URL ${MEMCACHED_URL}
ENV APP_ENV ${APP_ENV}
ENV MYFITNESSPAL_CLIENT_ID ${MYFITNESSPAL_CLIENT_ID}
ENV MYFITNESSPAL_CLIENT_SECRET ${MYFITNESSPAL_CLIENT_SECRET}
ENV AWS_ACCESS_KEY_ID ${AWS_ACCESS_KEY_ID}
ENV AWS_SECRET_ACCESS_KEY ${AWS_SECRET_ACCESS_KEY}
ENV SENTRY_DSN ${SENTRY_DSN}
ENV STRIPE_DK_TAX_RATE_ID ${STRIPE_DK_TAX_RATE_ID}
ENV STRIPE_NO_TAX_RATE_ID ${STRIPE_NO_TAX_RATE_ID}

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

RUN echo 'deb http://deb.debian.org/debian buster-backports main' > /etc/apt/sources.list.d/backports.list

RUN apt-get update && apt-get install -y \
    apt-utils \
    libpng-dev \
    locales \
    libssl-dev \
    libmemcached-dev \
    git \
    unzip \
    build-essential \
    libpython3-dev \
    python3-pip \
    python3-cffi \
    libcairo2 libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libgdk-pixbuf2.0-0 \
    libffi-dev \
    shared-mime-info \
    ffmpeg \
    supervisor \
    libzip-dev \
    wget \
    xfonts-75dpi \
    xfonts-base

RUN sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

RUN pip3 install --upgrade cffi
RUN pip3 install numpy

RUN apt-get update && apt-get install -t buster-backports -y weasyprint

RUN apt-get update \
    && apt-get -y install \
        libmagickwand-dev --no-install-recommends \
        libjpeg62-turbo-dev \
        librabbitmq-dev \
        libssh-dev \
        libxpm-dev \
        libxslt-dev \
        libvpx-dev \
        libfreetype6-dev \
        ghostscript \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-configure bcmath \
        --enable-bcmath \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        intl \
        xml \
        xsl \
        exif \
        zip \
        bcmath \
        sockets \
        gd \
        opcache \
    && pecl install imagick \
    && pecl install memcached \
    && pecl install apcu \
    && docker-php-ext-enable imagick \
    && docker-php-ext-enable apcu \
    && docker-php-ext-enable memcached \
    && rm -r /var/lib/apt/lists/*

RUN if [ -f $IMAGE_MAGICK_CONFIG ] ; then sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/g' $IMAGE_MAGICK_CONFIG ; else echo did not see file $IMAGE_MAGICK_CONFIG ; fi

# Install nvm with node and npm
RUN curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.38.0/install.sh | bash \
    && source $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

# Set up our PATH correctly so we don't have to long-reference npm, node, &c.
ENV NODE_PATH $NVM_DIR/versions/node/v$NODE_VERSION/lib/node_modules
ENV PATH      $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

WORKDIR ${appDir}

# Install Datadog not compatible with arm
#RUN wget https://github.com/DataDog/dd-trace-php/releases/download/0.45.2/datadog-php-tracer_0.45.2_amd64.deb
#RUN dpkg -i datadog-php-tracer_0.45.2_amd64.deb

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --2
RUN composer --version

# Set timezone
RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime
RUN "date"

# Install HTML to PDF
RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.buster_arm64.deb
RUN apt-get install -f
RUN ln -sf /var/www/html/docker/wkhtmltopdf/wkhtmltoimage /usr/local/bin/wkhtmltoimage
RUN ln -sf /var/www/html/docker/wkhtmltopdf/wkhtmltopdf /usr/local/bin/wkhtmltopdf
RUN dpkg -i wkhtmltox_0.12.6-1.buster_arm64.deb

RUN echo 'alias sf="php bin/console"' >> ~/.bashrc

# Install node dependencies
ADD package.json .
RUN npm install --force

# Setup Supervisor
RUN mkdir -p /var/log/supervisor
ADD docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Add php config to container
ADD docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
ADD docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
ADD docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

ADD . .

RUN usermod -u 1000 www-data

#Install dev dependencies for testing purposes
RUN composer install --no-ansi --no-interaction --no-progress  --optimize-autoloader --classmap-authoritative --dev
RUN bin/console cache:warmup --env dev

# Build js files
RUN npm run webpack-prod

# Install php dependencies (for production)
RUN composer install --no-ansi --no-interaction --no-progress --optimize-autoloader --classmap-authoritative

# Update translations
RUN php bin/console translation:update --output-format=yaml_tree --force en
RUN php bin/console translation:update --output-format=yaml_tree --force da
RUN php bin/console translation:update --output-format=yaml_tree --force sv
RUN php bin/console translation:update --output-format=yaml_tree --force nb
RUN php bin/console translation:update --output-format=yaml_tree --force de
RUN php bin/console translation:update --output-format=yaml_tree --force fi
RUN php bin/console translation:update --output-format=yaml_tree --force nl

RUN chown -R 1000:1000 var/cache/

# Clear cache
RUN php bin/console cache:clear --no-warmup --env=prod

# not compatible with arm
#RUN chmod -R 0777 /var/www/html

VOLUME ["/var/www/html"]

LABEL com.datadoghq.ad.logs='[{"source": "app", "service": "app"}]'

CMD ["/usr/bin/supervisord"]

