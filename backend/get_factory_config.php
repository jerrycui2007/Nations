<?php
require_once 'factory_config.php';
require_once 'resource_config.php';

$factory_type = $_GET['type'] ?? '';

if (isset($FACTORY_CONFIG[$factory_type])) {
    $config = $FACTORY_CONFIG[$factory_type];
    
    // Add display names for resources
    $config['input'] = array_map(function($input) use ($RESOURCE_CONFIG) {
        $input['display_name'] = $RESOURCE_CONFIG[$input['resource']]['display_name'];
        return $input;
    }, $config['input']);
    
    $config['output'] = array_map(function($output) use ($RESOURCE_CONFIG) {
        $output['display_name'] = $RESOURCE_CONFIG[$output['resource']]['display_name'];
        return $output;
    }, $config['output']);
    
    echo json_encode($config);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Factory type not found']);
}