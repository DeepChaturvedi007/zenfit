FROM ubuntu:21.10

RUN apt-get update && apt-get install -y \
    nginx

ADD nginx.conf /etc/nginx/
ADD symfony.conf /etc/nginx/sites-available/
ADD ./start.sh /start.sh

RUN ln -s /etc/nginx/sites-available/symfony.conf /etc/nginx/sites-enabled/symfony
RUN rm /etc/nginx/sites-enabled/default
RUN chmod +x /start.sh

RUN usermod -u 1000 www-data

LABEL com.datadoghq.ad.logs='[{"source": "nginx", "service": "nginx"}]'

CMD ["/start.sh"]

EXPOSE 80
EXPOSE 443
