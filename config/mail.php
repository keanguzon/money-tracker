<?php
/**
 * Mail Configuration and Helper Functions
 * Uses Brevo (Sendinblue) API
 */

define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
define('SENDER_EMAIL', getenv('SENDER_EMAIL') ?: 'moneytrackerbukojuice@gmail.com');
define('SENDER_NAME', getenv('SENDER_NAME') ?: 'BukoJuice');

/**
 * Send an email using Brevo API
 */
function sendEmail($toEmail, $toName, $subject, $htmlContent) {
    $apiKey = BREVO_API_KEY;
    
    if (empty($apiKey)) {
        error_log('Brevo API Key is missing');
        return false;
    }

    $url = 'https://api.brevo.com/v3/smtp/email';
    
    $data = [
        'sender' => [
            'name' => SENDER_NAME,
            'email' => SENDER_EMAIL
        ],
        'to' => [
            [
                'email' => $toEmail,
                'name' => $toName
            ]
        ],
        'subject' => $subject,
        'htmlContent' => $htmlContent
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    } else {
        // Log detailed error for debugging
        error_log("Brevo Email Failed. HTTP Code: $httpCode");
        if ($curlError) {
            error_log("Curl Error: $curlError");
        }
        error_log("Brevo Response: " . $response);
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
