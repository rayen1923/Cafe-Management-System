<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}
include "nav.php";

require_once('Config.php');
$mysqli = new mysqli(db_host, db_user, db_password, db_database);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$userCountQuery = "SELECT COUNT(*) AS user_count FROM users WHERE role != 'admin'";
$userCountResult = $mysqli->query($userCountQuery);
$userCountRow = $userCountResult->fetch_assoc();
$userCount = $userCountRow['user_count'];

$currentMonth = date('Y-m');
$totalInvoiceQuery = "SELECT SUM(total_amount) AS total_amount FROM invoices WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
$totalInvoiceResult = $mysqli->query($totalInvoiceQuery);
$totalInvoiceRow = $totalInvoiceResult->fetch_assoc();
$totalInvoiceAmount = $totalInvoiceRow['total_amount'];

$totalWageQuery = "SELECT SUM(u.daily_wage) AS total_wage 
                   FROM users u 
                   INNER JOIN shifts s ON u.user_id = s.user_id 
                   WHERE MONTH(s.clock_in) = MONTH(CURRENT_DATE()) 
                   AND YEAR(s.clock_in) = YEAR(CURRENT_DATE())";
$totalWageResult = $mysqli->query($totalWageQuery);
$totalWageRow = $totalWageResult->fetch_assoc();
$totalWage = $totalWageRow['total_wage'];


$productQuery = "SELECT * FROM produits WHERE quantity_stock < 10";
$productResult = $mysqli->query($productQuery);

$yesterday = date('Y-m-d', strtotime("-1 days"));
$absentUsersQuery = "SELECT u.first_name, u.last_name FROM users u LEFT JOIN shifts s ON u.user_id = s.user_id WHERE s.clock_in < '$yesterday 00:00:00' OR s.clock_out > '$yesterday 23:59:59' GROUP BY u.user_id";
$absentUsersResult = $mysqli->query($absentUsersQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAFE</title>
    <link rel="stylesheet" href="lib/bootstrap.min.css">
    <link rel="stylesheet" href="lib/docs.css">
    <script src="lib/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-image: url('img/bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            margin: 10px;
            width: 18rem;
        }
    </style>
</head>
<body>

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Number of employees </h5>
                    <p class="card-text"><?php echo $userCount; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Amount of Invoices </h5>
                    <p class="card-text"><?php echo $totalInvoiceAmount; ?> D</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Wage for employees</h5>
                    <p class="card-text"><?php echo $totalWage; ?> D</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">coming soon</h5>
                    <p class="card-text">coming soon</p>
                </div>
            </div>
        </div>
    
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Alerts</h4>
            <ul>
                <?php 
                if ($productResult->num_rows > 0) {
                    while ($productRow = $productResult->fetch_assoc()) : ?>
                        <li><?php echo $productRow['produit_name']; ?> - Quantity: <?php echo $productRow['quantity_stock']; ?></li>
                    <?php endwhile;
                } else {
                    echo "<li>No alerts found for stock.</li>";
                }
                if($absentUsersResult->num_rows > 0){
                    while ($absentUser = $absentUsersResult->fetch_assoc()) {
                        echo "<li>{$absentUser['first_name']} {$absentUser['last_name']} was absent yesterday</li>";
                    }
                } else {
                    echo "<li>No alerts found for employee.</li>";
                }
                ?>
            </ul>
        </div>
</div>
</body>
</html>
