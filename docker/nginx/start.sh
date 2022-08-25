#!/bin/bash

/bin/sed -i -e 's/APP_SERVER_NAME/'"$APP_SERVER_NAME"'/g' /etc/nginx/sites-available/symfony.conf

/usr/sbin/nginx;