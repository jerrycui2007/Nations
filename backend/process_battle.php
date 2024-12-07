<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/battle_process.log');

require_once 'db_connection.php';
require_once 'unit_config.php';
require_once 'mission_config.php';

// Define log directory and file paths
define('LOG_DIR', __DIR__ . '/logs');
define('BATTLE_LOG_FILE', LOG_DIR . '/battle_process.log');

function log_battle_message($message, $level = 'INFO', $context = []) {
    try {
        // Create logs directory if it doesn't exist
        if (!file_exists(LOG_DIR)) {
            mkdir(LOG_DIR, 0755, true);
        }

        // Format the log entry
        $timestamp = date('Y-m-d H:i:s');
        $context_str = empty($context) ? '' : ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $log_entry = sprintf("[%s][%s] %s%s\n", $timestamp, strtoupper($level), $message, $context_str);

        // Write to file with exclusive lock
        if (file_put_contents(BATTLE_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX) === false) {
            error_log("Failed to write to battle log file: " . BATTLE_LOG_FILE);
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log("Exception in log_battle_message: " . $e->getMessage());
        return false;
    }
}

function append_to_battle_report($battle_id, $html_content) {
    global $pdo, $MISSION_CONFIG;
    $stmt = $pdo->prepare("
        UPDATE battle_reports 
        SET message = CONCAT(COALESCE(message, ''), ?)
        WHERE battle_id = ?
    ");
    $stmt->execute([$html_content, $battle_id]);
}

function process_level_ups($defender_division_id, $attacker_division_id) {
    global $pdo, $LEVEL_CONFIG, $MISSION_CONFIG;
    
    $level_up_messages = [];
    
    // Get all units that might level up
    $stmt = $pdo->prepare("
        SELECT unit_id, level, xp, custom_name, name 
        FROM units 
        WHERE division_id IN (?, ?) 
        AND hp > 0
    ");
    $stmt->execute([$defender_division_id, $attacker_division_id]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($units as $unit) {
        $current_level = $unit['level'];
        $next_level = $current_level + 1;
        $unit_name = $unit['custom_name'] ?: $unit['name'];
        
        // Skip if already at max level
        if ($current_level >= 15) {
            continue;
        }
        
        // Check if unit has enough XP for the next level
        if ($unit['xp'] >= $LEVEL_CONFIG[$next_level]) {
            // Randomly choose which stat to increase (1-4)
            $stat_increase = rand(1, 4);
            $stat_name = '';
            
            switch ($stat_increase) {
                case 1:
                    $sql = "UPDATE units SET level = ?, firepower = firepower + 1, xp = 0 WHERE unit_id = ?";
                    $stat_name = "Firepower";
                    break;
                case 2:
                    $sql = "UPDATE units SET level = ?, armour = armour + 1, xp = 0 WHERE unit_id = ?";
                    $stat_name = "Armour";
                    break;
                case 3:
                    $sql = "UPDATE units SET level = ?, maneuver = maneuver + 1, xp = 0 WHERE unit_id = ?";
                    $stat_name = "Maneuver";
                    break;
                case 4:
                    $sql = "UPDATE units SET level = ?, max_hp = FLOOR(max_hp * 1.1), hp = FLOOR(hp * 1.1), xp = 0 WHERE unit_id = ?";
                    $stat_name = "HP";
                    break;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$next_level, $unit['unit_id']]);
            
            $level_up_messages[] = sprintf(
                "<li>%s leveled up to level %d! (%s +1)</li>",
                htmlspecialchars($unit_name),
                $next_level,
                $stat_name
            );
        }
    }
    
    return $level_up_messages;
}

function process_battle($battle_id) {
    global $pdo, $MISSION_CONFIG;

    log_battle_message("Starting battle processing", 'INFO', ['battle_id' => $battle_id]);
    log_battle_message("Mission config check", 'DEBUG', [
        'mission_config_exists' => isset($MISSION_CONFIG),
        'mission_config_value' => $MISSION_CONFIG
    ]);

    $COMBAT_REPORT_TEMPLATES = [
        "Infantry" => [
            "Infantry" => [
                "critical" => [
                    "%s (%s) caught %s (%s) in a flanking maneuver and opened fire, dealing %s damage.",
                    "%s (%s) executed a perfect ambush on %s (%s), inflicting %s damage.",
                    "%s (%s) found a weak spot in %s's (%s) formation and struck hard for %s damage.",
                    "In a brilliant tactical move, %s (%s) outmaneuvered %s (%s) and dealt %s damage."
                ],
                "regular" => [
                    "%s (%s) fired at %s (%s), dealing %s damage.",
                    "%s (%s) engaged %s (%s) in combat, inflicting %s damage.",
                    "%s (%s) exchanged fire with %s (%s), causing %s damage.",
                    "%s (%s) attacked %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) fired at %s (%s) and missed, but shrapnel did %s damage.",
                    "%s (%s) barely grazed %s (%s), causing %s damage.",
                    "%s (%s) made glancing contact with %s (%s), dealing %s damage.",
                    "A stray shot from %s (%s) caught %s (%s), doing %s damage."
                ],
                "miss" => [
                    "%s (%s) fired at %s (%s) and missed.",
                    "%s (%s) attempted to engage %s (%s) but failed to connect.",
                    "%s (%s) lost sight of %s (%s) and missed their shot.",
                    "The terrain prevented %s (%s) from getting a clean shot at %s (%s)."
                ]
            ],
            "Armour" => [
                "critical" => [
                    "%s (%s) found a weak spot in %s's (%s) armor plating, dealing %s damage with anti-tank weapons.",
                    "%s (%s) coordinated a perfect AT ambush against %s (%s), inflicting %s damage.",
                    "%s (%s) disabled %s's (%s) tracks with precise fire, dealing %s damage.",
                    "Using advanced AT weaponry, %s (%s) penetrated %s's (%s) armor for %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with AT rockets, dealing %s damage.",
                    "%s (%s) launched coordinated AT fire at %s (%s), causing %s damage.",
                    "%s (%s) attacked %s (%s) from cover, dealing %s damage.",
                    "%s (%s) hit %s (%s) with sustained AT fire, inflicting %s damage."
                ],
                "graze" => [
                    "%s (%s) barely penetrated %s's (%s) armor, dealing %s damage.",
                    "%s (%s) hit %s's (%s) auxiliary systems for %s damage.",
                    "%s (%s) damaged %s's (%s) external equipment for %s damage.",
                    "A glancing blow from %s (%s) scratched %s's (%s) armor, causing %s damage."
                ],
                "miss" => [
                    "%s (%s) AT rounds bounced off %s's (%s) armor.",
                    "%s (%s) failed to penetrate %s's (%s) thick plating.",
                    "%s (%s) couldn't find a good angle on %s (%s).",
                    "%s (%s) AT weapons were ineffective against %s's (%s) armor."
                ]
            ],
            "Special Forces" => [
                "critical" => [
                    "%s (%s) caught %s (%s) in a devastating crossfire, dealing %s damage.",
                    "%s (%s) executed a perfect fire-and-move against %s (%s), inflicting %s damage.",
                    "%s (%s) exploited a tactical weakness in %s's (%s) position for %s damage.",
                    "%s (%s) outmaneuvered and overwhelmed %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) in close combat, dealing %s damage.",
                    "%s (%s) exchanged fire with %s (%s), inflicting %s damage.",
                    "%s (%s) attacked %s (%s) from multiple angles, causing %s damage.",
                    "%s (%s) pressed the attack against %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) grazed %s (%s) with suppressing fire, dealing %s damage.",
                    "%s (%s) forced %s (%s) to take cover, causing %s damage.",
                    "%s (%s) disrupted %s's (%s) movement, dealing %s damage.",
                    "%s (%s) harassed %s (%s) with covering fire, inflicting %s damage."
                ],
                "miss" => [
                    "%s (%s) lost track of %s (%s) in the chaos.",
                    "%s (%s) failed to get a clean shot at %s (%s).",
                    "%s (%s) was forced to take cover by %s (%s).",
                    "%s (%s) couldn't maintain sight of %s (%s)."
                ]
            ],
            "Air" => [
                "critical" => [
                    "%s (%s) caught %s (%s) in a devastating anti-air barrage, dealing %s damage.",
                    "%s (%s) coordinated concentrated AA fire on %s (%s), inflicting %s damage.",
                    "%s (%s) exploited %s's (%s) low altitude approach for %s damage.",
                    "%s (%s) executed perfect anti-air tactics against %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with AA weapons, dealing %s damage.",
                    "%s (%s) maintained suppressing fire on %s (%s), causing %s damage.",
                    "%s (%s) fired at %s's (%s) exposed approach, inflicting %s damage.",
                    "%s (%s) coordinated AA fire against %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) scattered AA fire near %s (%s), dealing %s damage.",
                    "%s (%s) forced %s (%s) to break off their attack run, causing %s damage.",
                    "%s (%s) harassed %s (%s) with covering fire, inflicting %s damage.",
                    "%s (%s) disrupted %s's (%s) approach, dealing %s damage."
                ],
                "miss" => [
                    "%s (%s) couldn't track %s (%s) effectively.",
                    "%s (%s) failed to lead %s (%s) properly.",
                    "%s (%s) lost sight of %s (%s) in the clouds.",
                    "%s (%s) AA fire fell short of %s (%s)."
                ]
            ],
            "Static" => [
                "critical" => [
                    "%s (%s) breached %s's (%s) defenses with explosives, dealing %s damage.",
                    "%s (%s) found a weak point in %s's (%s) fortifications, inflicting %s damage.",
                    "%s (%s) executed a perfect assault on %s's (%s) position for %s damage.",
                    "%s (%s) overwhelmed %s's (%s) defenses, dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with sustained fire, dealing %s damage.",
                    "%s (%s) pressed the attack against %s (%s), causing %s damage.",
                    "%s (%s) assaulted %s's (%s) position, inflicting %s damage.",
                    "%s (%s) maintained pressure on %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) caused minor damage to %s's (%s) defenses, dealing %s damage.",
                    "%s (%s) harassed %s's (%s) position, causing %s damage.",
                    "%s (%s) probed %s's (%s) defensive line, inflicting %s damage.",
                    "%s (%s) tested %s's (%s) fortifications, dealing %s damage."
                ],
                "miss" => [
                    "%s (%s) failed to breach %s's (%s) defenses.",
                    "%s (%s) attack was repelled by %s (%s).",
                    "%s (%s) couldn't find a weakness in %s's (%s) position.",
                    "%s (%s) assault was stopped by %s's (%s) fortifications."
                ]
            ]
        ],
        "Armour" => [
            "Infantry" => [
                "critical" => [
                    "%s (%s) unleashed devastating cannon fire on %s (%s), dealing %s damage.",
                    "%s (%s) rolled through %s's (%s) position, inflicting %s damage.",
                    "%s (%s) decimated %s's (%s) cover with HE rounds for %s damage.",
                    "%s (%s) crushed %s's (%s) defensive line, dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) fired HE shells at %s (%s), dealing %s damage.",
                    "%s (%s) suppressed %s (%s) with machine gun fire for %s damage.",
                    "%s (%s) bombarded %s's (%s) position, causing %s damage.",
                    "%s (%s) advanced on %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) scattered %s (%s) with near misses, dealing %s damage.",
                    "%s (%s) forced %s (%s) to relocate, causing %s damage.",
                    "%s (%s) suppressed %s (%s) with machine gun fire for %s damage.",
                    "Shrapnel from %s's (%s) near miss hit %s (%s) for %s damage."
                ],
                "miss" => [
                    "%s (%s) overshot %s's (%s) position.",
                    "%s (%s) lost sight of %s (%s) in the terrain.",
                    "%s (%s) failed to track %s's (%s) movement.",
                    "%s (%s) couldn't bring weapons to bear on %s (%s)."
                ]
            ],
            "Armour" => [
                "critical" => [
                    "%s (%s) penetrated %s's (%s) frontal armor, dealing %s damage.",
                    "%s (%s) found a weak spot in %s's (%s) armor, inflicting %s damage.",
                    "%s (%s) disabled %s's (%s) critical systems for %s damage.",
                    "%s (%s) executed a perfect flanking shot on %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) exchanged fire with %s (%s), dealing %s damage.",
                    "%s (%s) engaged %s (%s) at medium range, causing %s damage.",
                    "%s (%s) hit %s (%s) with AP rounds, inflicting %s damage.",
                    "%s (%s) scored a direct hit on %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) glanced off %s's (%s) armor, dealing %s damage.",
                    "%s (%s) damaged %s's (%s) external equipment for %s damage.",
                    "%s (%s) hit %s's (%s) tracks, causing %s damage.",
                    "%s (%s) struck %s's (%s) secondary systems for %s damage."
                ],
                "miss" => [
                    "%s (%s) shell bounced off %s's (%s) armor.",
                    "%s (%s) failed to penetrate %s's (%s) plating.",
                    "%s (%s) shot wide of %s (%s).",
                    "%s (%s) miscalculated the range to %s (%s)."
                ]
            ],
            "Special Forces" => [
                "critical" => [
                    "%s (%s) caught %s (%s) in the open, dealing %s damage.",
                    "%s (%s) overwhelmed %s's (%s) position with superior firepower for %s damage.",
                    "%s (%s) crushed through %s's (%s) defenses, inflicting %s damage.",
                    "%s (%s) dominated the engagement with %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with combined arms fire, dealing %s damage.",
                    "%s (%s) pressed the attack against %s (%s), causing %s damage.",
                    "%s (%s) suppressed %s (%s) with heavy fire, inflicting %s damage.",
                    "%s (%s) advanced on %s's (%s) position, dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) forced %s (%s) to relocate, dealing %s damage.",
                    "%s (%s) disrupted %s's (%s) operation for %s damage.",
                    "%s (%s) scattered %s (%s) with suppressing fire, causing %s damage.",
                    "%s (%s) kept %s (%s) pinned down, inflicting %s damage."
                ],
                "miss" => [
                    "%s (%s) lost track of %s (%s) in the confusion.",
                    "%s (%s) couldn't acquire %s (%s) through their stealth.",
                    "%s (%s) was outmaneuvered by %s (%s).",
                    "%s (%s) failed to corner %s (%s)."
                ]
            ]
        ],
        "Special Forces" => [
            "Infantry" => [
                "critical" => [
                    "%s (%s) executed a perfect ambushed on %s (%s), dealing %s damage.",
                    "%s (%s) infiltrated and devastated %s's (%s) position for %s damage.",
                    "%s (%s) caught %s (%s) completely off guard, inflicting %s damage.",
                    "%s (%s) dominated %s (%s) with superior tactics, dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with precision fire, dealing %s damage.",
                    "%s (%s) coordinated an attack on %s (%s), causing %s damage.",
                    "%s (%s) struck %s (%s) from an unexpected angle for %s damage.",
                    "%s (%s) outmaneuvered %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) harassed %s (%s) with covering fire, dealing %s damage.",
                    "%s (%s) disrupted %s's (%s) formation for %s damage.",
                    "%s (%s) forced %s (%s) to take cover, causing %s damage.",
                    "%s (%s) kept %s (%s) suppressed, inflicting %s damage."
                ],
                "miss" => [
                    "%s (%s) lost the element of surprise against %s (%s).",
                    "%s (%s) was spotted before engaging %s (%s).",
                    "%s (%s) had to abort their approach to %s (%s).",
                    "%s (%s) couldn't find an opening against %s (%s)."
                ]
            ],
            "Armour" => [
                "critical" => [
                    "%s (%s) planted explosives on %s's (%s) weak points, dealing %s damage.",
                    "%s (%s) executed a perfect anti-tank operation on %s (%s), inflicting %s damage.",
                    "%s (%s) disabled %s's (%s) critical systems for %s damage.",
                    "%s (%s) infiltrated and sabotaged %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with specialized AT weapons, dealing %s damage.",
                    "%s (%s) coordinated an attack on %s (%s), causing %s damage.",
                    "%s (%s) struck %s's (%s) vulnerable points for %s damage.",
                    "%s (%s) executed hit-and-run tactics against %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) damaged %s's (%s) external systems for %s damage.",
                    "%s (%s) harassed %s (%s) with light AT weapons, causing %s damage.",
                    "%s (%s) temporarily disabled %s's (%s) sensors, dealing %s damage.",
                    "%s (%s) forced %s (%s) to button up, inflicting %s damage."
                ],
                "miss" => [
                    "%s (%s) failed to find a weakness in %s's (%s) armor.",
                    "%s (%s) couldn't get close enough to %s (%s).",
                    "%s (%s)'s AT weapons were ineffective against %s (%s).",
                    "%s (%s) had to withdraw from %s (%s)."
                ]
            ],
            "Special Forces" => [
                "critical" => [
                    "%s (%s) outmaneuvered and overwhelmed %s (%s), dealing %s damage.",
                    "%s (%s) executed a perfect counter-spec ops move against %s (%s), inflicting %s damage.",
                    "%s (%s) dominated the close-quarters fight with %s (%s) for %s damage.",
                    "%s (%s) caught %s (%s) in a devastating ambush, dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) in a tactical firefight, dealing %s damage.",
                    "%s (%s) traded precision fire with %s (%s), causing %s damage.",
                    "%s (%s) coordinated an attack on %s (%s), inflicting %s damage.",
                    "%s (%s) pressed the advantage against %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) forced %s (%s) to change position, dealing %s damage.",
                    "%s (%s) disrupted %s's (%s) operation for %s damage.",
                    "%s (%s) maintained pressure on %s (%s), causing %s damage.",
                    "%s (%s) kept %s (%s) off balance, inflicting %s damage."
                ],
                "miss" => [
                    "%s (%s) and %s (%s) stalemated in their engagement.",
                    "%s (%s) lost track of %s (%s) in the chaos.",
                    "%s (%s) was countered by %s's (%s) defensive moves.",
                    "%s (%s) couldn't find an opening against %s (%s)."
                ]
            ]
        ],
        "Air" => [
            "Infantry" => [
                "critical" => [
                    "%s (%s) caught %s (%s) during a low-altitude pass, dealing %s damage.",
                    "%s (%s) coordinated concentrated fire on %s (%s), inflicting %s damage.",
                    "%s (%s) exploited %s's (%s) strafing run for %s damage.",
                    "%s (%s) executed perfect anti-air tactics against %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with small arms fire, dealing %s damage.",
                    "%s (%s) maintained suppressing fire on %s (%s), causing %s damage.",
                    "%s (%s) fired at %s's (%s) exposed approach, inflicting %s damage.",
                    "%s (%s) coordinated volley fire against %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) scattered shots near %s (%s), dealing %s damage.",
                    "%s (%s) forced %s (%s) to break off their attack run, causing %s damage.",
                    "%s (%s) harassed %s (%s) with covering fire, inflicting %s damage.",
                    "%s (%s) disrupted %s's (%s) approach, dealing %s damage."
                ],
                "miss" => [
                    "%s (%s) couldn't track %s (%s) effectively.",
                    "%s (%s) failed to lead %s (%s) properly.",
                    "%s (%s) lost sight of %s (%s) in the clouds.",
                    "%s (%s) small arms fire fell short of %s (%s)."
                ]
            ],
            "Armour" => [
                "critical" => [
                    "%s (%s) caught %s (%s) with concentrated anti-air fire, dealing %s damage.",
                    "%s (%s) tracked and struck %s (%s) with precision, inflicting %s damage.",
                    "%s (%s) unleashed a barrage of AA fire on %s (%s) for %s damage.",
                    "%s (%s) executed a perfect anti-air defense against %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with AA guns, dealing %s damage.",
                    "%s (%s) fired at %s (%s) with mounted weapons, causing %s damage.",
                    "%s (%s) tracked %s (%s) with sustained fire, inflicting %s damage.",
                    "%s (%s) maintained AA fire on %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) forced %s (%s) to break off their attack, dealing %s damage.",
                    "%s (%s) scattered fire near %s (%s), causing %s damage.",
                    "%s (%s) grazed %s's (%s) fuselage, inflicting %s damage.",
                    "%s (%s) disrupted %s's (%s) approach, dealing %s damage."
                ],
                "miss" => [
                    "%s (%s) failed to track %s (%s) effectively.",
                    "%s (%s) AA fire missed %s (%s) completely.",
                    "%s (%s) couldn't maintain a lock on %s (%s).",
                    "%s (%s) shots were evaded by %s (%s)."
                ]
            ],
            "Air" => [
                "critical" => [
                    "%s (%s) outmaneuvered and shot down %s (%s), dealing %s damage.",
                    "%s (%s) dominated the dogfight with %s (%s), inflicting %s damage.",
                    "%s (%s) caught %s (%s) in a perfect firing position for %s damage.",
                    "%s (%s) executed a textbook air combat maneuver against %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) in a dogfight, dealing %s damage.",
                    "%s (%s) exchanged fire with %s (%s) mid-air, causing %s damage.",
                    "%s (%s) pursued and struck %s (%s), inflicting %s damage.",
                    "%s (%s) scored hits on %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) clipped %s's (%s) wing, dealing %s damage.",
                    "%s (%s) grazed %s (%s) with machine gun fire, causing %s damage.",
                    "%s (%s) landed glancing hits on %s (%s), inflicting %s damage.",
                    "%s (%s) forced %s (%s) to evade, dealing %s damage."
                ],
                "miss" => [
                    "%s (%s) lost sight of %s (%s) in the clouds.",
                    "%s (%s) was outmaneuvered by %s (%s).",
                    "%s (%s) couldn't get a firing solution on %s (%s).",
                    "%s (%s) missiles failed to track %s (%s)."
                ]
            ],
            "Static" => [
                "critical" => [
                    "%s (%s) breached %s's (%s) defensive line with explosives, dealing %s damage.",
                    "%s (%s) found a weak point in %s's (%s) fortifications, inflicting %s damage.",
                    "%s (%s) executed a perfect assault on %s's (%s) position for %s damage.",
                    "%s (%s) overwhelmed %s's (%s) defenses, dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with sustained fire, dealing %s damage.",
                    "%s (%s) pressed the attack against %s (%s), causing %s damage.",
                    "%s (%s) assaulted %s's (%s) position, inflicting %s damage.",
                    "%s (%s) maintained pressure on %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) caused minor damage to %s's (%s) defenses, dealing %s damage.",
                    "%s (%s) harassed %s's (%s) position, causing %s damage.",
                    "%s (%s) probed %s's (%s) defensive line, inflicting %s damage.",
                    "%s (%s) tested %s's (%s) fortifications, dealing %s damage."
                ],
                "miss" => [
                    "%s (%s) failed to breach %s's (%s) defenses.",
                    "%s (%s) attack was repelled by %s (%s).",
                    "%s (%s) couldn't find a weakness in %s's (%s) position.",
                    "%s (%s) assault was stopped by %s's (%s) fortifications."
                ]
            ]
        ],
        "Static" => [
            "Infantry" => [
                "critical" => [
                    "%s (%s) caught %s (%s) in devastating crossfire, dealing %s damage.",
                    "%s (%s) unleashed concentrated fire on %s (%s), inflicting %s damage.",
                    "%s (%s) pinned down and eliminated %s (%s) for %s damage.",
                    "%s (%s) dominated the battlefield against %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with sustained fire, dealing %s damage.",
                    "%s (%s) suppressed %s's (%s) advance, causing %s damage.",
                    "%s (%s) defended their position against %s (%s), inflicting %s damage.",
                    "%s (%s) maintained fire on %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) forced %s (%s) to take cover, dealing %s damage.",
                    "%s (%s) scattered %s's (%s) formation, causing %s damage.",
                    "%s (%s) suppressed %s (%s) with covering fire for %s damage.",
                    "%s (%s) harassed %s's (%s) movement, inflicting %s damage."
                ],
                "miss" => [
                    "%s (%s) failed to hit %s (%s) through the smoke.",
                    "%s (%s) lost sight of %s (%s) in the chaos.",
                    "%s (%s) couldn't track %s's (%s) movement.",
                    "%s (%s) fire was ineffective against %s (%s)."
                ]
            ],
            "Armour" => [
                "critical" => [
                    "%s (%s) penetrated %s's (%s) weak point, dealing %s damage.",
                    "%s (%s) disabled %s's (%s) critical systems, inflicting %s damage.",
                    "%s (%s) caught %s (%s) in a perfect killzone for %s damage.",
                    "%s (%s) executed a perfect anti-tank defense against %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with anti-tank fire, dealing %s damage.",
                    "%s (%s) defended against %s's (%s) advance, causing %s damage.",
                    "%s (%s) maintained AT fire on %s (%s), inflicting %s damage.",
                    "%s (%s) struck %s (%s) from prepared positions, dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) scored a glancing hit on %s (%s), dealing %s damage.",
                    "%s (%s) damaged %s's (%s) external systems for %s damage.",
                    "%s (%s) partially penetrated %s's (%s) armor, causing %s damage.",
                    "%s (%s) forced %s (%s) to adjust course, inflicting %s damage."
                ],
                "miss" => [
                    "%s (%s) rounds deflected off %s's (%s) armor.",
                    "%s (%s) failed to penetrate %s's (%s) plating.",
                    "%s (%s) couldn't find a vulnerability in %s's (%s).",
                    "%s (%s) fire was ineffective against %s's (%s) armor."
                ]
            ],
            "Air" => [
                "critical" => [
                    "%s (%s) caught %s (%s) in concentrated AA fire, dealing %s damage.",
                    "%s (%s) tracked and struck %s (%s) perfectly, inflicting %s damage.",
                    "%s (%s) shredded %s's (%s) airframe for %s damage.",
                    "%s (%s) executed perfect anti-air defense against %s (%s), dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) engaged %s (%s) with anti-air fire, dealing %s damage.",
                    "%s (%s) maintained AA fire on %s (%s), causing %s damage.",
                    "%s (%s) tracked %s (%s) through the sky, inflicting %s damage.",
                    "%s (%s) defended airspace against %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) clipped %s's (%s) wing, dealing %s damage.",
                    "%s (%s) forced %s (%s) to take evasive action, causing %s damage.",
                    "%s (%s) scattered flak near %s (%s), inflicting %s damage.",
                    "%s (%s) disrupted %s's (%s) attack run, dealing %s damage."
                ],
                "miss" => [
                    "%s (%s) failed to track %s (%s) effectively.",
                    "%s (%s) AA fire missed %s (%s) completely.",
                    "%s (%s) couldn't maintain a lock on %s (%s).",
                    "%s (%s) flak was avoided by %s (%s)."
                ]
            ],
            "Static" => [
                "critical" => [
                    "%s (%s) found a critical weakness in %s's (%s) position, dealing %s damage.",
                    "%s (%s) concentrated fire on %s's (%s) vulnerable point, inflicting %s damage.",
                    "%s (%s) breached %s's (%s) defenses for %s damage.",
                    "%s (%s) demolished %s's (%s) fortifications, dealing %s damage."
                ],
                "regular" => [
                    "%s (%s) exchanged fire with %s (%s), dealing %s damage.",
                    "%s (%s) engaged %s's (%s) position, causing %s damage.",
                    "%s (%s) bombarded %s's (%s) defenses, inflicting %s damage.",
                    "%s (%s) maintained pressure on %s (%s), dealing %s damage."
                ],
                "graze" => [
                    "%s (%s) scored glancing hits on %s (%s), dealing %s damage.",
                    "%s (%s) partially damaged %s's (%s) defenses, causing %s damage.",
                    "%s (%s) struck %s's (%s) outer works, inflicting %s damage.",
                    "%s (%s) made limited impact on %s (%s), dealing %s damage."
                ],
                "miss" => [
                    "%s (%s) fire was absorbed by %s's (%s) defenses.",
                    "%s (%s) failed to breach %s's (%s) fortifications.",
                    "%s (%s) couldn't find a weakness in %s's (%s) position.",
                    "%s (%s) attacks were ineffective against %s (%s)."
                ]
            ]
        ]
    ];
    
    try {
        // Get battle info and units
        $stmt = $pdo->prepare("
            SELECT * FROM battles WHERE battle_id = ?
        ");
        $stmt->execute([$battle_id]);
        $battle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$battle) {
            log_battle_message("Battle not found", 'ERROR', ['battle_id' => $battle_id]);
            throw new Exception("Battle not found");
        }
        
        // Check if initial strengths are recorded
        if ($battle['defender_initial_strength'] === null || $battle['attacker_initial_strength'] === null) {
            // Get all units from both sides (including dead ones for initial strength)
            $stmt = $pdo->prepare("
                SELECT * FROM units 
                WHERE division_id IN (?, ?)
            ");
            $stmt->execute([$battle['defender_division_id'], $battle['attacker_division_id']]);
            $initial_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate initial strengths
            $defender_strength = 0;
            $attacker_strength = 0;

            foreach ($initial_units as $unit) {
                $strength = $unit['firepower'] + $unit['armour'] + $unit['maneuver'] + floor($unit['hp'] / 10);
                if ($unit['division_id'] == $battle['defender_division_id']) {
                    $defender_strength += $strength;
                } else {
                    $attacker_strength += $strength;
                }
            }

            // Update battle with initial strengths
            $stmt = $pdo->prepare("
                UPDATE battles 
                SET defender_initial_strength = ?, attacker_initial_strength = ? 
                WHERE battle_id = ?
            ");
            $stmt->execute([$defender_strength, $attacker_strength, $battle_id]);
        }
        
        // Add structured debug logging
        log_battle_message("Processing battle", 'INFO', [
            'battle_id' => $battle_id,
            'defender_id' => $battle['defender_division_id'],
            'attacker_id' => $battle['attacker_division_id']
        ]);
        
        // Get all living units from both sides
        $stmt = $pdo->prepare("
            SELECT * FROM units 
            WHERE division_id IN (?, ?) 
            AND hp > 0
        ");
        $stmt->execute([$battle['defender_division_id'], $battle['attacker_division_id']]);
        $all_units = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log unit counts
        $unit_count = count($all_units);
        log_battle_message("Retrieved units", 'INFO', [
            'total_units' => $unit_count,
            'sql_error' => $stmt->errorInfo()
        ]);

        if ($unit_count === 0) {
            log_battle_message("No living units found", 'WARNING', [
                'defender_id' => $battle['defender_division_id'],
                'attacker_id' => $battle['attacker_division_id']
            ]);
        }
        
        // Separate units by side
        $defender_units = array_filter($all_units, function($unit) use ($battle) {
            return $unit['division_id'] == $battle['defender_division_id'];
        });
        $attacker_units = array_filter($all_units, function($unit) use ($battle) {
            return $unit['division_id'] == $battle['attacker_division_id'];
        });
        
        if (empty($defender_units) || empty($attacker_units)) {
            // Battle is over
            
            return false;
        }
        
        // Define buff handlers for different buff types
        $BUFF_HANDLERS = [
            'AllStatsMultiplier' => function($unit, $buff, $battle) {
                // Check if buff applies (matches continent or IsDefending condition)
                if ($buff['target'] === $battle['continent'] || 
                    ($buff['target'] === 'IsDefending' && $unit['division_id'] == $battle['defender_division_id'])) {
                    return $buff['value'];
                    log_battle_message("AllStatsMultiplier buff applied");
                }
                log_battle_message("AllStatsMultiplier buff not applied");
                return 1; // No multiplier if conditions don't match
            },
            'ManeuverMultiplier' => function($unit, $buff, $battle) {
                // Similar to AllStatsMultiplier but only for maneuver
                if ($buff['target'] === $battle['continent'] || 
                    ($buff['target'] === 'IsDefending' && $unit['division_id'] == $battle['defender_division_id'])) {
                    return $buff['value'];
                }
                return 1;
            },
            'FriendlyDamageReductionMultiplier' => function($unit, $buff, $battle) {
                // This handler will be used later for damage calculations
                if ($buff['target'] === $unit['type']) {
                    return $buff['value'];
                }
                return 1;
            },
            'FirepowerMultiplierAgainstUnit' => function($unit, $buff, $target_unit) {
                if ($buff['target'] === $target_unit['type']) {
                    log_battle_message("FirepowerMultiplierAgainstUnit buff applied", 'DEBUG', [
                        'unit_id' => $unit['unit_id'],
                        'target_unit_id' => $target_unit['unit_id'],
                        'buff_value' => $buff['value']
                    ]);
                    return $buff['value'];
                }
                return 1;
            },
            'FriendlyManeuverMultiplier' => function($unit, $buff, $battle) {
                // Always apply the multiplier regardless of target
                return $buff['value'];
            },
            'ArmourMultiplierAgainstUnit' => function($unit, $buff, $attacking_unit) {
                if ($buff['target'] === $attacking_unit['type']) {
                    log_battle_message("ArmourMultiplierAgainstUnit buff applied", 'DEBUG', [
                        'unit_id' => $unit['unit_id'],
                        'attacking_unit_id' => $attacking_unit['unit_id'],
                        'buff_value' => $buff['value']
                    ]);
                    return $buff['value'];
                }
                return 1;
            },
            'EnemyManeuverMultiplier' => function($unit, $buff, $battle) {
                // Always apply the multiplier to enemy units
                return $buff['value'];
            },
        ];

        // Get all buffs for units, grouped by type
        $stmt = $pdo->prepare("
            SELECT b.* 
            FROM buffs b
            WHERE b.unit_id IN (" . implode(',', array_column($all_units, 'unit_id')) . ")
        ");
        $stmt->execute();
        $all_buffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Index buffs by unit_id and buff_type for easy lookup
        $unit_buffs = [];
        foreach ($all_buffs as $buff) {
            if (!isset($unit_buffs[$buff['unit_id']])) {
                $unit_buffs[$buff['unit_id']] = [];
            }
            if (!isset($unit_buffs[$buff['unit_id']][$buff['buff_type']])) {
                $unit_buffs[$buff['unit_id']][$buff['buff_type']] = [];
            }
            $unit_buffs[$buff['unit_id']][$buff['buff_type']][] = $buff;
        }

        // Get FriendlyManeuverMultiplier buffs for each division
        $stmt = $pdo->prepare("
            SELECT b.*, u.division_id 
            FROM buffs b
            JOIN units u ON b.unit_id = u.unit_id
            WHERE u.division_id IN (?, ?) 
            AND b.buff_type = 'FriendlyManeuverMultiplier'
        ");
        $stmt->execute([$battle['defender_division_id'], $battle['attacker_division_id']]);
        $maneuver_buffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Index maneuver multipliers by division
        $division_maneuver_multipliers = [
            $battle['defender_division_id'] => 1,
            $battle['attacker_division_id'] => 1
        ];

        foreach ($maneuver_buffs as $buff) {
            $division_maneuver_multipliers[$buff['division_id']] *= $buff['value'];
        }

        // Get EnemyManeuverMultiplier buffs for each division
        $stmt = $pdo->prepare("
            SELECT b.*, u.division_id 
            FROM buffs b
            JOIN units u ON b.unit_id = u.unit_id
            WHERE u.division_id IN (?, ?) 
            AND b.buff_type = 'EnemyManeuverMultiplier'
        ");
        $stmt->execute([$battle['defender_division_id'], $battle['attacker_division_id']]);
        $enemy_maneuver_buffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Index enemy maneuver multipliers by division
        $enemy_division_maneuver_multipliers = [
            $battle['defender_division_id'] => 1,
            $battle['attacker_division_id'] => 1
        ];

        foreach ($enemy_maneuver_buffs as $buff) {
            // Apply the multiplier to the OPPOSING division
            $opposing_division_id = ($buff['division_id'] == $battle['defender_division_id']) 
                ? $battle['attacker_division_id'] 
                : $battle['defender_division_id'];
            $enemy_division_maneuver_multipliers[$opposing_division_id] *= $buff['value'];
        }

        // Calculate total maneuver for weighted random selection
        $total_maneuver = 0;
        foreach ($all_units as $unit) {
            $maneuver = max(1, $unit['maneuver']); // Ensure minimum of 1 maneuver
            
            // Apply AllStatsMultiplier buffs if they exist
            if (isset($unit_buffs[$unit['unit_id']]['AllStatsMultiplier'])) {
                foreach ($unit_buffs[$unit['unit_id']]['AllStatsMultiplier'] as $buff) {
                    $maneuver *= $BUFF_HANDLERS['AllStatsMultiplier']($unit, $buff, $battle);
                }
            }
            
            // Apply FriendlyManeuverMultiplier buffs if they exist
            if (isset($unit_buffs[$unit['unit_id']]['FriendlyManeuverMultiplier'])) {
                foreach ($unit_buffs[$unit['unit_id']]['FriendlyManeuverMultiplier'] as $buff) {
                    $maneuver *= $BUFF_HANDLERS['FriendlyManeuverMultiplier']($unit, $buff, $battle);
                }
            }
            
            // Apply division-wide maneuver multiplier
            $maneuver *= $division_maneuver_multipliers[$unit['division_id']];
            
            // Apply enemy maneuver multiplier
            $maneuver *= $enemy_division_maneuver_multipliers[$unit['division_id']];
            
            $total_maneuver += $maneuver;
        }
        
        // Randomly select attacking unit based on maneuver
        $roll = rand(1, $total_maneuver);
        $current_sum = 0;
        $attacking_unit = null;
        
        foreach ($all_units as $unit) {
            $maneuver = max(1, $unit['maneuver']);
            
            // Apply AllStatsMultiplier buffs if they exist
            if (isset($unit_buffs[$unit['unit_id']]['AllStatsMultiplier'])) {
                foreach ($unit_buffs[$unit['unit_id']]['AllStatsMultiplier'] as $buff) {
                    $maneuver *= $BUFF_HANDLERS['AllStatsMultiplier']($unit, $buff, $battle);
                }
            }
            
            // Apply FriendlyManeuverMultiplier buffs if they exist
            if (isset($unit_buffs[$unit['unit_id']]['FriendlyManeuverMultiplier'])) {
                foreach ($unit_buffs[$unit['unit_id']]['FriendlyManeuverMultiplier'] as $buff) {
                    $maneuver *= $BUFF_HANDLERS['FriendlyManeuverMultiplier']($unit, $buff, $battle);
                }
            }
            
            // Apply division-wide maneuver multiplier
            $maneuver *= $division_maneuver_multipliers[$unit['division_id']];
            
            // Apply enemy maneuver multiplier
            $maneuver *= $enemy_division_maneuver_multipliers[$unit['division_id']];
            
            $current_sum += $maneuver;
            if ($roll <= $current_sum) {
                $attacking_unit = $unit;
                break;
            }
        }
        
        // Fallback if no unit was selected
        if (!$attacking_unit) {
            $attacking_unit = $all_units[array_rand($all_units)];
        }
        
        // Select random target from opposing side
        $possible_targets = ($attacking_unit['division_id'] == $battle['defender_division_id']) 
            ? $attacker_units : $defender_units;
        $target_unit = $possible_targets[array_rand($possible_targets)];
        
        // Combat calculations
        $base_roll = rand(1, 100);
        
        // Apply buffs to stats for combat
        $attacking_firepower = $attacking_unit['firepower'];
        $attacking_maneuver = $attacking_unit['maneuver'];
        $attacking_armour = $attacking_unit['armour'];

        // Apply AllStatsMultiplier buffs
        if (isset($unit_buffs[$attacking_unit['unit_id']]['AllStatsMultiplier'])) {
            foreach ($unit_buffs[$attacking_unit['unit_id']]['AllStatsMultiplier'] as $buff) {
                $multiplier = $BUFF_HANDLERS['AllStatsMultiplier']($attacking_unit, $buff, $battle);
                $attacking_firepower = floor($attacking_firepower * $multiplier);
                $attacking_maneuver = floor($attacking_maneuver * $multiplier);
                $attacking_armour = floor($attacking_armour * $multiplier);
            }
        }

        // Apply FirepowerMultiplierAgainstUnit buffs
        if (isset($unit_buffs[$attacking_unit['unit_id']]['FirepowerMultiplierAgainstUnit'])) {
            log_battle_message("FirepowerMultiplierAgainstUnit buffs applied", 'DEBUG', [
                'unit_id' => $attacking_unit['unit_id'],
                'target_unit_id' => $target_unit['unit_id'],
                'buffs' => $unit_buffs[$attacking_unit['unit_id']]['FirepowerMultiplierAgainstUnit']
            ]);
            foreach ($unit_buffs[$attacking_unit['unit_id']]['FirepowerMultiplierAgainstUnit'] as $buff) {
                $multiplier = $BUFF_HANDLERS['FirepowerMultiplierAgainstUnit']($attacking_unit, $buff, $target_unit);
                log_battle_message("FirepowerMultiplierAgainstUnit multiplier applied", 'DEBUG', [
                    'multiplier' => $multiplier
                ]);
                $attacking_firepower = floor($attacking_firepower * $multiplier);
            }
        }

        $target_armour = $target_unit['armour'];
        $target_maneuver = $target_unit['maneuver'];

        // Apply AllStatsMultiplier buffs if they exist
        if (isset($unit_buffs[$target_unit['unit_id']]['AllStatsMultiplier'])) {
            foreach ($unit_buffs[$target_unit['unit_id']]['AllStatsMultiplier'] as $buff) {
                $multiplier = $BUFF_HANDLERS['AllStatsMultiplier']($target_unit, $buff, $battle);
                $target_armour = floor($target_armour * $multiplier);
                $target_maneuver = floor($target_maneuver * $multiplier);
            }
        }

        // Apply ArmourMultiplierAgainstUnit buffs if they exist
        if (isset($unit_buffs[$target_unit['unit_id']]['ArmourMultiplierAgainstUnit'])) {
            foreach ($unit_buffs[$target_unit['unit_id']]['ArmourMultiplierAgainstUnit'] as $buff) {
                $multiplier = $BUFF_HANDLERS['ArmourMultiplierAgainstUnit']($target_unit, $buff, $attacking_unit);
                $target_armour = floor($target_armour * $multiplier);
            }
        }

        // Calculate maneuver modifier with buffed stats
        $maneuver_modifier = $attacking_maneuver - $target_maneuver;
        $final_roll = $base_roll + $maneuver_modifier;
        
        // Calculate base damage with buffed stats
        $base_damage = ($attacking_firepower * 10) - ($target_armour * 5);
        $damage = $base_damage + rand(-4, 4);

        // Get all buffs from the defending team's units
        $stmt = $pdo->prepare("
            SELECT b.* 
            FROM buffs b
            JOIN units u ON b.unit_id = u.unit_id
            WHERE u.division_id = ? 
            AND b.buff_type = 'FriendlyDamageReductionMultiplier'
        ");
        $stmt->execute([$target_unit['division_id']]);
        $defensive_buffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply defensive buffs if target type matches
        foreach ($defensive_buffs as $buff) {
            if ($buff['target'] === $target_unit['type']) {
                $damage = floor($damage * $buff['value']);
            }
        }
        
        // Apply multiplier based on roll
        if ($final_roll >= 90) {
            $damage = floor($damage * 1.5);
            $hit_type = 'critical';
            $damage = max(1, $damage); // Ensure minimum 1 damage for critical hits
        } elseif (1 <= $final_roll && $final_roll <= 10) {
            $damage = floor($damage * 0.5);
            $hit_type = 'graze';
            $damage = max(1, $damage); // Ensure minimum 1 damage for grazing hits
        } elseif ($final_roll <= 0) {
            $damage = 0;
            $hit_type = 'miss';
        } else {
            $hit_type = 'regular';
            $damage = max(1, $damage); // Ensure minimum 1 damage for regular hits
        }
        
        // Update target unit's HP
        if ($damage > 0) {
            log_battle_message("Updating unit HP", 'INFO', [
                'target_unit_id' => $target_unit['unit_id'],
                'current_hp' => $target_unit['hp'],
                'damage' => $damage,
                'attacking_unit_id' => $attacking_unit['unit_id']
            ]);

            // Calculate new HP
            $new_hp = max(0, $target_unit['hp'] - $damage);
            
            if ($new_hp <= 0) {
                // First delete any buffs associated with the unit (due to foreign key constraints)
                $stmt = $pdo->prepare("
                    DELETE FROM buffs 
                    WHERE unit_id = ?
                ");
                $stmt->execute([$target_unit['unit_id']]);
                
                // Delete any equipment associated with the unit
                $stmt = $pdo->prepare("
                    DELETE FROM equipment 
                    WHERE unit_id = ?
                ");
                $stmt->execute([$target_unit['unit_id']]);
                
                // Then delete the unit itself
                $stmt = $pdo->prepare("
                    DELETE FROM units 
                    WHERE unit_id = ?
                ");
                $stmt->execute([$target_unit['unit_id']]);
                
                log_battle_message("Unit and associated equipment deleted", 'INFO', [
                    'target_unit_id' => $target_unit['unit_id'],
                    'unit_name' => $target_unit['custom_name']
                ]);
            } else {
                // Update HP if unit survives
                $stmt = $pdo->prepare("
                    UPDATE units 
                    SET hp = ? 
                    WHERE unit_id = ?
                ");
                $stmt->execute([$new_hp, $target_unit['unit_id']]);
            }
            
            log_battle_message("Unit HP updated", 'INFO', [
                'target_unit_id' => $target_unit['unit_id'],
                'new_hp' => $new_hp,
                'damage_dealt' => $damage
            ]);
        }
        
        // After calculating current strengths of both sides
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as living_units, division_id,
                   SUM(firepower + armour + maneuver + FLOOR(hp/10)) as total_strength 
            FROM units 
            WHERE division_id IN (?, ?) 
            AND hp > 0 
            GROUP BY division_id
        ");
        $stmt->execute([$battle['defender_division_id'], $battle['attacker_division_id']]);
        $living_units = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        log_battle_message("Checking living units and strengths", 'INFO', [
            'living_units_count' => count($living_units),
            'living_units' => $living_units
        ]);
        
        // Check if either division has no living units or is too weak
        $defender_has_units = false;
        $attacker_has_units = false;
        $defender_strength = 0;
        $attacker_strength = 0;
        
        foreach ($living_units as $division) {
            if ($division['division_id'] == $battle['defender_division_id']) {
                $defender_has_units = true;
                $defender_strength = $division['total_strength'];
            }
            if ($division['division_id'] == $battle['attacker_division_id']) {
                $attacker_has_units = true;
                $attacker_strength = $division['total_strength'];
            }
        }
        
        // Check for retreat conditions
        $retreat_triggered = false;
        $retreating_division_id = null;
        if ($defender_has_units && $attacker_has_units) {
            if ($defender_strength < ($attacker_strength / 10)) {
                $retreat_triggered = true;
                $retreating_division_id = $battle['defender_division_id'];
                $winner_division_id = $battle['attacker_division_id'];
                log_battle_message("Defender retreating due to strength disparity", 'INFO', [
                    'defender_strength' => $defender_strength,
                    'attacker_strength' => $attacker_strength
                ]);
            } elseif ($attacker_strength < ($defender_strength / 10)) {
                $retreat_triggered = true;
                $retreating_division_id = $battle['attacker_division_id'];
                $winner_division_id = $battle['defender_division_id'];
                log_battle_message("Attacker retreating due to strength disparity", 'INFO', [
                    'defender_strength' => $defender_strength,
                    'attacker_strength' => $attacker_strength
                ]);
            }
        }

        // End battle if either side has no units or a retreat is triggered
        if (!$defender_has_units || !$attacker_has_units || $retreat_triggered) {
            if (empty($living_units)) {
                log_battle_message("Error: No living units found on either side", 'ERROR');
                // Handle the edge case - end battle with no winner
                $stmt = $pdo->prepare("
                    UPDATE battles 
                    SET is_over = 1
                    WHERE battle_id = ?
                ");
                $stmt->execute([$battle_id]);
                return false;
            }

            if (!$retreat_triggered) {
                // Original victory condition (all units destroyed)
                $winner_division_id = $living_units[0]['division_id'];
            }
            
            $winner_name = ($winner_division_id == $battle['defender_division_id']) 
                ? $battle['defender_name'] 
                : $battle['attacker_name'];
                
            // Calculate XP reward based on opponent's initial strength
            $xp_reward = floor(
                ($winner_division_id == $battle['defender_division_id'] 
                    ? $battle['attacker_initial_strength'] 
                    : $battle['defender_initial_strength']
                ) / 5
            );

            if ($retreat_triggered) {
                // Get mission ID if this is a mission battle
                $stmt = $pdo->prepare("
                    SELECT mission_id FROM battles 
                    WHERE battle_id = ? AND mission_id != 0
                ");
                $stmt->execute([$battle_id]);
                $mission_result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Create retreat message
                $retreating_name = ($retreating_division_id == $battle['defender_division_id'])
                    ? $battle['defender_name']
                    : $battle['attacker_name'];
                
                $stmt = $pdo->prepare("
                    INSERT INTO combat_reports (
                        battle_id, time, message
                    ) VALUES (
                        ?, NOW(), ?
                    )
                ");
                $stmt->execute([
                    $battle_id,
                    "{$retreating_name} has retreated from battle!"
                ]);
            }
            
            // Award XP to surviving units
            $stmt = $pdo->prepare("
                UPDATE units 
                SET xp = xp + ?,
                    hp = max_hp 
                WHERE division_id IN (?, ?) 
                AND hp > 0
            ");
            $stmt->execute([
                $xp_reward,
                $battle['defender_division_id'], 
                $battle['attacker_division_id']
            ]);

            // Award loot tokens to the winner if this is a player victory
            $stmt = $pdo->prepare("
                SELECT user_id FROM divisions WHERE division_id = ?
            ");
            $stmt->execute([$winner_division_id]);
            $winner_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($winner_user) {
                $loot_tokens = floor(
                    ($winner_division_id == $battle['defender_division_id'] 
                        ? $battle['attacker_initial_strength'] 
                        : $battle['defender_initial_strength']
                    ) / 10
                );

                // Update the winner's loot tokens
                $stmt = $pdo->prepare("
                    UPDATE commodities
                    SET loot_token = loot_token + ?
                    WHERE id = ?;
                ");
                $stmt->execute([$loot_tokens, $winner_user['user_id']]);

                // Add loot token gain to battle report
                $result_message .= sprintf(
                    "<p class='loot-reward'>Gained %d loot tokens!</p>",
                    $loot_tokens
                );
            }
            
            // Update battle status
            $stmt = $pdo->prepare("
                UPDATE battles 
                SET is_over = 1, winner_name = ? 
                WHERE battle_id = ?
            ");
            $stmt->execute([$winner_name, $battle_id]);

            log_battle_message($winner_name . " won battle " . $battle_id);
            
            // If this is a mission battle, update mission status
            $stmt = $pdo->prepare("
                SELECT mission_id, user_id 
                FROM missions 
                WHERE battle_id = ?
            ");
            $stmt->execute([$battle_id]);
            $mission_result = $stmt->fetch(PDO::FETCH_ASSOC);

            log_battle_message("Got mission result");

            // Check if the player won (attacker wins and player is attacker, or defender wins and player is defender)
            $stmt = $pdo->prepare("
                SELECT user_id FROM missions WHERE mission_id = ?
            ");
            $stmt->execute([$mission_result['mission_id']]);
            $mission_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            
            if ($winner_name == $battle['attacker_name']) {
                log_battle_message("Player won");
                // Player won - mark as complete
                $complete_mission_stmt = $pdo->prepare("
                    UPDATE missions 
                    SET status = 'Complete' 
                    WHERE mission_id = ?
                ");
                $complete_mission_stmt->execute([$mission_result['mission_id']]);
                log_battle_message("Mission set to complete");
                
            } else {
                log_battle_message("Player lost");
                // Player lost - delete the mission and generate a new one
                $reset_mission_stmt = $pdo->prepare("
                    DELETE FROM missions 
                    WHERE mission_id = ?
                ");
                $reset_mission_stmt->execute([$mission_result['mission_id']]);

                // Generate a new mission to replace the deleted one
                try {
                    // Get user's other missions to avoid duplicates
                    $stmt = $pdo->prepare("
                        SELECT mission_type 
                        FROM missions 
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$mission_user['user_id']]);
                    $existing_missions = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    // Get available mission types excluding existing ones
                    $available_missions = array_diff(array_keys($MISSION_CONFIG), $existing_missions);

                    if (!empty($available_missions)) {
                        // Calculate total spawn weight
                        $total_weight = 0;
                        foreach ($available_missions as $mission_type) {
                            $total_weight += $MISSION_CONFIG[$mission_type]['spawn_weight'];
                        }

                        // Select random mission based on spawn weights
                        $random_num = rand(1, $total_weight);
                        $current_weight = 0;
                        $selected_mission = null;

                        foreach ($available_missions as $mission_type) {
                            $current_weight += $MISSION_CONFIG[$mission_type]['spawn_weight'];
                            if ($random_num <= $current_weight) {
                                $selected_mission = $mission_type;
                                break;
                            }
                        }

                        if ($selected_mission) {
                            // Insert new mission
                            $stmt = $pdo->prepare("
                                INSERT INTO missions (
                                    user_id, mission_type, status
                                ) VALUES (
                                    ?, ?, 'incomplete'
                                )
                            ");
                            $stmt->execute([
                                $mission_user['user_id'],
                                $selected_mission
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    log_battle_message("Failed to generate replacement mission", 'ERROR', [
                        'error' => $e->getMessage(),
                        'user_id' => $mission_user['user_id']
                    ]);
                }
            }
            
            // Update division combat status
            $stmt = $pdo->prepare("
                UPDATE divisions 
                SET in_combat = 0 
                WHERE division_id IN (?, ?)
            ");
            $stmt->execute([
                $battle['defender_division_id'], 
                $battle['attacker_division_id']
            ]);

            // Process level ups
            $level_up_messages = process_level_ups($battle['defender_division_id'], $battle['attacker_division_id']);
            
            // Add battle result to report
            $result_message = "<h3>Battle Conclusion</h3>";
            if ($retreat_triggered) {
                $result_message .= sprintf(
                    "<p class='battle-result'>%s retreated from battle due to overwhelming enemy strength!</p>",
                    htmlspecialchars($retreating_name)
                );
            } else {
                $result_message .= sprintf(
                    "<p class='battle-result'>%s emerged victorious!</p>",
                    htmlspecialchars($winner_name)
                );
            }

            // Add XP gains section
            $result_message .= "<h4>Experience Gained</h4><ul>";

            // Get all surviving units and their names
            $stmt = $pdo->prepare("
                SELECT unit_id, custom_name, name, division_id 
                FROM units 
                WHERE division_id IN (?, ?) 
                AND hp > 0
            ");
            $stmt->execute([$battle['defender_division_id'], $battle['attacker_division_id']]);
            $surviving_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($surviving_units as $unit) {
                $unit_name = $unit['custom_name'] ?: $unit['name'];
                $result_message .= sprintf(
                    "<li>%s gained %d XP</li>",
                    htmlspecialchars($unit_name),
                    $xp_reward
                );
            }
            $result_message .= "</ul>";

            // Add loot token section if tokens were awarded
            if (isset($loot_tokens) && $loot_tokens > 0) {
                $result_message .= sprintf(
                    "<h4>Rewards</h4><p class='loot-reward'>Gained %d loot tokens!</p>",
                    $loot_tokens
                );
            }

            // Add level-ups section
            if (!empty($level_up_messages)) {
                $result_message .= "<h4>Level Ups</h4><ul>";
                $result_message .= implode('', $level_up_messages);
                $result_message .= "</ul>";
            }

            append_to_battle_report($battle_id, $result_message);

            // Make the report visible now that battle is over
            $stmt = $pdo->prepare("
                UPDATE battle_reports 
                SET visible = 1 
                WHERE battle_id = ?
            ");
            $stmt->execute([$battle_id]);
            
            // Get user IDs for both divisions
            $stmt = $pdo->prepare("
                SELECT user_id 
                FROM divisions 
                WHERE division_id IN (?, ?)
            ");
            $stmt->execute([$battle['defender_division_id'], $battle['attacker_division_id']]);
            $involved_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Store battle end status in session to prevent duplicate notifications
            foreach ($involved_users as $user_id) {
                $_SESSION['battle_' . $battle_id . '_notification_sent'] = true;
            }
            
            // Get user IDs for both divisions and send them notifications
            $stmt = $pdo->prepare("
                SELECT d.user_id 
                FROM divisions d 
                WHERE d.division_id IN (?, ?) 
                AND d.user_id IS NOT NULL
            ");
            $stmt->execute([$battle['defender_division_id'], $battle['attacker_division_id']]);
            $involved_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($involved_users as $user_id) {
                // Check if user has notifications enabled
                $stmt = $pdo->prepare("SELECT notifications_enabled FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && $user['notifications_enabled']) {
                    echo json_encode([
                        'notification' => [
                            'title' => 'Battle Concluded',
                            'message' => "The battle '{$battle['battle_name']}' has ended! {$winner_name} emerged victorious!"
                        ]
                    ]);
                }
            }
            
            return false;
        }

        log_battle_message("About to create combat report:");
        
        // Create combat report
        $attacker_type = $attacking_unit['type'];
        $defender_type = $target_unit['type'];

        log_battle_message("Got types of units:");
        log_battle_message("Attacker type: " . $attacker_type);
        log_battle_message("Defender type: " . $defender_type);
        log_battle_message("Hit type: " . $hit_type);

        log_battle_message("Template Existence Check", 'DEBUG', [
            'isset_attacker_type' => isset($COMBAT_REPORT_TEMPLATES[$attacker_type]),
            'isset_defender_type' => isset($COMBAT_REPORT_TEMPLATES[$attacker_type][$defender_type]),
            'isset_hit_type' => isset($COMBAT_REPORT_TEMPLATES[$attacker_type][$defender_type][$hit_type])
        ]);

        log_battle_message("Combat Report Templates Structure", 'DEBUG', [
            'templates' => $COMBAT_REPORT_TEMPLATES
        ]);
        
        
        
        
        
        
        

        // Get template based on unit types and hit type
        $template = null;

        // Try to get specific template for this unit type combination
        if (isset($COMBAT_REPORT_TEMPLATES[$attacker_type]) && 
            isset($COMBAT_REPORT_TEMPLATES[$attacker_type][$defender_type]) && 
            isset($COMBAT_REPORT_TEMPLATES[$attacker_type][$defender_type][$hit_type])) {
            
            // Get the array of possible messages for this combination
            $possible_messages = $COMBAT_REPORT_TEMPLATES[$attacker_type][$defender_type][$hit_type];
            // Randomly select one message
            $template = $possible_messages[array_rand($possible_messages)];
        } else {
            // Ultimate fallback if no template found
            $template = "%s (%s) attacked %s (%s)" . ($hit_type !== 'miss' ? " dealing %s damage" : "");
            log_battle_message("Selected fallback template");
        }

        log_battle_message("Selected template");
        
        // Get the correct division names based on the attacking unit
        $attacking_division_name = ($attacking_unit['division_id'] == $battle['defender_division_id']) 
            ? $battle['defender_name'] 
            : $battle['attacker_name'];
        
        $defending_division_name = ($target_unit['division_id'] == $battle['defender_division_id']) 
            ? $battle['defender_name'] 
            : $battle['attacker_name'];

        // Format message differently for misses (which don't need damage parameter)
        if ($hit_type === 'miss') {
            $message = sprintf($template,
                $attacking_unit['custom_name'],
                $attacking_division_name,
                $target_unit['custom_name'],
                $defending_division_name
            );
        } else {
            $message = sprintf($template,
                $attacking_unit['custom_name'],
                $attacking_division_name,
                $target_unit['custom_name'],
                $defending_division_name,
                $damage
            );
            
            // Check if target unit was destroyed by this attack
            if ($target_unit['hp'] - $damage <= 0) {
                $destroyed_unit_message = sprintf(" %s (%s) was destroyed!", 
                    $target_unit['custom_name'],
                    $defending_division_name
                );
                $message .= $destroyed_unit_message;
                
                // Add to battle report
                $report_message = sprintf(
                    "<p class='unit-destroyed %s'>%s (%s) was destroyed in combat!</p>",
                    ($target_unit['division_id'] == $battle['defender_division_id']) ? 'friendly' : 'enemy',
                    htmlspecialchars($target_unit['custom_name']),
                    htmlspecialchars($defending_division_name)
                );
                append_to_battle_report($battle_id, $report_message);
            }
        }
        
        log_battle_message("Attempting to create combat report with:");
        log_battle_message("Template: " . $template);
        log_battle_message("Hit type: " . $hit_type);
        log_battle_message("Attacking unit: " . print_r($attacking_unit, true));
        log_battle_message("Target unit: " . print_r($target_unit, true));
        log_battle_message("Damage: " . $damage);
        
        // Before creating combat report
        log_battle_message("Preparing combat report data", 'INFO', [
            'battle_id' => $battle_id,
            'message' => $message,
            'attacker_id' => $attacking_unit['unit_id'],
            'defender_id' => $target_unit['unit_id'],
            'roll' => $base_roll,
            'modifier' => $maneuver_modifier,
            'damage' => $damage
        ]);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO combat_reports (
                    battle_id, time, message, 
                    attacker_unit_id, defender_unit_id,
                    combat_roll, maneuver_modifier, damage
                ) VALUES (
                    ?, NOW(), ?,
                    ?, ?,
                    ?, ?, ?
                )
            ");
            
            $result = $stmt->execute([
                $battle_id,
                $message,
                $attacking_unit['unit_id'],
                $target_unit['unit_id'],
                $base_roll,
                $maneuver_modifier,
                $damage
            ]);

            if (!$result) {
                throw new Exception("Failed to insert combat report: " . implode(" ", $stmt->errorInfo()));
            }

            log_battle_message("Combat report created successfully", 'INFO', [
                'battle_id' => $battle_id,
                'report_id' => $pdo->lastInsertId()
            ]);

        } catch (Exception $e) {
            log_battle_message("Failed to create combat report", 'ERROR', [
                'error' => $e->getMessage(),
                'sql_error' => $stmt->errorInfo()
            ]);
            throw $e;
        }
        
        return true;
        
    } catch (Exception $e) {
        log_battle_message("Battle Processing Error", 'ERROR', [
            'battle_id' => $battle_id,
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString()
        ]);

        // If it's a PDO error, log the specific database error
        if ($pdo->errorInfo()[0] !== '00000') {
            log_battle_message("Database Error", 'ERROR', [
                'sql_state' => $pdo->errorInfo()[0],
                'error_code' => $pdo->errorInfo()[1],
                'error_message' => $pdo->errorInfo()[2]
            ]);
        }
        
        return false;
    }
}

function sendBattleEndNotification($user_id, $battle_name, $winner_name) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT notifications_enabled FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['notifications_enabled']) {
        echo json_encode([
            'notification' => [
                'title' => 'Battle Concluded',
                'message' => "The battle '$battle_name' has ended! $winner_name emerged victorious!"
            ]
        ]);
    }
}

// Process a single combat action if battle_id is provided
if (isset($_GET['battle_id'])) {
    $battle_id = intval($_GET['battle_id']);
    process_battle($battle_id);
} 