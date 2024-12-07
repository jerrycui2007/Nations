<?php
require_once __DIR__ . '/../../backend/unit_config.php';

$unit_type = strtolower(str_replace(' ', '_', $unit['name']));
$unit_type = strtolower(str_replace('-', '_', $unit_type));
$recruitment_cost = isset($UNIT_CONFIG[$unit_type]['recruitment_cost']) 
    ? $UNIT_CONFIG[$unit_type]['recruitment_cost'] 
    : [];

$division_in_combat = false;
if ($unit['division_id'] > 0) {
    $stmt = $pdo->prepare("SELECT in_combat FROM divisions WHERE division_id = ?");
    $stmt->execute([$unit['division_id']]);
    $division = $stmt->fetch(PDO::FETCH_ASSOC);
    $division_in_combat = $division['in_combat'] ?? false;
}
?>
<div class="unit-card" data-unit-type="<?php echo $unit['name']; ?>">
    <div class="unit-level-box"><?php echo $unit['level']; ?></div>
    <div class="unit-content">
        <div class="unit-custom-name">
            <a href="unit_view.php?unit_id=<?php echo $unit['unit_id']; ?>" 
               class="unit-name-link" 
               target="_blank">
                <?php echo htmlspecialchars($unit['custom_name']); ?>
            </a>
            <button class="rename-unit-btn" 
                    onclick="showRenameUnitModal(<?php echo $unit['unit_id']; ?>, '<?php echo addslashes($unit['custom_name'] ?? $unit['name']); ?>')"
                    <?php echo $division_in_combat ? 'disabled title="Cannot rename units while division is in combat"' : ''; ?>>
                <i class="fas fa-edit"></i> Rename
            </button>  
        </div>
        <div class="unit-name">
            <?php echo htmlspecialchars($unit['name']); ?>
        </div>
        
        <div class="unit-stats-container">
            <!-- Firepower -->
            <div class="unit-stat">
                <span class="unit-stat-label">FIR:</span>
                <div class="unit-stat-bar-container">
                    <div class="unit-stat-bar stat-firepower" style="width: <?php echo min(($unit['firepower'] / 20) * 100, 100); ?>%"></div>
                    <div class="unit-stat-content">
                        <span><?php echo $unit['firepower']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Armour -->
            <div class="unit-stat">
                <span class="unit-stat-label">DEF:</span>
                <div class="unit-stat-bar-container">
                    <div class="unit-stat-bar stat-armour" style="width: <?php echo min(($unit['armour'] / 20) * 100, 100); ?>%"></div>
                    <div class="unit-stat-content">
                        <span><?php echo $unit['armour']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Maneuver -->
            <div class="unit-stat">
                <span class="unit-stat-label">MAN:</span>
                <div class="unit-stat-bar-container">
                    <div class="unit-stat-bar stat-maneuver" style="width: <?php echo min(($unit['maneuver'] / 20) * 100, 100); ?>%"></div>
                    <div class="unit-stat-content">
                        <span><?php echo $unit['maneuver']; ?></span>
                    </div>
                </div>
            </div>

            <!-- HP -->
            <div class="unit-stat">
                <span class="unit-stat-label">HP:</span>
                <div class="unit-stat-bar-container">
                    <div class="unit-stat-bar stat-hp <?php 
                        if ($unit['hp'] <= 0) {
                            echo 'stat-hp-dead';
                        } elseif ($unit['hp'] < $unit['max_hp'] / 2) {
                            echo 'stat-hp-low';
                        }
                    ?>" style="width: <?php echo min(($unit['hp'] / $unit['max_hp']) * 100, 100); ?>%"></div>
                    <div class="unit-stat-content">
                        <span><?php echo $unit['hp']; ?>/<?php echo $unit['max_hp']; ?></span>
                    </div>
                </div>
            </div>

            <!-- XP -->
            <div class="unit-stat">
                <span class="unit-stat-label">XP:</span>
                <div class="unit-stat-bar-container">
                    <?php
                    $current_level = $unit['level'];
                    $next_level = $current_level + 1;
                    $xp_for_next_level = $LEVEL_CONFIG[$next_level] ?? $LEVEL_CONFIG[count($LEVEL_CONFIG)];
                    $xp_percentage = ($unit['xp'] / $xp_for_next_level) * 100;
                    ?>
                    <div class="unit-stat-bar stat-xp" style="width: <?php echo min($xp_percentage, 100); ?>%"></div>
                    <div class="unit-stat-content">
                        <span><?php echo $unit['xp'] . '/' . $xp_for_next_level; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Upkeep -->
            <div class="unit-stat upkeep-stat">
                <span class="unit-stat-label">UPK:</span>
                <div class="upkeep-content">
                    <?php 
                    $upkeep_strings = [];
                    $unit_type = strtolower(str_replace(' ', '_', $unit['name']));
                    foreach ($UNIT_CONFIG[$unit_type]['upkeep'] as $resource => $amount) {
                        $upkeep_strings[] = getResourceIcon($resource) . formatNumber($amount);
                    }
                    echo implode(' ', $upkeep_strings) ?: 'None';
                    ?>
                </div>
            </div>

            <!-- Buffs -->
            <?php if (!empty($unit['buff_descriptions'])): ?>
                <div class="unit-stat buff-stat">
                    <span class="unit-stat-label">Buffs:</span>
                    <div class="buff-content">
                        <?php 
                        $buffs = explode(',', $unit['buff_descriptions']);
                        foreach ($buffs as $buff) {
                            echo "<div class='buff-item'>" . htmlspecialchars($buff) . "</div>";
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="unit-actions">
        <button class="move-division-button" 
                onclick="showMoveDivisionModal(<?php echo $unit['unit_id']; ?>, '<?php echo addslashes($unit['name']); ?>', '<?php echo addslashes($unit['type']); ?>')"
                <?php echo $division_in_combat ? 'disabled title="Cannot move units while division is in combat"' : ''; ?>>
            <i class="fas fa-exchange-alt"></i> Move Division
        </button>
        <button class="disband-button" 
                onclick="disbandUnit(<?php echo $unit['unit_id']; ?>, '<?php echo addslashes($unit['name']); ?>')"
                <?php echo $division_in_combat ? 'disabled title="Cannot disband units while division is in combat"' : ''; ?>>
            DISBAND UNIT
        </button>
    </div>
</div> 