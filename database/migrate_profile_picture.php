<?php
/**
 * Add profile_picture column migration
 */

// Set the correct driver for Supabase
putenv('DB_DRIVER=pgsql');

require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    
    echo "Connected to database successfully\n";
    echo "Running migration...\n\n";
    
    // Add profile_picture column
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture TEXT";
    $db->exec($sql);
    echo "✓ Added profile_picture column\n";
    
    // Create index
    $sql = "CREATE INDEX IF NOT EXISTS idx_users_profile_picture ON users(profile_picture)";
    $db->exec($sql);
    echo "✓ Created index on profile_picture\n";
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
