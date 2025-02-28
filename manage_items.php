<?php
// Database connection
$mysqli = new mysqli("localhost", "bewith_businesscard", "WSrRZHEmfjKzssJ5v9Xf", "bewith_businesscard");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $item_code = $_POST['item_code'] ?? '';
    $item_name = $_POST['item_name'] ?? '';
    $item_type = $_POST['item_type'] ?? '';

    if ($action === 'add' && !empty($item_code) && !empty($item_name) && !empty($item_type)) {
        $stmt = $mysqli->prepare("INSERT INTO items (item_code, item_name, type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $item_code, $item_name, $item_type);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'edit' && !empty($item_code) && !empty($item_name) && !empty($item_type)) {
        $stmt = $mysqli->prepare("UPDATE items SET item_name = ?, type = ? WHERE item_code = ?");
        $stmt->bind_param("sss", $item_name, $item_type, $item_code);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete' && !empty($item_code)) {
        $stmt = $mysqli->prepare("DELETE FROM items WHERE item_code = ?");
        $stmt->bind_param("s", $item_code);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch items
$search = $_GET['search'] ?? "";
$where = !empty($search) ? " WHERE item_name LIKE '%" . $mysqli->real_escape_string($search) . "%'" : "";
$sql = "SELECT * FROM items" . $where;
$res = $mysqli->query($sql);
$items = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Items</title>
    <link rel="stylesheet" href="css/style.css">
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
        <h2>Manage Items</h2>
        
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">🔍 Search</button>
        </form>
        
        <h3>Add/Edit Item</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <input type="text" name="item_code" placeholder="Item Code (3 characters)" required>
            <input type="text" name="item_name" placeholder="Item Name" required>
            <select name="item_type" required>
                <option value="">Select Type</option>
                <option value="Expense">Expense</option>
                <option value="Income">Income</option>
            </select>
            <button type="submit">➕ Add</button>
        </form>
        
        <h3>All Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Item Name</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['type']); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="item_code" value="<?php echo htmlspecialchars($item['item_code']); ?>">
                                <input type="text" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                                <select name="item_type" required>
                                    <option value="Expense" <?php echo ($item['type'] === 'Expense') ? 'selected' : ''; ?>>Expense</option>
                                    <option value="Income" <?php echo ($item['type'] === 'Income') ? 'selected' : ''; ?>>Income</option>
                                </select>
                                <button type="submit">✏ Edit</button>
                            </form>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="item_code" value="<?php echo htmlspecialchars($item['item_code']); ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this item?');">🗑 Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
