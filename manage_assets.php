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
    $asset_code = $_POST['asset_code'] ?? '';
    $asset_name = $_POST['asset_name'] ?? '';
    $image_url = "";

    // Handle file upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        }
    }

    if ($action === 'add' && !empty($asset_code) && !empty($asset_name)) {
        $stmt = $mysqli->prepare("INSERT INTO assets (asset_code, asset_name, image_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $asset_code, $asset_name, $image_url);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'edit' && !empty($asset_code) && !empty($asset_name)) {
        $stmt = $mysqli->prepare("UPDATE assets SET asset_name = ?, image_url = ? WHERE asset_code = ?");
        $stmt->bind_param("sss", $asset_name, $image_url, $asset_code);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete' && !empty($asset_code)) {
        $stmt = $mysqli->prepare("DELETE FROM assets WHERE asset_code = ?");
        $stmt->bind_param("s", $asset_code);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch assets
$sql = "SELECT * FROM assets";
$res = $mysqli->query($sql);
$assets = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Assets</title>
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
        <h2>Manage Assets</h2>
        
        <h3>Add/Edit Asset</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <input type="text" name="asset_code" placeholder="Asset Code (3 characters)" required>
            <input type="text" name="asset_name" placeholder="Asset Name" required>
            <input type="file" name="image" accept="image/*">
            <button type="submit">➕ Add</button>
        </form>
        
        <h3>All Assets</h3>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Asset Name</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assets as $asset): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($asset['asset_code']); ?></td>
                        <td><?php echo htmlspecialchars($asset['asset_name']); ?></td>
                        <td>
                            <?php if (!empty($asset['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($asset['image_url']); ?>" alt="Asset Image" width="50">
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="" enctype="multipart/form-data" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="asset_code" value="<?php echo htmlspecialchars($asset['asset_code']); ?>">
                                <input type="text" name="asset_name" value="<?php echo htmlspecialchars($asset['asset_name']); ?>" required>
                                <input type="file" name="image" accept="image/*">
                                <button type="submit">✏ Edit</button>
                            </form>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="asset_code" value="<?php echo htmlspecialchars($asset['asset_code']); ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this asset?');">🗑 Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
