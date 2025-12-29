FROM php:8.3-cli

# Install PDO drivers (Postgres for Supabase + MySQL as fallback) + curl for OAuth
RUN apt-get update \
  && apt-get install -y --no-install-recommends libpq-dev libpq5 libcurl4-openssl-dev \
  && docker-php-ext-install pdo_pgsql pgsql pdo_mysql curl \
  && php -m | grep -E '(^|\s)(pdo_pgsql|pgsql|pdo_mysql|curl)(\s|$)' \
  && apt-get purge -y --auto-remove libpq-dev libcurl4-openssl-dev \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

# Render/Railway provide $PORT; default to 8000 for local docker runs
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t /app"]
