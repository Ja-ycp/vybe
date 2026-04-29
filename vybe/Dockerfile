FROM php:8.2-cli

WORKDIR /app

# Needed by config/database.php (PDO MySQL)
RUN docker-php-ext-install pdo_mysql

COPY . /app

ENV PORT=10000
EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
