<?php
// The $item variable should be passed from the parent file containing the equipment data
?>
<div class="equipment-card rarity-<?php echo strtolower($item['rarity']); ?><?php echo $item['is_foil'] == 1 ? ' has-foil' : ''; ?>"
     data-type="<?php echo htmlspecialchars($item['type']); ?>"
     data-rarity="<?php echo htmlspecialchars($item['rarity']); ?>"
     data-equipped="<?php echo isset($item['unit_id']) && $item['unit_id'] > 0 ? 'true' : 'false'; ?>">
    <div class="equipment-header">
        <div class="equipment-name-container">
            <div class="equipment-name"><?php echo htmlspecialchars($item['name']); ?></div>
            <?php if (isset($item['show_rename']) && $item['show_rename']): ?>
                <button class="rename-equipment-btn" onclick="showRenameEquipmentModal(<?php echo $item['equipment_id']; ?>, '<?php echo addslashes($item['name']); ?>')">
                    <i class="fas fa-edit"></i> Rename
                </button>
            <?php endif; ?>
        </div>
        <div class="equipment-type"><?php echo htmlspecialchars($item['rarity'] . ' ' . $item['type']); ?></div>
    </div>
    <div class="equipment-content">
        <p class="buff-list">
            <?php foreach ($item['buffs'] as $buff): ?>
                <div class="buff-item">
                    <?php
                    switch ($buff['buff_type']) {
                        case 'Firepower':
                        case 'Armour':
                        case 'Maneuver':
                            echo '+' . $buff['value'] . ' ' . $buff['buff_type'];
                            break;
                        case 'Health':
                            echo '+' . $buff['value'] . '% max health';
                            break;
                        case 'Buff':
                            echo $buff['description'] !== null ? htmlspecialchars($buff['description']) : '';
                            break;
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </p>
    </div>
</div>