FROM php:8.2-cli

WORKDIR /app

# Required by config/database.php (PDO MySQL + SQLite fallback)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev pkg-config \
    && docker-php-ext-install pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# App currently lives in the repo subdirectory "vybe"
COPY vybe/ /app

ENV PORT=10000
EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
