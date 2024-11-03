<?php
require_once 'factory_config.php';

$type = $_GET['type'] ?? '';

if (isset($FACTORY_CONFIG[$type])) {
    header('Content-Type: application/json');
    echo json_encode([
        'input' => $FACTORY_CONFIG[$type]['input'],
        'output' => $FACTORY_CONFIG[$type]['output']
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Factory type not found']);
}