<?php

// Simple test script to verify agent API endpoints
// Run this from the project root: php test_agent_api.php

require_once 'vendor/autoload.php';

$baseUrl = 'http://localhost:8000'; // Adjust this to your local server URL
$testServerIp = '192.168.1.100'; // Test IP address

echo "Testing ServerPulse Agent API Integration\n";
echo "========================================\n\n";

// Test 1: Agent Registration
echo "1. Testing Agent Registration...\n";

$registrationData = [
    'server_ip' => $testServerIp,
    'hostname' => 'test-server',
    'agent_version' => '1.0.0',
    'system_info' => [
        'os' => 'Ubuntu 20.04',
        'kernel' => '5.4.0-74-generic',
        'arch' => 'x86_64'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/v1/agents/register');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registrationData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

$responseData = json_decode($response, true);

if ($httpCode === 200 && isset($responseData['success']) && $responseData['success']) {
    echo "✓ Registration successful!\n";
    $agentId = $responseData['agent_id'];
    $authToken = $responseData['auth_token'];
    
    echo "Agent ID: $agentId\n";
    echo "Auth Token: " . substr($authToken, 0, 10) . "...\n\n";
    
    // Test 2: Heartbeat
    echo "2. Testing Heartbeat...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/api/v1/agents/$agentId/heartbeat");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $heartbeatResponse = curl_exec($ch);
    $heartbeatCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: $heartbeatCode\n";
    echo "Response: $heartbeatResponse\n\n";
    
    // Test 3: Send Metrics
    echo "3. Testing Metrics Submission...\n";
    
    $metricsData = [
        'timestamp' => date('c'),
        'metrics' => [
            'cpu_usage' => 45.2,
            'memory_usage' => 67.8,
            'disk_usage' => 23.4,
            'uptime' => 123456,
            'load_average' => 1.5
        ],
        'services' => [
            ['name' => 'ssh', 'status' => 'active'],
            ['name' => 'nginx', 'status' => 'active'],
            ['name' => 'mysql', 'status' => 'active']
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/api/v1/agents/$agentId/metrics");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($metricsData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $authToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $metricsResponse = curl_exec($ch);
    $metricsCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: $metricsCode\n";
    echo "Response: $metricsResponse\n\n";
    
    if ($metricsCode === 200) {
        echo "✓ Metrics submission successful!\n";
    } else {
        echo "✗ Metrics submission failed!\n";
    }
    
} else {
    echo "✗ Registration failed!\n";
    if (isset($responseData['error'])) {
        echo "Error: " . $responseData['error'] . "\n";
    }
}

echo "\nTest completed.\n";
echo "Note: Make sure you have a server with IP $testServerIp in your ServerPulse database before running this test.\n";
