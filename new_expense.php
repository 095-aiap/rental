<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$mysqli = new mysqli("localhost", "bewith_businesscard", "WSrRZHEmfjKzssJ5v9Xf", "bewith_businesscard");

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$message = "";
$selected_asset = isset($_POST['asset_code']) ? $_POST['asset_code'] : "";
$selected_date = isset($_POST['transaction_date']) ? $_POST['transaction_date'] : date("Y-m-d");

// Fetch assets for dropdown
$assets = [];
$sqlAssets = "SELECT asset_code, asset_name FROM assets";
$resAssets = $mysqli->query($sqlAssets);
if ($resAssets) {
    while ($asset = $resAssets->fetch_assoc()) {
        $assets[] = $asset;
    }
}

// Fetch only "Expense" type items for dropdown
$items = [];
$sqlItems = "SELECT item_code, item_name FROM items WHERE type = 'Expense'";
$resItems = $mysqli->query($sqlItems);
if ($resItems) {
    while ($item = $resItems->fetch_assoc()) {
        $items[] = $item;
    }
}

// Handle form submission (without refreshing the screen)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_expense'])) {
    $selected_asset = $mysqli->real_escape_string($_POST['asset_code']);
    $selected_item = $mysqli->real_escape_string($_POST['item_code']);
    $transaction_date = $mysqli->real_escape_string($_POST['transaction_date']);
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $note = $mysqli->real_escape_string($_POST['note']);
    $type = "Expense";
    $created_by = 'mim';

    if (empty($selected_asset)) {
        $message = "⚠️ Error: Please select an asset.";
    } else {
        $sql = "INSERT INTO transactions (asset_code, type, item_code, transaction_date, amount, note, created_time, created_by)
                VALUES ('$selected_asset', '$type', '$selected_item', '$transaction_date', $amount, '$note', NOW(), '$created_by')";

        if ($mysqli->query($sql)) {
            $message = "✅ New expense recorded successfully!";
        } else {
            $message = "⚠️ Error: " . $mysqli->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New Expense</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script>
        // Hide success message after 3 seconds
        function hideMessage() {
            let messageBox = document.getElementById("message");
            if (messageBox) {
                setTimeout(() => {
                    messageBox.style.display = "none";
                }, 3000);
            }
        }
        window.onload = hideMessage;

        // Prevent non-numeric characters in amount field
        function validateAmount(event) {
            let key = event.key;
            if (!/[0-9.]/.test(key)) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>

    <div class="container">
        <!-- Top Menu Inside Container -->
        <div class="menu">
        <button onclick="window.location.href='index.php'" class="menu-btn">กลับ</button>      
            <button onclick="window.location.href='new_expense.php'" class="menu-btn">รายจ่าย</button>
            <button onclick="window.location.href='new_income.php'" class="menu-btn">รายรับ</button>
            <button onclick="window.location.href='search.php'" class="menu-btn">ค้นหา</button>   
        </div>

        <h2>Add New Expense</h2>
        <?php if (!empty($message)): ?>
            <p class="message" id="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="asset_code">Asset:</label>
                <select name="asset_code" id="asset_code" required class="form-input">
                    <option value="">Select an asset</option>
                    <?php foreach ($assets as $asset): ?>
                        <option value="<?php echo $asset['asset_code']; ?>" 
                            <?php if ($selected_asset == $asset['asset_code']) echo "selected"; ?>>
                            <?php echo $asset['asset_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="item_code">Item:</label>
                <select name="item_code" id="item_code" required class="form-input">
                    <option value="">Select an item</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item['item_code']; ?>">
                            <?php echo $item['item_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="transaction_date">Transaction Date:</label>
                <input type="date" id="transaction_date" name="transaction_date" value="<?php echo $selected_date; ?>" required class="form-input">
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0" value="0" required class="form-input" onkeypress="validateAmount(event)">
            </div>

            <div class="form-group">
                <label for="note">Note:</label>
                <textarea id="note" name="note" class="form-input"></textarea>
            </div>

            <button type="submit" name="save_expense" class="blue-button">Save Expense</button>
        </form>
    </div>

</body>
</html>
