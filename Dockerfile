FROM php:8.3-cli

# Install PDO drivers (Postgres for Supabase + MySQL as fallback)
RUN apt-get update \
  && apt-get install -y --no-install-recommends libpq-dev \
  && docker-php-ext-install pdo_pgsql pgsql pdo_mysql \
  && apt-get purge -y --auto-remove libpq-dev \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

# Render/Railway provide $PORT; default to 8000 for local docker runs
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t /app"]
