FROM php:8.2-cli-alpine

# Install SQLite extension
RUN apk add --no-cache sqlite-dev \
    && docker-php-ext-install pdo_sqlite

WORKDIR /app

# Copy server file
COPY c-helper-server.php /app/

# Create volume for database
VOLUME ["/app/data"]

# Environment variables
ENV C_HELPER_KEY="change-me-in-production"
ENV C_HELPER_DB="/app/data/c-helper.db"
ENV C_HELPER_BASE="/workspace"

EXPOSE 8888

CMD ["php", "-S", "0.0.0.0:8888", "c-helper-server.php"]
