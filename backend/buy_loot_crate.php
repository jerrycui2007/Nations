<?php
session_start();
require_once 'db_connection.php';
require_once 'equipment_config.php';
require_once 'continent_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$crate_type = $_POST['crate_type'] ?? '';
$unit_type = $_POST['unit_type'] ?? '';

if (!isset($CRATE_CONFIG[$crate_type]) || !isset($TYPE_CONFIG[$unit_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid crate or unit type']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Check if user can afford the crate
    $cost = $CRATE_CONFIG[$crate_type]['cost'];
    $stmt = $pdo->prepare("SELECT loot_token FROM commodities WHERE id = ? AND loot_token >= ?");
    $stmt->execute([$user_id, $cost]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Not enough loot tokens");
    }
    
    // Deduct cost
    $stmt = $pdo->prepare("UPDATE commodities SET loot_token = loot_token - ? WHERE id = ?");
    $stmt->execute([$cost, $user_id]);
    
    $generated_equipment = [];
    
    // Generate equipment for each slot in the crate
    foreach ($CRATE_CONFIG[$crate_type]['contents'] as $roll_type) {
        // Select rarity based on probability config
        $rarity_weights = $PROBABILITY_CONFIG[$roll_type];
        $total_weight = array_sum($rarity_weights);
        $roll = mt_rand(1, $total_weight);
        
        $current_weight = 0;
        $selected_rarity = '';
        foreach ($rarity_weights as $rarity => $weight) {
            $current_weight += $weight;
            if ($roll <= $current_weight) {
                $selected_rarity = $rarity;
                break;
            }
        }
        
        // Determine if item is foil (5% chance)
        $is_foil = (mt_rand(1, 20) === 1);
        $stat_total = $STAT_TOTAL_CONFIG[$selected_rarity];
        if ($is_foil) $stat_total *= 2;
        
        // Select random equipment type (excluding Battle Juice)
        $available_types = array_slice($TYPE_CONFIG[$unit_type], 0, 3, true);
        $type_index = array_rand($available_types);
        $equipment_type = $available_types[$type_index];
        
        // Insert equipment
        $stmt = $pdo->prepare("
            INSERT INTO equipment (user_id, name, type, rarity, is_foil)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $equipment_type, $equipment_type, $selected_rarity, $is_foil]);
        $equipment_id = $pdo->lastInsertId();
        
        // Distribute stats
        $remaining_points = $stat_total;
        $used_buff_types = [];  // Track used buff types during generation

        while ($remaining_points > 0) {
            // 20% chance for special buff
            if (mt_rand(2, 5) === 1 && $remaining_points >= 1) {
                $buff_type = array_rand($POSSIBLE_BUFF_CONFIG);
                $possible_targets = $POSSIBLE_BUFF_CONFIG[$buff_type];
                $target = $possible_targets[array_rand($possible_targets)];
                $value = 1 + (0.1 * $remaining_points);
                
                // Generate description before database insertion
                $description = "";
                if ($buff_type === 'AllStatsMultiplier') {
                    // Handle continent multiplier buff using CONTINENT_CONFIG
                    $continent_name = $CONTINENT_CONFIG[$target] ?? 'Unknown Location';
                    $description = number_format($value, 1) . "x all stats in " . $continent_name;
                } else {
                    // Handle unit type multiplier buff
                    $description = number_format($value, 1) . "x firepower against " . $target . " units";
                }
                
                // Insert buff with generated description
                $stmt = $pdo->prepare("
                    INSERT INTO buffs (unit_id, description, buff_type, value, target, equipment_id)
                    VALUES (0, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$description, $buff_type, $value, $target, $equipment_id]);
                $buff_id = $pdo->lastInsertId();

                // Link buff to equipment
                $stmt = $pdo->prepare("
                    INSERT INTO equipment_buffs (equipment_id, buff_type, value)
                    VALUES (?, 'Buff', ?)
                ");
                $stmt->execute([$equipment_id, $buff_id]);
                break;
            } else {
                // Regular stat buff
                $available_stats = array_diff(['Firepower', 'Armour', 'Maneuver', 'Health'], $used_buff_types);
                
                // If all stats are used, break the loop
                if (empty($available_stats)) {
                    break;
                }
                
                // Select random available stat
                $stat_type = array_rand($available_stats);
                $buff_type = $available_stats[$stat_type];
                $points_to_use = mt_rand(1, $remaining_points);
                
                $value = ($buff_type === 'Health') ? $points_to_use * 10 : $points_to_use;
                
                $stmt = $pdo->prepare("
                    INSERT INTO equipment_buffs (equipment_id, buff_type, value)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$equipment_id, $buff_type, $value]);
                
                $used_buff_types[] = $buff_type;
                $remaining_points -= $points_to_use;
            }
        }
        
        // Fetch the complete equipment data for display
        $stmt = $pdo->prepare("
            SELECT e.*, eb.buff_type, eb.value, 
                   CASE 
                       WHEN eb.buff_type = 'Buff' THEN b.description 
                       ELSE NULL 
                   END as buff_description,
                   b.target
            FROM equipment e
            LEFT JOIN equipment_buffs eb ON e.equipment_id = eb.equipment_id
            LEFT JOIN buffs b ON eb.buff_type = 'Buff' AND eb.value = b.buff_id
            WHERE e.equipment_id = ?
        ");
        $stmt->execute([$equipment_id]);
        $equipment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format equipment data
        $equipment = [
            'equipment_id' => $equipment_id,
            'name' => $equipment_type,
            'rarity' => $selected_rarity,
            'type' => $equipment_type,
            'is_foil' => $is_foil,
            'buffs' => []
        ];
        
        // Track which buff types we've already added
        $added_buff_types = [];

        foreach ($equipment_data as $row) {
            if ($row['buff_type']) {
                // Skip if we've already added this buff type
                if (in_array($row['buff_type'], $added_buff_types)) {
                    continue;
                }
                
                $equipment['buffs'][] = [
                    'buff_type' => $row['buff_type'],
                    'value' => $row['value'],
                    'target' => $row['target'],
                    'description' => $row['buff_description']
                ];
                
                // Track that we've added this buff type
                $added_buff_types[] = $row['buff_type'];
            }
        }
        
        $generated_equipment[] = $equipment;
    }
    
    $pdo->commit();
    $stmt = $pdo->prepare("SELECT loot_token FROM commodities WHERE id = ?");
    $stmt->execute([$user_id]);
    $remaining_tokens = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'equipment' => $generated_equipment, 'remaining_tokens' => $remaining_tokens]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 