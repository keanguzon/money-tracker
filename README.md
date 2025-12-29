# MoneyTrack (PHP)

## Run locally
- Requires PHP 8+
- `php -S 127.0.0.1:8000`
- Open `http://127.0.0.1:8000/`

## Deploy to Render (keeps Supabase)
This project includes a `Dockerfile` and `render.yaml`.

### Steps
1. Push this repo to GitHub.
2. In Render: New + → Blueprint → select your repo.
3. Set environment variables in Render:
   - `DB_DRIVER=pgsql`
   - `DB_HOST` = your Supabase host (pooler recommended)
   - `DB_PORT=5432`
   - `DB_NAME=postgres`
   - `DB_USER` = your Supabase DB user
   - `DB_PASS` = your Supabase DB password
   - `DB_SSLMODE=require`
4. Deploy.

### Notes
- This app auto-detects `APP_URL` from the request host, so you usually don’t need to set it.
- Don’t deploy `.env` (use Render env vars instead).
