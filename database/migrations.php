<?php
/**
 * Database Migration Runner
 * BukoJuice Application
 * 
 * Visit this page to run pending migrations
 * DELETE THIS FILE after running in production
 */

require_once __DIR__ . '/../config/database.php';

$migrations = [
    'add_oauth_columns' => "
        ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(20);
        ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_provider_id VARCHAR(100);
        ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar TEXT;
        CREATE INDEX IF NOT EXISTS idx_users_oauth ON users(oauth_provider, oauth_provider_id);
    ",
    'add_profile_picture' => "
        ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture TEXT;
        CREATE INDEX IF NOT EXISTS idx_users_profile_picture ON users(profile_picture);
    ",
    'add_is_verified' => "
        ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified BOOLEAN DEFAULT FALSE;
        CREATE INDEX IF NOT EXISTS idx_users_is_verified ON users(is_verified);
    "
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migrations - BukoJuice</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .migration { background: #f8f9fa; padding: 1rem; margin: 1rem 0; border-radius: 8px; border-left: 4px solid #10b981; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        button { background: #10b981; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #059669; }
        pre { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 6px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🗄️ Database Migrations</h1>
    <p>Run pending database migrations for BukoJuice</p>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migrations'])) {
        try {
            $db = getDB();
            echo "<div class='migration'>";
            echo "<h3>Running Migrations...</h3>";
            
            foreach ($migrations as $name => $sql) {
                try {
                    $db->exec($sql);
                    echo "<p class='success'>✓ Migration '{$name}' completed successfully</p>";
                } catch (PDOException $e) {
                    echo "<p class='error'>✗ Migration '{$name}' failed: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
            
            echo "<p><strong>All migrations processed!</strong></p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='migration'>";
            echo "<p class='error'>Database connection error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }
    ?>
    
    <div class="migration">
        <h3>Pending Migrations:</h3>
        <ul>
            <li><strong>add_oauth_columns</strong> - Adds oauth_provider, oauth_provider_id, and avatar columns</li>
            <li><strong>add_profile_picture</strong> - Adds profile_picture column to users table</li>
            <li><strong>add_is_verified</strong> - Adds is_verified column for email verification</li>
        </ul>
    </div>
    
    <form method="POST">
        <button type="submit" name="run_migrations" onclick="return confirm('Are you sure you want to run these migrations?')">
            Run All Migrations
        </button>
    </form>
    
    <div class="migration">
        <h3>⚠️ Important</h3>
        <p>Delete this file (<code>migrations.php</code>) after running migrations in production for security.</p>
    </div>
</body>
</html>
