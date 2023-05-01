FROM ubuntu:22.10

ARG DEBIAN_FRONTEND=noninteractive

COPY ./src /usr/src/fenrir-stability/src
COPY ./index.php /usr/src/fenrir-stability
COPY ./.env /usr/src/fenrir-stability
COPY ./composer.* /usr/src/fenrir-stability/

WORKDIR /usr/src/fenrir-stability

RUN apt-get update
RUN apt-get install php-cli php-xml php-dompdf composer php-bcmath -y
RUN composer install
RUN composer dump-autoload -o

CMD [ "php", "./index.php" ]
