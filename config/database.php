<?php
/**
 * Database Configuration
 * BukoJuice Application
 */

function env_value(string $key, $default = null) {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

// Supported drivers: mysql, pgsql
define('DB_DRIVER', env_value('DB_DRIVER', 'mysql'));

define('DB_HOST', env_value('DB_HOST', 'localhost'));
define('DB_NAME', env_value('DB_NAME', 'money_tracker'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));
define('DB_PORT', (int) env_value('DB_PORT', DB_DRIVER === 'pgsql' ? 5432 : 3306));
define('DB_CHARSET', env_value('DB_CHARSET', 'utf8mb4'));

// For Postgres/Supabase, SSL is typically required (use: require)
define('DB_SSLMODE', env_value('DB_SSLMODE', 'prefer'));

// Optional: force IPv4 by providing an explicit host address (useful on platforms without IPv6 egress)
define('DB_HOSTADDR', env_value('DB_HOSTADDR', ''));

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            if (DB_DRIVER === 'pgsql') {
                $hostaddr = '';

                if (is_string(DB_HOSTADDR) && DB_HOSTADDR !== '') {
                    $hostaddr = DB_HOSTADDR;
                } else {
                    // Prefer IPv4 when possible to avoid "Network is unreachable" on IPv6-only DNS answers.
                    // This is especially common when connecting to Supabase from some PaaS providers.
                    try {
                        $records = dns_get_record(DB_HOST, DNS_A);
                        if (is_array($records) && !empty($records[0]['ip'])) {
                            $hostaddr = $records[0]['ip'];
                        }
                    } catch (Throwable $ignored) {
                        $hostaddr = '';
                    }
                }

                $dsn = "pgsql:host=" . DB_HOST;
                if ($hostaddr !== '') {
                    $dsn .= ";hostaddr=" . $hostaddr;
                }
                $dsn .= ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=" . DB_SSLMODE;
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            }
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $availableDrivers = [];
            try {
                $availableDrivers = PDO::getAvailableDrivers();
            } catch (Throwable $ignored) {
                $availableDrivers = [];
            }

            $driverInfo = 'DB_DRIVER=' . DB_DRIVER;
            if (!empty($availableDrivers)) {
                $driverInfo .= ' (available: ' . implode(', ', $availableDrivers) . ')';
            }

            error_log('Database connection failed: ' . $e->getMessage() . ' | ' . $driverInfo);
            die("Database connection failed: " . $e->getMessage() . ' | ' . $driverInfo);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function to get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}
?>
