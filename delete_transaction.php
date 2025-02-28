<?php
// delete_transaction.php
$mysqli = new mysqli("localhost", "bewith_businesscard", "WSrRZHEmfjKzssJ5v9Xf", "bewith_businesscard");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Transaction ID not provided.");
}
$id = intval($_GET['id']);

$sql = "DELETE FROM transactions WHERE id = $id";
if ($mysqli->query($sql)) {
    $message = "Transaction deleted successfully.";
} else {
    $message = "Error deleting transaction: " . $mysqli->error;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete Transaction</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Top Menu -->
            <a href="index.php" class="button">กลับ</a>
            <a href="new_expense.php" class="button">รายจ่าย</a>
            <a href="new_income.php" class="button">รายรับ</a>
            <a href="search.php" class="button">ค้นห่า</a>
        </div>
        <h1>Delete Transaction</h1>
        <p><?php echo $message; ?></p>
        <p><a href="search.php" class="button">Return to Search</a></p>
    </div>
</body>
</html>
