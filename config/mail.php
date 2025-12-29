<?php
/**
 * Mail Configuration and Helper Functions
 * Uses Brevo SMTP
 */

define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp-relay.brevo.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: '587');
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '9ef132001@smtp-brevo.com');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('SENDER_EMAIL', getenv('SENDER_EMAIL') ?: 'moneytrackerbukojuice@gmail.com');
define('SENDER_NAME', getenv('SENDER_NAME') ?: 'BukoJuice');

/**
 * Send an email using Brevo SMTP
 */
function sendEmail($toEmail, $toName, $subject, $htmlContent) {
    $username = SMTP_USERNAME;
    $password = SMTP_PASSWORD;
    
    if (empty($password)) {
        error_log('SMTP password is missing');
        return false;
    }

    try {
        $smtp = fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 30);
        
        if (!$smtp) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return false;
        }
        
        $response = fgets($smtp, 515);
        
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        
        stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, base64_encode($username) . "\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, base64_encode($password) . "\r\n");
        $response = fgets($smtp, 515);
        
        if (strpos($response, '235') === false) {
            error_log("SMTP authentication failed: $response");
            fclose($smtp);
            return false;
        }
        
        fputs($smtp, "MAIL FROM: <" . SENDER_EMAIL . ">\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, "RCPT TO: <$toEmail>\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        
        $boundary = md5(time());
        $textContent = strip_tags($htmlContent);
        
        $headers = "From: " . SENDER_NAME . " <" . SENDER_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . SENDER_EMAIL . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        
        fputs($smtp, "Subject: $subject\r\n");
        fputs($smtp, $headers);
        fputs($smtp, "\r\n");
        fputs($smtp, "--{$boundary}\r\n");
        fputs($smtp, "Content-Type: text/plain; charset=UTF-8\r\n\r\n");
        fputs($smtp, $textContent . "\r\n");
        fputs($smtp, "--{$boundary}\r\n");
        fputs($smtp, "Content-Type: text/html; charset=UTF-8\r\n\r\n");
        fputs($smtp, $htmlContent . "\r\n");
        fputs($smtp, "--{$boundary}--\r\n");
        fputs($smtp, "\r\n.\r\n");
        $response = fgets($smtp, 515);
        
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        return strpos($response, '250') !== false;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate a 6-digit OTP
 */
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}
?>
