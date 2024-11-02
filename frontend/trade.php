<?php
session_start();
require_once '../backend/db_connection.php';
require_once '../backend/resource_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $resource_filter = $_GET['resource_filter'] ?? 'all';
    
    // Fetch active trades
    $query = "SELECT t.trade_id, t.seller_id, t.resource_offered, t.amount_offered, 
                     t.price_per_unit, t.date, u.country_name as seller_name
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
        }
        .content {
            margin-left: 200px;
            padding: 20px;
            padding-bottom: 60px;
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
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
                <th>Resource</th>
                <th>Amount</th>
                <th>Price per Unit</th>
                <th>Total Price</th>
                <th>Date Listed</th>
                <th>Action</th>
            </tr>
            <?php foreach ($trades as $trade): ?>
                <?php $total_price = $trade['amount_offered'] * $trade['price_per_unit']; ?>
                <tr>
                    <td>
                        <a href="view.php?id=<?php echo htmlspecialchars($trade['seller_id']); ?>" class="nation-link">
                            <?php echo htmlspecialchars($trade['seller_name']); ?>
                        </a>
                    </td>
                    <td><?php echo getResourceDisplayName($trade['resource_offered']); ?></td>
                    <td><?php echo number_format($trade['amount_offered']); ?></td>
                    <td>$<?php echo number_format($trade['price_per_unit']); ?></td>
                    <td>$<?php echo number_format($total_price); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($trade['date'])); ?></td>
                    <td>
                        <?php if ($trade['seller_id'] != $_SESSION['user_id']): ?>
                            <button class="trade-button" onclick="completeTrade(<?php echo $trade['trade_id']; ?>, <?php echo $total_price; ?>)">
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
                    <td><?php echo htmlspecialchars($history['date']); ?></td>
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

    <?php include 'footer.php'; ?>

    <script>
    function completeTrade(tradeId, totalPrice) {
    if (confirm(`Are you sure you want to complete this trade? Total cost: $${totalPrice.toLocaleString()}`)) {
        fetch('../backend/complete_trade.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `trade_id=${tradeId}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the trade.');
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
                alert(data.message);
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while canceling the trade.');
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
            alert(data.message);
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the trade.');
        });
    });
    </script>
</body>
</html>
