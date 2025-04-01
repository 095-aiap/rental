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
    $pay_code = $_POST['pay_code'] ?? '';
    $pay_name = $_POST['pay_name'] ?? '';
    $pay_description = $_POST['pay_description '] ?? '';    

    if ($action === 'add' && !empty($pay_code) && !empty($pay_name) && !empty($pay_description)) {
        $stmt = $mysqli->prepare("INSERT INTO pays (pay_code, pay_name, pay_description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $pay_code, $pay_name, $pay_description );
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'edit' && !empty($pay_code) && !empty($pay_name) && !empty($pay_description)) {
        $stmt = $mysqli->prepare("UPDATE pays SET pay_name = ?, pay_description = ? WHERE pay_code = ?");
        $stmt->bind_param("sss", $pay_name, $pay_code, $pay_description);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete' && !empty($pay_code)) {
        $stmt = $mysqli->prepare("DELETE FROM pays WHERE pay_code = ?");
        $stmt->bind_param("s", $pay_code);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch pays
$search = $_GET['search'] ?? "";
$where = !empty($search) ? " WHERE pay_name LIKE '%" . $mysqli->real_escape_string($search) . "%'" : "";
$sql = "SELECT * FROM pays" . $where;
$res = $mysqli->query($sql);
$pays = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Pays</title>
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
        <h2>Manage Pays</h2>
        
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search pays..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">🔍 Search</button>
        </form>
        
        <h3>Add/Edit Pay</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <input type="text" name="pay_code" placeholder="Pay Code (3 characters)" required>
            <input type="text" name="pay_name" placeholder="Pay Name" required>
            <input type="text" name="pay_description" placeholder="Pay Description">
            <button type="submit">➕ Add</button>
        </form>
        
        <h3>All Pays</h3>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Pay Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pays as $pay): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pay['pay_code']); ?></td>
                        <td><?php echo htmlspecialchars($pay['pay_name']); ?></td>
                        <td><?php echo htmlspecialchars($pay['pay_description']); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="pay_code" value="<?php echo htmlspecialchars($pay['pay_code']); ?>">
                                <input type="text" name="pay_name" value="<?php echo htmlspecialchars($pay['pay_name']); ?>" required>
                                <input type="text" name="pay_description" value="<?php echo htmlspecialchars($pay['pay_description']); ?>">
                                <button type="submit">✏ Edit</button>
                            </form>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="pay_code" value="<?php echo htmlspecialchars($pay['pay_code']); ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this pay?');">🗑 Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
