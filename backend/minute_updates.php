<?php
require_once 'db_connection.php';
require_once 'gp_functions.php';
require_once 'unit_config.php';

function performMinuteUpdates() {
    global $pdo;
    global $UNIT_CONFIG;

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Decrement minutes_left for all entries in factory_queue
        $stmt = $pdo->prepare("UPDATE factory_queue SET minutes_left = minutes_left - 1 WHERE minutes_left > 0");
        $stmt->execute();
        $affected_rows = $stmt->rowCount();
        log_message("Updated factory queue times. Affected rows: " . $affected_rows);

        // Decrement minutes_left for all entries in building_queue
        $stmt = $pdo->prepare("UPDATE building_queue SET minutes_left = minutes_left - 1 WHERE minutes_left > 0");
        $stmt->execute();
        $affected_rows = $stmt->rowCount();
        log_message("Updated building queue times. Affected rows: " . $affected_rows);

        // Decrement minutes_left for all entries in unit_queue
        $stmt = $pdo->prepare("UPDATE unit_queue SET minutes_left = minutes_left - 1 WHERE minutes_left > 0");
        $stmt->execute();
        $affected_rows = $stmt->rowCount();
        log_message("Updated unit queue times. Affected rows: " . $affected_rows);

        // Fetch completed factories
        $stmt_fetch = $pdo->prepare("SELECT id, factory_type, queue_position FROM factory_queue WHERE minutes_left <= 0");
        $stmt_fetch->execute();
        
        while ($row = $stmt_fetch->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $row['id'];
            $factory_type = $row['factory_type'];
            $queue_position = $row['queue_position'];

            // Add the completed factory to the user's factories
            $stmt_update = $pdo->prepare("UPDATE factories SET `$factory_type` = `$factory_type` + 1 WHERE id = ?");
            $stmt_update->execute([$user_id]);

            // Update GP - modified to use the function instead of class
            $new_gp = calculateTotalGP($pdo, $user_id)['total_gp'];
            $stmt_gp = $pdo->prepare("UPDATE users SET gp = ? WHERE id = ?");
            $stmt_gp->execute([$new_gp, $user_id]);

            // Delete the completed factory from the queue
            $stmt_delete = $pdo->prepare("DELETE FROM factory_queue WHERE id = ? AND factory_type = ? AND queue_position = ?");
            $stmt_delete->execute([$user_id, $factory_type, $queue_position]);

            log_message("Completed construction of $factory_type for user $user_id (queue position: $queue_position)");
        }

        // Update building queue
        $stmt = $pdo->prepare("SELECT id, building_type, level, minutes_left FROM building_queue WHERE minutes_left <= 0");
        $stmt->execute();

        while ($building_upgrade = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $building_upgrade['id'];
            $building_type = $building_upgrade['building_type'];
            $target_level = $building_upgrade['level'];

            // Upgrade is complete
            $stmt_update = $pdo->prepare("UPDATE buildings SET $building_type = ? WHERE id = ?");
            $stmt_update->execute([$target_level, $user_id]);

            // Remove from queue
            $stmt_delete = $pdo->prepare("DELETE FROM building_queue WHERE id = ? AND building_type = ?");
            $stmt_delete->execute([$user_id, $building_type]);

            log_message("Completed upgrade of $building_type to level $target_level for user $user_id");
        }

        // Update unit queue
        $stmt = $pdo->prepare("SELECT id, unit_type, minutes_left FROM unit_queue WHERE minutes_left <= 0");
        $stmt->execute();

        while ($unit = $stmt->fetch(PDO::FETCH_ASSOC)) {
            try {
                $user_id = $unit['id'];
                $unit_type = strtolower($unit['unit_type']);
                
                // Debug logging
                log_message("Processing unit: " . $unit_type);
                log_message("UNIT_CONFIG contents: " . var_export($UNIT_CONFIG, true));
                
                if (!isset($UNIT_CONFIG[$unit_type])) {
                    log_message("Error: Unit type '$unit_type' not found in config");
                    continue;
                }

                log_message("Unit name from config: " . $UNIT_CONFIG[$unit_type]['name']);
                log_message("Unit type from config: " . $UNIT_CONFIG[$unit_type]['type']);

                // Create the new unit
                $stmt_insert = $pdo->prepare("
                    INSERT INTO units (
                        player_id, name, custom_name, type, level, xp, division_id,
                        firepower, armour, maneuver, max_hp, hp,
                        equipment_1_id, equipment_2_id, equipment_3_id, equipment_4_id
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?
                    )
                ");

                $values = [
                    $user_id,                           // player_id
                    $UNIT_CONFIG[$unit_type]['name'],   // name
                    $UNIT_CONFIG[$unit_type]['name'],   // custom_name
                    $UNIT_CONFIG[$unit_type]['type'],   // type
                    1,                                  // level
                    0,                                  // xp
                    0,                                  // division_id
                    $UNIT_CONFIG[$unit_type]['firepower'], // firepower
                    $UNIT_CONFIG[$unit_type]['armour'],    // armour
                    $UNIT_CONFIG[$unit_type]['maneuver'],  // maneuver
                    $UNIT_CONFIG[$unit_type]['hp'],    // max_hp
                    $UNIT_CONFIG[$unit_type]['hp'],        // hp
                    0, 0, 0, 0                         // equipment slots
                ];

                log_message("Attempting to insert with values: " . implode(", ", $values));
                
                $stmt_insert->execute($values);
                log_message("Unit inserted successfully");

                // Get the last inserted unit ID
                $unit_id = $pdo->lastInsertId();

                // Create buffs for the unit if any exist
                if (!empty($UNIT_CONFIG[$unit_type]['buffs'])) {
                    $stmt_buff = $pdo->prepare("
                        INSERT INTO buffs (
                            unit_id, description, buff_type, value, target
                        ) VALUES (
                            ?, ?, ?, ?, ?
                        )
                    ");

                    foreach ($UNIT_CONFIG[$unit_type]['buffs'] as $buff) {
                        $buff_values = [
                            $unit_id,
                            $buff['description'],
                            $buff['buff_type'],
                            $buff['value'],
                            $buff['target']
                        ];
                        $stmt_buff->execute($buff_values);
                        log_message("Created buff for unit $unit_id: " . $buff['description']);
                    }
                }

                // Remove from queue
                $stmt_delete = $pdo->prepare("DELETE FROM unit_queue WHERE id = ? AND unit_type = ?");
                $stmt_delete->execute([$user_id, $unit['unit_type']]);
                log_message("Unit removed from queue");

            } catch (Exception $e) {
                log_message("Error processing unit: " . $e->getMessage());
            }
        }

        // Process ongoing battles
        $stmt = $pdo->prepare("SELECT battle_id FROM battles WHERE is_over = 0");
        $stmt->execute();
        $ongoing_battles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $battles_processed = 0;
        $battles_failed = 0;

        foreach ($ongoing_battles as $battle) {
            log_message("Processing battle ID: " . $battle['battle_id']);
            
            try {
                require_once 'process_battle.php';
                $result = process_battle($battle['battle_id']);
                
                if ($result) {
                    $battles_processed++;
                    log_message("Successfully processed battle " . $battle['battle_id']);
                } else {
                    $battles_failed++;
                    // Get the last error from the error log
                    $error = error_get_last();
                    log_message("Failed to process battle " . $battle['battle_id'] . 
                              ". Error: " . ($error ? $error['message'] : 'Unknown error'));
                }
            } catch (Exception $e) {
                $battles_failed++;
                log_message("Exception processing battle " . $battle['battle_id'] . 
                          ". Error: " . $e->getMessage());
            }
        }
        
        log_message("Battle processing complete. Processed: $battles_processed, Failed: $battles_failed");

        $pdo->commit();
        log_message("Minute updates completed successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("Error during minute updates: " . $e->getMessage());
    }
}

function log_message($message) {
    /*
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n"; 
    
    $log_file = __DIR__ . '/minute_updates.log';
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);*/
}

function main() {
    log_message("Starting minute updates");
    performMinuteUpdates();
    log_message("Minute updates completed");
}

main();
