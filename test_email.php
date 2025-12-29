<?php
// Load app config (which loads .env)
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/mail.php';

echo "Testing Email Sending...\n";
echo "API Key Length: " . strlen(BREVO_API_KEY) . "\n";
echo "Sender: " . SENDER_EMAIL . "\n";

$toEmail = 'jngdas25@gmail.com'; // Using the email from your screenshot
$toName = 'Test User';
$subject = 'Test Email from BukoJuice';
$content = '<h1>It works!</h1><p>This is a test email.</p>';

echo "Sending to: $toEmail...\n";

// Copy of sendEmail function but with debug output
$apiKey = BREVO_API_KEY;
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
    'htmlContent' => $content
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

echo "\n--- Result ---\n";
echo "HTTP Code: $httpCode\n";
if ($curlError) {
    echo "Curl Error: $curlError\n";
}
echo "Response: $response\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "\n✅ SUCCESS! Email sent.\n";
} else {
    echo "\n❌ FAILED! Check the response above.\n";
}
?>
