FROM php:8.3-cli-alpine AS lyngvi-php

RUN apk add --update linux-headers 

RUN apk add --no-cache pcre ${PHPIZE_DEPS} \
    libevent-dev openssl openssl-dev

RUN pecl install ev 

RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/f3108f64b4e1c1ce6eb462b159956461592b3e3e/web/installer -O - -q | php -- --quiet

CMD php /lyngvi/_index.php
