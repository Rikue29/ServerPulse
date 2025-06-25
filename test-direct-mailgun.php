<?php

// Load environment variables if not already loaded
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
}

// Test script to send email directly via Mailgun API
$apiKey = getenv('MAILGUN_SECRET');
$domain = getenv('MAILGUN_DOMAIN');
$email = '215746@student.upm.edu.my'; // You might want to make this configurable too

if (empty($apiKey) || empty($domain)) {
    echo "Error: Mailgun credentials not found in environment.\n";
    echo "Please set MAILGUN_SECRET and MAILGUN_DOMAIN in your .env file.\n";
    exit(1);
}

echo "Starting email test with domain: $domain\n";

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