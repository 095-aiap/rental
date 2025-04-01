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
$period = $_GET['period'] ?? "all"; // Default to show all records when first loaded
$note_item = $_GET['note_item'] ?? "";

// Fetch unique periods from transactions
$sqlPeriods = "SELECT DISTINCT DATE_FORMAT(transaction_date, '%b %y') AS period FROM transactions ORDER BY transaction_date DESC";
$resPeriods = $mysqli->query($sqlPeriods);
$periods = $resPeriods ? $resPeriods->fetch_all(MYSQLI_ASSOC) : [];

// Build the WHERE clause based on filters
$where_clauses = [];
if (!empty($q)) {
    $qEscaped = $mysqli->real_escape_string($q);
    $where_clauses[] = "(a.asset_name LIKE '%$qEscaped%' OR t.note LIKE '%$qEscaped%' OR i.item_name LIKE '%$qEscaped%')";
}
if (!empty($asset_filter)) {
    $asset_filter = $mysqli->real_escape_string($asset_filter);
    $where_clauses[] = "a.asset_code = '$asset_filter'";
}
if ($period !== "all" && !empty($period)) {
    $period = $mysqli->real_escape_string($period);
    $where_clauses[] = "DATE_FORMAT(t.transaction_date, '%b %y') = '$period'";
}
if (!empty($note_item)) {
    $note_itemEscaped = $mysqli->real_escape_string($note_item);
    $where_clauses[] = "(t.note LIKE '%$note_itemEscaped%' OR i.item_name LIKE '%$note_itemEscaped%')";
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

// Calculate total income and expense
$total_income = 0;
$total_expense = 0;
foreach ($results as $row) {
    if ($row['type'] === 'Income') {
        $total_income += $row['amount'];
    } elseif ($row['type'] === 'Expense') {
        $total_expense += $row['amount'];
    }
}

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
    <script>
        function autoSubmit() {
            document.getElementById('filterForm').submit();
        }
        function resetForm() {
            window.location.href = 'search.php';
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
        <h2>Search Transactions</h2>

        <p><strong>Total Income:</strong> <?php echo number_format($total_income, 2); ?></p>
        <p><strong>Total Expense:</strong> <?php echo number_format($total_expense, 2); ?></p>

        <form method="GET" action="search.php" id="filterForm">
            <div class="form-group">
                <label for="asset_filter">Asset:</label>
                <select name="asset_filter" id="asset_filter" onchange="autoSubmit()">
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
                <input type="text" name="q" id="q" placeholder="Search asset, note, or item" value="<?php echo htmlspecialchars($q); ?>">
            </div>
            <div class="form-group">
                <label for="period">Period:</label>
                <select name="period" id="period" onchange="autoSubmit()">
                    <option value="all">All Records</option>
                    <?php foreach ($periods as $p): ?>
                        <option value="<?php echo htmlspecialchars($p['period']); ?>" <?php echo ($period === $p['period']) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($p['period']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="blue-button">🔎 Search</button>
            <button type="button" class="red-button" onclick="resetForm()">🔄 Reset</button>
        </form>

        <?php if (count($results) > 0): ?>
            <h2>Search Results</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Asset</th>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Date</th>
                        <th style="text-align: right;">Amount</th>
                        <th>Note</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $row_no = 1; foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo $row_no++; ?></td>
                            <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                            <td style="text-align: right;"> <?php echo number_format($row['amount'], 2); ?> </td>
                            <td><?php echo htmlspecialchars($row['note']); ?></td>
                            <td>
                                <a href="edit_transaction.php?id=<?php echo $row['id']; ?>" class="edit-button">Edit</a>
                                <a href="delete_transaction.php?id=<?php echo $row['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this transaction?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
