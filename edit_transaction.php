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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Transaction ID not provided.");
}
$id = intval($_GET['id']);
$message = "";

// Fetch existing transaction
$sql = "SELECT * FROM transactions WHERE id = $id";
$res = $mysqli->query($sql);
if (!$res || $res->num_rows == 0) {
    die("Transaction not found.");
}
$transaction = $res->fetch_assoc();

// Fetch assets for dropdown
$assets = [];
$sqlAssets = "SELECT asset_code, asset_name FROM assets";
$resAssets = $mysqli->query($sqlAssets);
if ($resAssets) {
    while ($asset = $resAssets->fetch_assoc()) {
        $assets[] = $asset;
    }
}

// Fetch items based on type
$items = [];
$sqlItems = "SELECT item_code, item_name FROM items WHERE type = '" . $transaction['type'] . "'";
$resItems = $mysqli->query($sqlItems);
if ($resItems) {
    while ($item = $resItems->fetch_assoc()) {
        $items[] = $item;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asset_code = $mysqli->real_escape_string($_POST['asset_code']);
    $item_code = $mysqli->real_escape_string($_POST['item_code']);
    $transaction_date = $mysqli->real_escape_string($_POST['transaction_date']);
    $amount = floatval($_POST['amount']);
    $note = $mysqli->real_escape_string($_POST['note']);
    $type = $mysqli->real_escape_string($_POST['type']);

    $updateSQL = "UPDATE transactions SET 
                  asset_code = '$asset_code',
                  type = '$type',
                  item_code = '$item_code',
                  transaction_date = '$transaction_date',
                  amount = $amount,
                  note = '$note'
                  WHERE id = $id";

    if ($mysqli->query($updateSQL)) {
        $message = "✅ Transaction updated successfully!";
        // Refresh the transaction record
        $sql = "SELECT * FROM transactions WHERE id = $id";
        $res = $mysqli->query($sql);
        $transaction = $res->fetch_assoc();
    } else {
        $message = "⚠️ Error: " . $mysqli->error;
    }
}

// Format the date for display (YYYY-MM-DD)
$dateFormatted = date("Y-m-d", strtotime($transaction['transaction_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Transaction</title>
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
    </script>
</head>
<body>

    <div class="container">
        <!-- Top Menu -->
        <div class="menu">
            <button onclick="window.location.href='index.php'" class="menu-btn">กลับ</button>      
            <button onclick="window.location.href='new_expense.php'" class="menu-btn">รายจ่าย</button>
            <button onclick="window.location.href='new_income.php'" class="menu-btn">รายรับ</button>
            <button onclick="window.location.href='search.php'" class="menu-btn">ค้นหา</button>  

        </div>

        <h2>Edit Transaction</h2>
        <?php if (!empty($message)): ?>
            <p class="message" id="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="edit_transaction.php?id=<?php echo $id; ?>">
            <div class="form-group">
                <label for="asset_code">Asset:</label>
                <select name="asset_code" id="asset_code" required>
                    <option value="">Select an asset</option>
                    <?php foreach ($assets as $asset): ?>
                        <option value="<?php echo $asset['asset_code']; ?>" 
                            <?php if ($transaction['asset_code'] == $asset['asset_code']) echo "selected"; ?>>
                            <?php echo $asset['asset_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="type">Type:</label>
                <select id="type" name="type">
                    <option value="Expense" <?php echo ($transaction['type'] == 'Expense') ? 'selected' : ''; ?>>Expense</option>
                    <option value="Income" <?php echo ($transaction['type'] == 'Income') ? 'selected' : ''; ?>>Income</option>
                </select>
            </div>

            <div class="form-group">
                <label for="item_code">Item:</label>
                <select name="item_code" id="item_code" required>
                    <option value="">Select an item</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item['item_code']; ?>" 
                            <?php if ($transaction['item_code'] == $item['item_code']) echo "selected"; ?>>
                            <?php echo $item['item_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="transaction_date">Transaction Date:</label>
                <input type="date" id="transaction_date" name="transaction_date" value="<?php echo $dateFormatted; ?>" required>
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?php echo htmlspecialchars($transaction['amount']); ?>" required>
            </div>

            <div class="form-group">
                <label for="note">Note:</label>
                <textarea id="note" name="note"><?php echo htmlspecialchars($transaction['note']); ?></textarea>
            </div>

            <button type="submit" class="blue-button">Update Transaction</button>
        </form>
    </div>

</body>
</html>
