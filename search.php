<?php
// Establish database connection
$mysqli = new mysqli("localhost", "bewith_businesscard", "WSrRZHEmfjKzssJ5v9Xf", "bewith_businesscard");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get filter values
$asset_filter = $_GET['asset_filter'] ?? "";
$q = $_GET['q'] ?? "";

// Build the WHERE clause based on filters
$where_clauses = [];
if (!empty($q)) {
    $qEscaped = $mysqli->real_escape_string($q);
    $where_clauses[] = "(a.asset_name LIKE '%$qEscaped%' OR t.note LIKE '%$qEscaped%')";
}
if (!empty($asset_filter)) {
    $asset_filter = $mysqli->real_escape_string($asset_filter);
    $where_clauses[] = "a.asset_code = '$asset_filter'";
}

$where = count($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch transactions
$sql = "SELECT t.*, a.asset_name, i.item_name 
        FROM transactions t
        JOIN assets a ON t.asset_code = a.asset_code
        JOIN items i ON t.item_code = i.item_code" . $where;
$res = $mysqli->query($sql);

if (!$res) {
    die("Error executing query: " . $mysqli->error);
}

$results = $res->fetch_all(MYSQLI_ASSOC);

// Fetch all assets for the dropdown
$sqlAssets = "SELECT asset_code, asset_name FROM assets";
$resAssets = $mysqli->query($sqlAssets);
$assets = $resAssets ? $resAssets->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Transactions</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
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

        <h2>Search Transactions</h2>

        <form method="GET" action="search.php">
            <div class="form-group">
                <label for="asset_filter">Asset:</label>
                <select name="asset_filter" id="asset_filter">
                    <option value="">All Assets</option>
                    <?php foreach ($assets as $asset): ?>
                        <option value="<?php echo $asset['asset_code']; ?>" <?php echo ($asset_filter === $asset['asset_code']) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($asset['asset_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="q">Keyword:</label>
                <input type="text" name="q" id="q" placeholder="Enter asset name or note" value="<?php echo htmlspecialchars($q); ?>">
            </div>

            <button type="submit" class="blue-button">🔎 Search</button>
        </form>

        <?php if (!empty($q) || !empty($asset_filter) || count($results) > 0): ?>
            <h2>Search Results</h2>
            <?php if (count($results) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Type</th>
                            <th>Item</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['type']); ?></td>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                                <td><?php echo number_format($row['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['note']); ?></td>
                                <td>
                                    <a href="edit_transaction.php?id=<?php echo $row['id']; ?>" class="edit-button">✏ Edit</a>
                                    <a href="delete_transaction.php?id=<?php echo $row['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this transaction?');">🗑 Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No results found.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Please use the filters above or click "Search" to display all records.</p>
        <?php endif; ?>
    </div>
</body>
</html>
