<?php
require_once 'db_connection.php';
require_once 'factory_config.php';

function calculateTotalGP($pdo, $user_id) {
    try {
        // First, get the building column names dynamically
        $stmt = $pdo->query("
            SELECT GROUP_CONCAT(CONCAT('COALESCE(', COLUMN_NAME, ', 0)') SEPARATOR ' + ') as columns
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'buildings'
            AND TABLE_SCHEMA = DATABASE()
            AND COLUMN_NAME != 'id'
        ");
        $building_columns = $stmt->fetch(PDO::FETCH_ASSOC)['columns'];

        // Get all necessary data in a single query
        $stmt = $pdo->prepare("
            SELECT 
                u.population,
                (l.cleared_land + l.forest + l.mountain + l.river + l.lake + 
                 l.grassland + l.jungle + l.desert + l.tundra + l.used_land + l.urban_areas) as total_land,
                COALESCE((
                    SELECT ($building_columns)
                    FROM buildings
                    WHERE id = ?
                ), 0) as building_gp
            FROM users u
            JOIN land l ON u.id = l.id
            WHERE u.id = ?
        ");
        
        $stmt->execute([$user_id, $user_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            throw new Exception("User data not found");
        }

        // Get factory GP using the PHP config
        $factory_gp = 0;
        $stmt = $pdo->prepare("
            SELECT *
            FROM factories 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        $factories = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($factories) {
            global $FACTORY_CONFIG;
            foreach ($FACTORY_CONFIG as $factory_type => $config) {
                if (isset($factories[$factory_type]) && $factories[$factory_type] > 0) {
                    $factory_gp += $factories[$factory_type] * $config['gp_value'];
                }
            }
        }

        // Calculate components
        $population_gp = floor($data['population'] / 1000);
        $land_gp = $data['total_land'];
        $building_gp = $data['building_gp'];
        
        // Calculate total
        $total_gp = $population_gp + $land_gp + $factory_gp + $building_gp;

        // Update the user's GP in the database
        $stmt = $pdo->prepare("UPDATE users SET gp = ? WHERE id = ?");
        $stmt->execute([$total_gp, $user_id]);

        return [
            'population_gp' => $population_gp,
            'land_gp' => $land_gp,
            'factory_gp' => $factory_gp,
            'building_gp' => $building_gp,
            'total_gp' => $total_gp
        ];

    } catch (Exception $e) {
        error_log("Error in calculateTotalGP: " . $e->getMessage());
        throw $e;
    }
}
