FROM php:8.2-cli

WORKDIR /app

# Required by config/database.php (PDO MySQL)
RUN docker-php-ext-install pdo_mysql

# App currently lives in the repo subdirectory "vybe"
COPY vybe/ /app

ENV PORT=10000
EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
