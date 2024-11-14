<?php
require_once 'helpers/resource_display.php';
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/resource_config.php';
require_once 'toast.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $resource_filter = $_GET['resource_filter'] ?? 'all';
    
    // Fetch active trades
    $query = "SELECT t.trade_id, t.seller_id, t.resource_offered, t.amount_offered, 
                     t.price_per_unit, t.date, u.country_name as seller_name,
                     u.leader_name as leader_name
              FROM trades t
              JOIN users u ON t.seller_id = u.id";
    
    $params = [];
    if ($resource_filter !== 'all') {
        $query .= " WHERE t.resource_offered = ?";
        $params[] = $resource_filter;
    }
    
    $query .= " ORDER BY t.price_per_unit ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch trade history
    $stmt = $pdo->prepare("SELECT th.*, 
                           u_buyer.country_name as buyer_name,
                           u_seller.country_name as seller_name
                           FROM trade_history th
                           JOIN users u_buyer ON th.buyer_id = u_buyer.id
                           JOIN users u_seller ON th.seller_id = u_seller.id
                           WHERE buyer_id = ? OR seller_id = ?
                           ORDER BY date DESC
                           LIMIT 10");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $trade_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "An error occurred while loading trades.";
}

try {
    // Fetch user's resources
    $stmt = $pdo->prepare("SELECT * FROM commodities WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_resources = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "An error occurred while loading resources.";
}

function getResourceDisplayName($resource) {
    global $RESOURCE_CONFIG;
    return isset($RESOURCE_CONFIG[$resource]['display_name']) ? 
           $RESOURCE_CONFIG[$resource]['display_name'] : 
           ucwords(str_replace('_', ' ', $resource));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trade Market - Nations</title>
    <link rel="stylesheet" type="text/css" href="design/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 220px;
            padding-bottom: 60px; /* Add space for footer */
        }

        .content {
            padding: 40px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            position: fixed;
            bottom: 0;
            right: 0;
            width: calc(100% - 220px); /* Viewport width minus sidebar width */
            z-index: 1000;
        }

        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .trade-button {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .trade-button:hover {
            background-color: #45a049;
        }
        .cancel-button {
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .cancel-button:hover {
            background-color: #da190b;
        }
        .nation-link {
            color: #0066cc;
            text-decoration: none;
        }
        .nation-link:hover {
            text-decoration: underline;
        }
        select, input[type="number"] {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 100%;
            box-sizing: border-box;
        }

        #createTradeForm table {
            margin-bottom: 0;
        }

        .trade-history h2 {
            margin-top: 30px;
        }
        .trade-history table {
            margin-top: 10px;
        }
        /* Color for purchases (red) and sales (green) is handled inline */
        .nation-link {
            color: #0066cc;
            text-decoration: none;
        }
        .nation-link:hover {
            text-decoration: underline;
        }
        /* Add hover effects for the rows */
        tr[style*="ffebee"]:hover {
            background-color: #ffcdd2 !important;
        }
        tr[style*="e8f5e9"]:hover {
            background-color: #c8e6c9 !important;
        }
        #resource_filter {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            min-width: 200px;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background-color: #333;
            color: white;
            border-radius: 4px;
            padding: 12px 24px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            border-left: 4px solid #4CAF50;
        }

        .toast.error {
            border-left: 4px solid #dc3545;
        }

        .trade-amount-input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: right;
            display: inline-block;
            margin: 0;
            background-color: white;
            box-sizing: border-box;
        }

        .total-price {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .trade-amount-input::-webkit-inner-spin-button,
        .trade-amount-input::-webkit-outer-spin-button {
            opacity: 1;
        }

        td {
            min-width: 100px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content">
            <h1>Commodities and Resource Exchange</h1>
            
            <form id="createTradeForm" style="margin-bottom: 20px;">
                <table>
                    <tr>
                        <th>Resource to Sell</th>
                        <th>Amount</th>
                        <th>Price per Unit</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td>
                            <select name="resource" required>
                                <option value="">Select Resource</option>
                                <?php
                                foreach ($RESOURCE_CONFIG as $resource_key => $resource_data) {
                                    // Skip money as it's not tradeable
                                    if ($resource_key === 'money') continue;
                                    
                                    echo "<option value=\"{$resource_key}\">" . 
                                         getResourceDisplayName($resource_key) . 
                                         "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="amount" min="1" required>
                        </td>
                        <td>
                            <input type="number" name="price" min="1" step="0.01" required>
                        </td>
                        <td>
                            <button type="submit" class="trade-button">Create Trade</button>
                        </td>
                    </tr>
                </table>
            </form>
            
            <div style="margin-bottom: 15px;">
                <label for="resource_filter">Filter by Resource:</label>
                <select id="resource_filter" onchange="filterTrades(this.value)" style="margin-left: 10px; padding: 5px;">
                    <option value="all">All Resources</option>
                    <?php
                    foreach ($RESOURCE_CONFIG as $resource_key => $resource_data) {
                        // Skip money as it's not tradeable
                        if ($resource_key === 'money') continue;
                        
                        $selected = ($resource_filter === $resource_key) ? 'selected' : '';
                        echo "<option value=\"{$resource_key}\" {$selected}>" . 
                             getResourceDisplayName($resource_key) . 
                             "</option>";
                    }
                    ?>
                </select>
            </div>

            <table>
                <tr>
                    <th>Seller</th>
                    <th>Resource & Amount</th>
                    <th>Price Per Unit</th>
                    <th>Date Listed</th>
                    <th>Purchase Amount</th>
                    <th>Total Price</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($trades as $trade): ?>
                    <?php $total_price = $trade['amount_offered'] * $trade['price_per_unit']; ?>
                    <tr>
                        <td>
                            <a href="view.php?id=<?php echo htmlspecialchars($trade['seller_id']); ?>" class="nation-link">
                                <?php echo htmlspecialchars($trade['seller_name']); ?><br>
                                <small><?php echo htmlspecialchars($trade['leader_name']); ?></small>
                            </a>
                        </td>
                        <td>
                            <?php echo getResourceIcon($trade['resource_offered']); ?> 
                            <?php echo number_format($trade['amount_offered']); ?>
                            <?php echo htmlspecialchars($RESOURCE_CONFIG[$trade['resource_offered']]['display_name']); ?>
                        </td>
                        <td>
                            <?php echo getResourceIcon('money'); ?> 
                            <?php echo number_format($trade['price_per_unit']); ?><br>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($trade['date'])); ?></td>
                        <td>
                            <?php if ($trade['seller_id'] != $_SESSION['user_id']): ?>
                                <input type="number" 
                                       class="trade-amount-input" 
                                       id="amount_<?php echo $trade['trade_id']; ?>"
                                       min="1" 
                                       max="<?php echo $trade['amount_offered']; ?>"
                                       value="1"
                                       oninput="updateTotalPrice(<?php echo $trade['trade_id']; ?>, <?php echo $trade['price_per_unit']; ?>, <?php echo $user_resources['money']; ?>)">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div id="total_<?php echo $trade['trade_id']; ?>" class="total-price">
                                <?php 
                                // Calculate initial total for 1 unit
                                $initial_total = $trade['price_per_unit']; // For 1 unit
                                $can_afford = $user_resources['money'] >= $initial_total;
                                $color_style = $can_afford ? '' : 'color: #ff4444;';
                                ?>
                                <span style="<?php echo $color_style; ?>">
                                    <?php echo getResourceIcon('money'); ?> <?php echo number_format($initial_total); ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?php if ($trade['seller_id'] != $_SESSION['user_id']): ?>
                                <button class="trade-button" onclick="completeTrade(<?php echo $trade['trade_id']; ?>)">
                                    Purchase
                                </button>
                            <?php else: ?>
                                <button class="cancel-button" onclick="cancelTrade(<?php echo $trade['trade_id']; ?>)">
                                    Cancel
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h2>Trade History</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Other Party</th>
                    <th>Resource</th>
                    <th>Amount</th>
                    <th>Price per Unit</th>
                    <th>Total Value</th>
                </tr>
                <?php foreach ($trade_history as $history): ?>
                    <?php 
                    $is_buyer = $history['buyer_id'] == $_SESSION['user_id'];
                    $total_value = $history['amount_offered'] * $history['price_per_unit'];
                    ?>
                    <tr style="background-color: <?php echo $is_buyer ? '#ffebee' : '#e8f5e9'; ?>">
                        <td><?php echo date('M j, Y g:i A', strtotime($history['date_finished'])); ?></td>
                        <td><?php echo $is_buyer ? 'Purchase' : 'Sale'; ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $is_buyer ? $history['seller_id'] : $history['buyer_id']; ?>" class="nation-link">
                                <?php echo htmlspecialchars($is_buyer ? $history['seller_name'] : $history['buyer_name']); ?>
                            </a>
                        </td>
                        <td><?php echo getResourceDisplayName($history['resource_offered']); ?></td>
                        <td><?php echo number_format($history['amount_offered']); ?></td>
                        <td>$<?php echo number_format($history['price_per_unit']); ?></td>
                        <td>$<?php echo number_format($total_value); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($trade_history)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No trade history found</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <div class="toast-container"></div>

    <script>
    function showToast(message, type = 'success') {
        const container = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    function completeTrade(tradeId) {
        const amountInput = document.getElementById(`amount_${tradeId}`);
        const amount = parseInt(amountInput.value) || 0;
        
        if (confirm(`Are you sure you want to purchase ${amount.toLocaleString()} units?`)) {
            fetch('../backend/complete_trade.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `trade_id=${tradeId}&amount=${amount}`
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while completing the trade.', 'error');
            });
        }
    }

    function cancelTrade(tradeId) {
        if (confirm('Are you sure you want to cancel this trade? The resources will be returned to your inventory.')) {
            fetch('../backend/cancel_trade.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `trade_id=${tradeId}`
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while canceling the trade.', 'error');
            });
        }
    }
    function filterTrades(resource) {
    window.location.href = 'trade.php' + (resource !== 'all' ? '?resource_filter=' + resource : '');
}
    </script>
    <script>
    document.getElementById('createTradeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('../backend/create_trade.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => window.location.reload(), 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while creating the trade.', 'error');
        });
    });
    </script>
    <script>
    function updateTotalPrice(tradeId, pricePerUnit, userMoney) {
        const amountInput = document.getElementById(`amount_${tradeId}`);
        const totalElement = document.getElementById(`total_${tradeId}`);
        const amount = parseInt(amountInput.value) || 0;
        const total = amount * pricePerUnit;
        
        // Get the existing money icon from the page
        const moneyIcon = document.querySelector('img[alt="money"].resource-icon').outerHTML;
        
        // Format the number with commas
        const formattedTotal = total.toLocaleString();
        
        // Check if user can afford it
        const canAfford = userMoney >= total;
        const color = canAfford ? '' : '#ff4444';
        
        totalElement.innerHTML = `<span style="color: ${color}">${moneyIcon} ${formattedTotal}</span>`;
    }
    </script>
</body>
</html>
