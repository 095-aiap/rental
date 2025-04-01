<?php
// Database connection
$mysqli = new mysqli("localhost", "bewith_businesscard", "WSrRZHEmfjKzssJ5v9Xf", "bewith_businesscard");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch total income and expense per asset
$sql = "SELECT a.asset_code, 
               SUM(CASE WHEN t.type = 'Income' THEN t.amount ELSE 0 END) AS total_income,
               SUM(CASE WHEN t.type = 'Expense' THEN t.amount ELSE 0 END) AS total_expense
        FROM transactions t
        JOIN assets a ON t.asset_code = a.asset_code
        GROUP BY a.asset_code";
$res = $mysqli->query($sql);
$data = $res->fetch_all(MYSQLI_ASSOC);

$asset_codes = [];
$income_values = [];
$expense_values = [];
$total_income = 0;
$total_expense = 0;
foreach ($data as $row) {
    $asset_codes[] = $row['asset_code'];
    $income_values[] = $row['total_income'];
    $expense_values[] = $row['total_expense'];
    $total_income += $row['total_income'];
    $total_expense += $row['total_expense'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ระบบจัดการรายรับ-รายจ่าย</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
        }
        .chart-box {
            width: 80%;
            height: 400px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🏠 แดชบอร์ดการจัดการเช่า</h1>
            <p>ยินดีต้อนรับสู่ระบบจัดการการเช่าของคุณ</p>
        </header>

        <nav class="menu">   
            <button onclick="window.location.href='new_expense.php'" class="menu-btn">รายจ่าย</button>
            <button onclick="window.location.href='new_income.php'" class="menu-btn">รายรับ</button>
            <button onclick="window.location.href='search.php'" class="menu-btn">ค้นหา</button>  
        </nav>

        <section>
            <h2>📊 ภาพรวมทางการเงิน</h2>
            <p>จัดการการเงินสำหรับการเช่าได้อย่างมีประสิทธิภาพด้วยฟีเจอร์เหล่านี้:</p>
            <ul>
                <li>📌 บันทึกรายจ่ายใหม่สำหรับอสังหาริมทรัพย์ให้เช่าของคุณ</li>
                <li>📌 บันทึกรายรับจากการชำระเงินที่ได้รับ</li>
                <li>📌 ค้นหา แก้ไข และตรวจสอบธุรกรรมทั้งหมดได้อย่างง่ายดาย</li>
            </ul>

            <br>    
            <nav class="menu">   
                <button onclick="window.location.href='manage_items.php'" class="menu-btn">เพิ่มรายการรับ-จ่าย</button>  <br><br>
                <button onclick="window.location.href='manage_pays.php'" class="menu-btn">เพิ่มผู้จ่ายเงิน</button>  <br><br>                
                <button onclick="window.location.href='manage_assets.php'" class="menu-btn">เพิ่มสินทรัพย์/โครงการ</button>
            </nav>
                            
            <div class="chart-container">
                <div class="chart-box">
                    <canvas id="financeChart"></canvas>
                </div>
                <div class="chart-box">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </section>

        <script>
            const ctxBar = document.getElementById('financeChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($asset_codes); ?>,
                    datasets: [
                        {
                            label: 'รายรับ',
                            data: <?php echo json_encode($income_values); ?>,
                            backgroundColor: 'rgba(0, 123, 255, 0.5)',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'รายจ่าย',
                            data: <?php echo json_encode($expense_values); ?>,
                            backgroundColor: 'rgba(0, 86, 179, 0.5)',
                            borderColor: 'rgba(0, 86, 179, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'รายรับและรายจ่ายต่อโครงการ' }
                    }
                }
            });

            const ctxPie = document.getElementById('pieChart').getContext('2d');
            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['รายรับ', 'รายจ่าย'],
                    datasets: [{
                        data: [<?php echo $total_income; ?>, <?php echo $total_expense; ?>],
                        backgroundColor: ['rgba(0, 123, 255, 0.5)', 'rgba(0, 86, 179, 0.5)']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'สัดส่วนรายรับและรายจ่าย' }
                    }
                }
            });
        </script>

        <footer>
            <p>&copy; <?php echo date("Y"); ?> ระบบจัดการเช่า</p>
        </footer>
    </div>
</body>
</html>
