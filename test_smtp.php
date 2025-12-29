<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';

echo "<h2>Testing Brevo SMTP Connection</h2>";

$host = getenv('SMTP_HOST') ?: 'smtp-relay.brevo.com';
$port = getenv('SMTP_PORT') ?: '587';
$username = getenv('SMTP_USERNAME') ?: '9ef132001@smtp-brevo.com';
$password = getenv('SMTP_PASSWORD') ?: '';

echo "<p>Host: $host</p>";
echo "<p>Port: $port</p>";
echo "<p>Username: $username</p>";
echo "<p>Password: " . (empty($password) ? "MISSING!" : "Set (length: " . strlen($password) . ")") . "</p>";

if (empty($password)) {
    die("<p style='color:red'>ERROR: SMTP password is not set in environment!</p>");
}

echo "<hr><h3>Testing Connection...</h3>";

$smtp = @fsockopen($host, $port, $errno, $errstr, 30);

if (!$smtp) {
    die("<p style='color:red'>Connection failed: $errstr ($errno)</p>");
}

echo "<p style='color:green'>Connected successfully!</p>";

$response = fgets($smtp, 515);
echo "<p>Server: $response</p>";

fputs($smtp, "EHLO localhost\r\n");
$response = fgets($smtp, 515);
echo "<p>EHLO: $response</p>";

fputs($smtp, "STARTTLS\r\n");
$response = fgets($smtp, 515);
echo "<p>STARTTLS: $response</p>";

if (strpos($response, '220') === false) {
    die("<p style='color:red'>STARTTLS not supported: $response</p>");
}

if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    die("<p style='color:red'>Failed to enable TLS encryption</p>");
}

echo "<p style='color:green'>TLS encryption enabled!</p>";

fputs($smtp, "EHLO localhost\r\n");
$response = fgets($smtp, 515);
echo "<p>EHLO (after TLS): $response</p>";

fputs($smtp, "AUTH LOGIN\r\n");
$response = fgets($smtp, 515);
echo "<p>AUTH LOGIN: $response</p>";

fputs($smtp, base64_encode($username) . "\r\n");
$response = fgets($smtp, 515);
echo "<p>Username sent: $response</p>";

fputs($smtp, base64_encode($password) . "\r\n");
$response = fgets($smtp, 515);
echo "<p>Password sent: $response</p>";

if (strpos($response, '235') === false) {
    echo "<p style='color:red'>Authentication failed! Response: $response</p>";
} else {
    echo "<p style='color:green'>Authentication successful!</p>";
}

fputs($smtp, "QUIT\r\n");
fclose($smtp);

echo "<hr><h3>Now testing actual email send...</h3>";

require_once __DIR__ . '/config/mail.php';

$result = sendEmail('jngdas25@gmail.com', 'Test User', 'Test from BukoJuice', '<h1>Test Email</h1><p>This is a test.</p>');

if ($result) {
    echo "<p style='color:green'>Email sent successfully!</p>";
} else {
    echo "<p style='color:red'>Email sending failed!</p>";
}
?>
