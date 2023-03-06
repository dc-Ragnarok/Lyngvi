FROM fedora:37

COPY ./src /usr/src/fenrir-stability/src
COPY ./index.php /usr/src/fenrir-stability
COPY ./composer.* /usr/src/fenrir-stability/

WORKDIR /usr/src/fenrir-stability

RUN dnf update -y
RUN dnf install php-cli composer php-bcmath -y
RUN composer install
RUN composer dump-autoload -o

CMD [ "php", "./index.php" ]
