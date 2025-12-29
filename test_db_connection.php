<?php
// Load app config (which loads .env)
require_once __DIR__ . '/config/app.php';
// Load database configuration
require_once __DIR__ . '/config/database.php';

echo "Testing database connection...\n";
echo "Host: " . DB_HOST . "\n";
echo "Port: " . DB_PORT . "\n";
echo "User: " . DB_USER . "\n";
// Don't print password for security, but we use it.

try {
    $db = getDB();
    echo "\n✅ SUCCESS! Connected to the database successfully.\n";
    echo "The password in your .env file is CORRECT.\n";
} catch (Exception $e) {
    echo "\n❌ FAILED! Could not connect.\n";
    echo "Error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'password authentication failed') !== false) {
        echo "\n👉 DIAGNOSIS: The password is WRONG.\n";
        echo "Please go to Supabase Dashboard > Project Settings > Database > Reset Database Password\n";
        echo "and set it to exactly: " . DB_PASS . "\n";
    }
}
?>
