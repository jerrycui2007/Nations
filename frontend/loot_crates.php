<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/equipment_config.php';
require_once 'helpers/resource_display.php';
require_once 'helpers/time_display.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's resources
$stmt = $pdo->prepare("SELECT * FROM commodities WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_resources = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loot Crate Shop - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <?php include 'toast.php'; ?>
    <style>
        .main-content {
            margin-left: 220px;
            padding: 15px;
            padding-bottom: 60px;
        }

        .content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .crate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .crate-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .crate-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .crate-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .crate-type {
            font-size: 0.9em;
            color: #666;
        }

        .crate-content {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .crate-description {
            color: #666;
            margin-bottom: 15px;
            flex: 1;
        }

        .crate-cost {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }

        .buy-button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-top: auto;
        }

        .buy-button:hover {
            background-color: #45a049;
        }

        .buy-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            width: calc(100% - 200px);
            position: fixed;
            bottom: 0;
            right: 0;
            z-index: 0;
            margin-left: 200px;
        }

        /* Equipment Card Styles */
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .equipment-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .equipment-header {
            padding: 15px 20px;
            margin-bottom: 0;
            position: relative;
            z-index: 5;
        }

        .rarity-common .equipment-header { background: transparent; }
        .rarity-uncommon .equipment-header { background: #2a9d38; color: white; }
        .rarity-rare .equipment-header { background: #0066cc; color: white; }
        .rarity-epic .equipment-header { background: #6a1b9a; color: white; }
        .rarity-legendary .equipment-header { background: #e65100; color: white; }

        .equipment-content {
            padding: 15px;
            position: relative;
            z-index: 3;
            border-radius: 0 0 8px 8px;
        }

        .has-foil .equipment-content {
            background: url('resources/foil.gif');
            background-size: cover;
        }

        .buff-list {
            position: relative;
            z-index: 4;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 4px;
            padding: 10px;
        }

        .buff-item {
            padding: 5px 0;
            color: #444;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 10px;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Loot Crate Shop</h1>
            
            <?php
            $unit_types = ['Infantry', 'Armour', 'Air', 'Static', 'Special Forces'];
            
            foreach ($unit_types as $unit_type):
            ?>
                <h2><?php echo $unit_type; ?> Equipment Crates</h2>
                <div class="crate-grid">
                    <?php foreach ($CRATE_CONFIG as $crate_key => $crate): ?>
                        <div class="crate-card">
                            <div class="crate-header">
                                <div class="crate-name"><?php echo $crate_key; ?> <?php echo $unit_type; ?> Crate</div>
                                <div class="crate-type"><?php echo $unit_type; ?> Equipment</div>
                            </div>
                            <div class="crate-content">
                                <div class="crate-description">
                                    <?php echo $crate['description']; ?>
                                </div>
                                <div class="crate-cost">
                                    <?php 
                                    $cost = $crate['cost'];
                                    $can_afford = ($user_resources['loot_token'] ?? 0) >= $cost;
                                    ?>
                                    <span style="color: <?php echo $can_afford ? '#333' : '#ff4444'; ?>">
                                        <?php echo getResourceIcon('loot_token') . formatNumber($cost); ?>
                                    </span>
                                </div>
                                <button class="buy-button" onclick="buyLootCrate('<?php echo $crate_key; ?>', '<?php echo $unit_type; ?>')" <?php echo $can_afford ? '' : 'disabled'; ?>>
                                    Buy Crate
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        function buyLootCrate(crateType, unitType) {
            fetch('../backend/buy_loot_crate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `crate_type=${encodeURIComponent(crateType)}&unit_type=${encodeURIComponent(unitType)}`
            })
            .then(response => {
                return response.text().then(text => {
                    try {
                        const data = JSON.parse(text);
                        return data;
                    } catch (e) {
                        console.error('Failed to parse JSON response');
                        console.error('Raw response:', text);
                        console.error('Parse error:', e);
                        throw new Error('Server returned invalid JSON. Check console for details.');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    showLootResults(data.equipment);
                    const lootTokens = data.remaining_tokens;
                    updateLootTokenDisplay(lootTokens);
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                showToast(error.message || 'An error occurred while purchasing the crate', "error");
            });
        }

        function updateLootTokenDisplay(newAmount) {
            document.querySelectorAll('.crate-card').forEach(card => {
                const costSpan = card.querySelector('.crate-cost span');
                const costText = costSpan.textContent.trim();
                const cost = parseInt(costText.replace(/[^0-9]/g, ''));
                const buyButton = card.querySelector('.buy-button');
                
                costSpan.style.color = newAmount >= cost ? '#333' : '#ff4444';
                buyButton.disabled = newAmount < cost;
            });
        }

        function showLootResults(equipment) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.display = 'block';
            
            const content = document.createElement('div');
            content.className = 'modal-content';
            content.style.width = '90%';
            content.style.maxWidth = '800px';
            
            const closeBtn = document.createElement('span');
            closeBtn.className = 'close';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = () => modal.remove();
            
            const title = document.createElement('h2');
            title.textContent = 'Your New Equipment!';
            
            const grid = document.createElement('div');
            grid.className = 'equipment-grid';
            
            equipment.forEach(item => {
                const card = document.createElement('div');
                card.innerHTML = `
                    <div class="equipment-card rarity-${item.rarity.toLowerCase()}${item.is_foil ? ' has-foil' : ''}">
                        <div class="equipment-header">
                            <div class="equipment-name" style="font-size: 1.4em; margin-bottom: 8px;">
                                ${item.name}
                            </div>
                            <div class="equipment-type">
                                ${item.rarity} ${item.type}
                            </div>
                        </div>
                        <div class="equipment-content">
                            <p class="buff-list">
                                ${item.buffs.map(buff => {
                                    let buffText = '';
                                    switch (buff.buff_type) {
                                        case 'Firepower':
                                        case 'Armour':
                                        case 'Maneuver':
                                            buffText = `+${buff.value} ${buff.buff_type}`;
                                            break;
                                        case 'Health':
                                            buffText = `+${buff.value}% max health`;
                                            break;
                                        case 'Buff':
                                            buffText = buff.description;
                                            break;
                                        default:
                                            buffText = buff.description || '';
                                    }
                                    return `<div class="buff-item">${buffText}</div>`;
                                }).join('')}
                            </p>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
            
            content.appendChild(closeBtn);
            content.appendChild(title);
            content.appendChild(grid);
            modal.appendChild(content);
            document.body.appendChild(modal);
        }
    </script>
</body>
</html> 