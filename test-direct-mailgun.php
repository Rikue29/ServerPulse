<?php

// Test script to send email directly via Mailgun API
$apiKey = '88986abb0e180651f5ae5da5782eb0fe-a1dad75f-46d63fad';
$domain = 'sandbox1903e7c34fd549419d635a5a38e4bf39.mailgun.org';
$email = '215746@student.upm.edu.my';

echo "Starting email test...\n";

// Initialize curl
$ch = curl_init("https://api.mailgun.net/v3/{$domain}/messages");
if (!$ch) {
    echo "Failed to initialize curl\n";
    exit(1);
}

try {
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "api:{$apiKey}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'from' => "ServerPulse Alerts <postmaster@{$domain}>",
        'to' => $email,
        'subject' => "URGENT: Test Alert Message",
        'text' => "This is a test email sent from test-direct-mailgun.php script at " . date('Y-m-d H:i:s') . ".\n\nIf you're seeing this, it means the direct API approach works."
    ]);

    // Execute the request
    echo "Sending request to Mailgun API...\n";
    $result = curl_exec($ch);
    
    if ($result === false) {
        echo "Curl error: " . curl_error($ch) . "\n";
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "Email sending attempt to {$email}\n";
        echo "HTTP Status: {$httpCode}\n";
        echo "API Response: {$result}\n";
    }
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
} finally {
    if ($ch) {
        curl_close($ch);
    }
} 