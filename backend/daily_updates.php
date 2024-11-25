<?php
require_once 'db_connection.php';
require_once 'mission_config.php';
require_once 'unit_config.php';
log_message("Mission config loaded: " . (is_array($MISSION_CONFIG) ? count($MISSION_CONFIG) . " missions" : "Failed to load"));
log_message("Mission config contents: " . print_r($MISSION_CONFIG, true));

function performDailyUpdates() {
    global $pdo, $MISSION_CONFIG, $UNIT_CONFIG;
    
    // Add a debug log to check if UNIT_CONFIG is loaded
    log_message("UNIT_CONFIG loaded: " . (is_array($UNIT_CONFIG) ? count($UNIT_CONFIG) . " units" : "Failed to load"));
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Reset expanded_borders_today for all users
        $stmt = $pdo->prepare("UPDATE land SET expanded_borders_today = 0");
        $stmt->execute();
        
        $affected_rows = $stmt->rowCount();
        log_message("Reset expanded_borders_today for {$affected_rows} users");

        // Clean up orphaned buffs
        $stmt = $pdo->prepare("
            DELETE b FROM buffs b
            LEFT JOIN units u ON b.unit_id = u.unit_id
            WHERE u.unit_id IS NULL
        ");
        $stmt->execute();
        
        $deleted_buffs = $stmt->rowCount();
        log_message("Cleaned up {$deleted_buffs} orphaned buffs");

        // Clean up completed battles and their combat reports
        $stmt = $pdo->prepare("
            DELETE cr FROM combat_reports cr
            INNER JOIN battles b ON cr.battle_id = b.battle_id
            WHERE b.is_over = 1
        ");
        $stmt->execute();
        $deleted_reports = $stmt->rowCount();
        
        $stmt = $pdo->prepare("DELETE FROM battles WHERE is_over = 1");
        $stmt->execute();
        $deleted_battles = $stmt->rowCount();
        
        log_message("Cleaned up {$deleted_battles} completed battles and {$deleted_reports} combat reports");

        // Clean up unassigned units (division_id = -1)
        // First delete their buffs (foreign key constraint)
        $stmt = $pdo->prepare("
            DELETE b FROM buffs b
            INNER JOIN units u ON b.unit_id = u.unit_id
            WHERE u.division_id = -1
        ");
        $stmt->execute();
        $deleted_unit_buffs = $stmt->rowCount();

        // Then delete the units
        $stmt = $pdo->prepare("DELETE FROM units WHERE division_id = -1");
        $stmt->execute();
        $deleted_units = $stmt->rowCount();
        
        log_message("Cleaned up {$deleted_units} unassigned units and {$deleted_unit_buffs} associated buffs");

        // Clean up inactive NPC divisions and their units
        // First delete buffs of units in these divisions
        $stmt = $pdo->prepare("
            DELETE b FROM buffs b
            INNER JOIN units u ON b.unit_id = u.unit_id
            INNER JOIN divisions d ON u.division_id = d.division_id
            WHERE d.user_id = 0 AND d.in_combat = 0
        ");
        $stmt->execute();
        $deleted_npc_buffs = $stmt->rowCount();

        // Then delete the units in these divisions
        $stmt = $pdo->prepare("
            DELETE u FROM units u
            INNER JOIN divisions d ON u.division_id = d.division_id
            WHERE d.user_id = 0 AND d.in_combat = 0
        ");
        $stmt->execute();
        $deleted_npc_units = $stmt->rowCount();

        // Finally delete the NPC divisions themselves
        $stmt = $pdo->prepare("
            DELETE FROM divisions 
            WHERE user_id = 0 AND in_combat = 0
        ");
        $stmt->execute();
        $deleted_npc_divisions = $stmt->rowCount();
        
        log_message("Cleaned up {$deleted_npc_divisions} inactive NPC divisions with {$deleted_npc_units} units and {$deleted_npc_buffs} buffs");

        // Update daily unit
        log_message("Updating daily unit selection");
        try {
            // Get all unit types
            $unit_types = array_keys($UNIT_CONFIG);

            if (!empty($unit_types)) {
                // Select random unit type
                $random_unit = $unit_types[array_rand($unit_types)];
                
                // Update or insert the daily unit
                $stmt = $pdo->prepare("
                    INSERT INTO daily_unit (id, unit_type) 
                    VALUES (1, ?) 
                    ON DUPLICATE KEY UPDATE unit_type = ?
                ");
                $stmt->execute([$random_unit, $random_unit]);
                
                log_message("Updated daily unit to: " . $random_unit);
            } else {
                log_message("No units found in config");
            }
        } catch (Exception $e) {
            log_message("Error updating daily unit: " . $e->getMessage());
            // Continue with other updates even if this fails
        }

        // Refresh incomplete missions
        log_message("Starting mission refresh process");
        
        try {
            // Get missions to refresh
            $stmt = $pdo->prepare("
                SELECT m.user_id, m.mission_id, m.mission_type 
                FROM missions m 
                WHERE m.status = 'incomplete' AND m.battle_id IS NULL
            ");
            $stmt->execute();
            $missions_to_refresh = $stmt->fetchAll(PDO::FETCH_ASSOC);
            log_message("Found " . count($missions_to_refresh) . " missions to refresh");

            foreach ($missions_to_refresh as $mission) {
                try {
                    log_message("Processing mission ID {$mission['mission_id']} for user {$mission['user_id']}");
                    
                    // Get user's other missions
                    $stmt = $pdo->prepare("
                        SELECT mission_type 
                        FROM missions 
                        WHERE user_id = ? 
                        AND mission_id != ?
                    ");
                    $stmt->execute([$mission['user_id'], $mission['mission_id']]);
                    $existing_missions = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    log_message("User has " . count($existing_missions) . " other missions");
                    
                    // Generate new random mission
                    $total_weight = 0;
                    $available_missions = [];

                    // Build available missions array
                    foreach ($MISSION_CONFIG as $type => $config) {
                        if (!in_array($type, $existing_missions)) {
                            $available_missions[$type] = $config;
                        }
                    }

                    log_message("Available missions: " . implode(", ", array_keys($available_missions)));
                    
                    if (empty($available_missions)) {
                        log_message("Warning: No available missions after filtering, using all missions");
                        $available_missions = $MISSION_CONFIG;
                    }
                    
                    foreach ($available_missions as $type => $config) {
                        if (!isset($config['spawn_weight'])) {
                            log_message("Warning: Missing spawn_weight for mission type: " . $type);
                            continue;
                        }
                        $total_weight += (int)$config['spawn_weight'];
                    }
                    
                    if ($total_weight <= 0) {
                        throw new Exception("Invalid total weight: " . $total_weight);
                    }

                    log_message("Total weight calculated: " . $total_weight);
                    $random = rand(1, $total_weight);
                    $current_weight = 0;
                    $new_mission_type = '';

                    foreach ($available_missions as $type => $config) {
                        $current_weight += $config['spawn_weight'];
                        if ($random <= $current_weight) {
                            $new_mission_type = $type;
                            break;
                        }
                    }

                    if (!$new_mission_type || !isset($MISSION_CONFIG[$new_mission_type])) {
                        log_message("Warning: Invalid mission selected, using fallback");
                        $mission_types = array_keys($MISSION_CONFIG);
                        $new_mission_type = $mission_types[array_rand($mission_types)];
                    }

                    log_message("Selected new mission type: " . $new_mission_type);

                    // Update mission with new type
                    $stmt = $pdo->prepare("
                        UPDATE missions 
                        SET mission_type = ?, 
                            status = 'incomplete', 
                            battle_id = NULL,
                            rewards_claimed = FALSE
                        WHERE mission_id = ?
                    ");
                    $stmt->execute([$new_mission_type, $mission['mission_id']]);
                    log_message("Successfully updated mission {$mission['mission_id']} to {$new_mission_type}");

                } catch (Exception $e) {
                    log_message("Error processing mission {$mission['mission_id']}: " . $e->getMessage());
                    // Continue with next mission instead of breaking the whole process
                    continue;
                }
            }

            log_message("Mission refresh process completed successfully");
        } catch (Exception $e) {
            log_message("Fatal error in mission refresh process: " . $e->getMessage());
            throw $e;
        }

        // Commit the single transaction at the end
        $pdo->commit();
        log_message("Daily updates completed successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("Error during daily updates: " . $e->getMessage());
    }
}

function log_message($message) {
    /*
    $log_file = __DIR__ . '/daily_updates.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);*/
}

log_message("Starting daily updates");
performDailyUpdates();
log_message("Daily updates completed");