FROM ubuntu:24.04

ARG DEBIAN_FRONTEND=noninteractive

COPY ./src /usr/src/fenrir-stability/src
COPY ./_*.php ./.en[v] ./composer.* /usr/src/fenrir-stability/

WORKDIR /usr/src/fenrir-stability

RUN apt-get update
RUN apt-get install php-cli php-xml composer php-bcmath -y
RUN composer install --no-dev
RUN composer dump-autoload -o

RUN php ./_register-commands.php

CMD [ "php", "./_index.php" ]
