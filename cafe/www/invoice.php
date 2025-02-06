<?php
ob_start();
session_start();
include "nav.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}

require_once('Config.php');
$mysqli = new mysqli(db_host, db_user, db_password, db_database);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $mysqli->real_escape_string($_POST['product']);
    $supplier_id = $mysqli->real_escape_string($_POST['supplier']);
    $date = date("Y-m-d");
    $quantity = $mysqli->real_escape_string($_POST['quantity']);
    $total_amount = $mysqli->real_escape_string($_POST['total_amount']);

    $insert_query = "INSERT INTO invoices (produit_id, supplier_id, date, quantity, total_amount) VALUES ('$product_id', '$supplier_id', '$date', '$quantity', '$total_amount')";
    
    $update_query = "UPDATE produits SET quantity_stock = quantity_stock + $quantity WHERE produit_id = '$product_id'";
    
    $mysqli->begin_transaction();
    $error = false;
    if ($mysqli->query($insert_query) !== TRUE) {
        $error = true;
        echo "Error inserting new invoice: " . $mysqli->error;
    }
    if ($mysqli->query($update_query) !== TRUE) {
        $error = true;
        echo "Error updating quantity in products table: " . $mysqli->error;
    }

    if (!$error) {
        $mysqli->commit();
        header("Location: ".$_SERVER['PHP_SELF']);
    } else {
        $mysqli->rollback();
    }    

    $mysqli->close();
    exit();
}
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
        .container {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
        }
        .modal-container {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
        }
        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close-btn:hover,
        .close-btn:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .close-btn1 {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Invoices</h1>
        <form method="GET" action="" class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="product">Product:</label>
                    <select class="form-control" id="product" name="product">
                        <option value="">Select Product</option>
                        <?php
                        $query_products = "SELECT * FROM produits";
                        $result_products = $mysqli->query($query_products);
                        while ($row_product = $result_products->fetch_assoc()) {
                            echo "<option value='" . $row_product['produit_id'] . "'>" . $row_product['produit_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="supplier">Supplier:</label>
                    <select class="form-control" id="supplier" name="supplier">
                        <option value="">Select Supplier</option>
                        <?php
                        $query_suppliers = "SELECT * FROM suppliers";
                        $result_suppliers = $mysqli->query($query_suppliers);
                        while ($row_supplier = $result_suppliers->fetch_assoc()) {
                            echo "<option value='" . $row_supplier['supplier_id'] . "'>" . $row_supplier['supplier_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                </div>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary btn-block"><img src='img\find.png' title='Search' style='height: 24px; width: 24px;'></button>
            </div>
        </form>

        <?php
        $query = "SELECT i.*, s.supplier_name, p.produit_name 
        FROM invoices i
        INNER JOIN suppliers s ON i.supplier_id = s.supplier_id
        INNER JOIN produits p ON i.produit_id = p.produit_id";

        if (isset($_GET['product']) || isset($_GET['supplier']) || (isset($_GET['start_date']) && isset($_GET['end_date']))) {
        $conditions = " WHERE ";
        $conditions_arr = [];
        if (!empty($_GET['product'])) {
            $product_id = $mysqli->real_escape_string($_GET['product']);
            $conditions_arr[] = "p.produit_id = '$product_id'";
        }
        if (!empty($_GET['supplier'])) {
            $supplier_id = $mysqli->real_escape_string($_GET['supplier']);
            $conditions_arr[] = "s.supplier_id = '$supplier_id'";
        }
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $start_date = $mysqli->real_escape_string($_GET['start_date']);
            $end_date = $mysqli->real_escape_string($_GET['end_date']);
            $conditions_arr[] = "i.date BETWEEN '$start_date' AND '$end_date'";
        }
        $conditions .= implode(" AND ", $conditions_arr);
        $query .= $conditions;
        }

        $result = $mysqli->query($query);
        
        if ($result->num_rows > 0) {
            echo "<table class='table'>";
            echo "<thead><tr><th>Supplier Name</th><th>Product Name</th><th>Date</th><th>Quantity</th><th>Total Amount</th></tr></thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['supplier_name'] . "</td>";
                echo "<td>" . $row['produit_name'] . "</td>";
                echo "<td>" . $row['date'] . "</td>";
                echo "<td>" . $row['quantity'] . "</td>";
                echo "<td>" . $row['total_amount'] . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "No invoices found.";
        }
        $mysqli->close();
        ?>
        <button id="openModalBtn" class="btn btn-primary"><img src='img\plus.png' title='add User' style='height: 24px; width: 24px;'></button>
    </div>
    <div id="openModal" class="modal-container">
        <div class="modal-content">
            <a href="#" class="close-btn">&times;</a>
            <h2>Add Invoice</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="product">Product:</label>
                    <select class="form-control" id="product" name="product">
                        <option value="">Select Product</option>
                        <?php
                        require_once('Config.php');
                        $mysqli = new mysqli(db_host, db_user, db_password, db_database);
                        $query_products = "SELECT * FROM produits";
                        $result_products = $mysqli->query($query_products);
                        while ($row_product = $result_products->fetch_assoc()) {
                            echo "<option value='" . $row_product['produit_id'] . "'>" . $row_product['produit_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="supplier">Supplier:</label>
                    <select class="form-control" id="supplier" name="supplier">
                        <option value="">Select Supplier</option>
                        <?php
                        require_once('Config.php');
                        $mysqli = new mysqli(db_host, db_user, db_password, db_database);
                        $query_suppliers = "SELECT * FROM suppliers";
                        $result_suppliers = $mysqli->query($query_suppliers);
                        while ($row_supplier = $result_suppliers->fetch_assoc()) {
                            echo "<option value='" . $row_supplier['supplier_id'] . "'>" . $row_supplier['supplier_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" required>
                </div>
                <div class="form-group">
                    <label for="total_amount">Total Amount:</label>
                    <input type="number" class="form-control" id="total_amount" name="total_amount" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('openModalBtn').addEventListener('click', function() {
            document.getElementById('openModal').style.display = 'block';
        });

        document.getElementsByClassName('close-btn')[0].addEventListener('click', function() {
            document.getElementById('openModal').style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == document.getElementById('openModal')) {
                document.getElementById('openModal').style.display = 'none';
            }
        });
    </script>
</body>
</html>
<?php
    ob_end_flush();
?>