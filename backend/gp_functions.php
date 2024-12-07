<?php
require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/factory_config.php';

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

        // Calculate military GP from units
        $stmt = $pdo->prepare("
            SELECT 
                SUM(firepower + armour + maneuver + FLOOR(hp/10)) as total_strength
            FROM units 
            WHERE player_id = ?
        ");
        $stmt->execute([$user_id]);
        $military_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $military_gp = floor(($military_data['total_strength'] ?? 0) / 10);

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
        $land_gp = floor($data['total_land'] / 10);
        $building_gp = $data['building_gp'] * 10;
        
        // Calculate total
        $total_gp = $population_gp + $land_gp + $factory_gp + $building_gp + $military_gp;

        // Update the user's GP in the database
        $stmt = $pdo->prepare("UPDATE users SET gp = ? WHERE id = ?");
        $stmt->execute([$total_gp, $user_id]);

        return [
            'population_gp' => $population_gp,
            'land_gp' => $land_gp,
            'factory_gp' => $factory_gp,
            'building_gp' => $building_gp,
            'military_gp' => $military_gp,
            'total_gp' => $total_gp
        ];

    } catch (Exception $e) {
        error_log("Error in calculateTotalGP: " . $e->getMessage());
        throw $e;
    }
}
