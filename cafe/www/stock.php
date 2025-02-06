<?php
ob_start();
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}
include "nav.php";

require_once('Config.php');
$mysqli = new mysqli(db_host, db_user, db_password, db_database);

$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['productName']) && isset($_POST['quantity']) && isset($_POST['unit'])) {
        $productName = $_POST['productName'];
        $quantity = $_POST['quantity'];
        $unit = $_POST['unit'];
        $stmt = $mysqli->prepare("INSERT INTO produits (produit_name, quantity_stock, unit) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $productName, $quantity, $unit);
        if ($stmt->execute()) {
            header("Location: stock.php");
            exit();
        } else {
            echo "<script>alert('Error adding product: " . $mysqli->error . "');</script>";
        }
        $stmt->close();
    } elseif(isset($_POST['operation']) && isset($_POST['quantity']) && isset($_POST['productId'])) {
        $operation = $_POST['operation'];
        $quantity = $_POST['quantity'];
        $productId = $_POST['productId'];

        if($operation === "+") {
            $stmt = $mysqli->prepare("UPDATE produits SET quantity_stock = quantity_stock + ? WHERE produit_id = ?");
            $stmt->bind_param("ii", $quantity, $productId);
            if ($stmt->execute()) {
                header("Location: stock.php");
                exit();
            } else {
                echo "<script>alert('Error updating quantity: " . $mysqli->error . "');</script>";
            }
            $stmt->close();
        } elseif($operation === "-") {
            $stmt_check = $mysqli->prepare("SELECT quantity_stock FROM produits WHERE produit_id = ?");
            $stmt_check->bind_param("i", $productId);
            $stmt_check->execute();
            $stmt_check->store_result();
            $stmt_check->bind_result($currentQuantity);
            $stmt_check->fetch();
            $stmt_check->close();
            if($currentQuantity - $quantity < 0) {
                $errorMsg = "Error: Subtracting quantity will result in negative value";
            } else {
                $stmt = $mysqli->prepare("UPDATE produits SET quantity_stock = quantity_stock - ? WHERE produit_id = ?");
                $stmt->bind_param("ii", $quantity, $productId);
                if ($stmt->execute()) {
                    header("Location: stock.php");
                    exit();
                } else {
                    echo "<script>alert('Error updating quantity: " . $mysqli->error . "');</script>";
                }
                $stmt->close();
            }
        }
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAFE</title>
    <link rel="stylesheet" href="lib\bootstrap.min.css">
    <link rel="stylesheet" href="lib\docs.css">
    <script src="lib\bootstrap.bundle.min.js"></script>
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

        .close-btn1:hover,
        .close-btn1:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        #openModal:target {
            display: block;
        }
        #openModal1:target {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Products in stock</h1>
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>
        <table class='table table-hover'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once('Config.php');
                $mysqli = new mysqli(db_host, db_user, db_password, db_database);

                $sql = "SELECT produit_id, produit_name, quantity_stock, unit FROM produits";
                $result = $mysqli->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["produit_id"] . "</td>";
                        echo "<td>" . $row["produit_name"] . "</td>";
                        echo "<td>" . $row["quantity_stock"] . " " .$row["unit"]. "</td>";
                        echo "<td>
                                <form method='post' action='stock.php'>
                                    <input type='hidden' name='productId' value='" . $row["produit_id"] . "'>
                                    <button type='button' class='btn btn-success' onclick='openUpdateModal(" . $row["produit_id"] . ")'><img src='img\update.png' title='Update qte' style='height: 24px; width: 24px;'></button>
                                    <a href='delete_product.php?produit_id=" . $row["produit_id"] . "'><button type='button' class='btn btn-danger'><img src='img\delete.png' title='delete produit' style='height: 24px; width: 24px;'></button></a>
                                </form>
                                </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No products found.</td></tr>";
                }
                $mysqli->close();
                ?>
            </tbody>
        </table>
        <button id="openModalBtn" class="btn btn-primary"><img src='img\plus.png' title='add User' style='height: 24px; width: 24px;'></button>
    </div>
        <div id="openModal" class="modal-container">
            <div class="modal-content">
                <a href="#" class="close-btn">&times;</a>
                <h2>New Product</h2>
                <form method="post" action="stock.php">
                    <div class="form-group">
                        <label for="productName">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="productName" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <input type="text" class="form-control" id="unit" name="unit" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>
        <div id="openModal1" class="modal-container">
            <div class="modal-content">
                <a href="#" class="close-btn1">&times;</a>
                <h2>Update QTE</h2>
                <form method="post" action="stock.php" id="updateForm">
                    <input type="hidden" name="productId" id="productId" value="">
                    <label for="quantity">Select +/-:</label>
                    <select name="operation" id="operation">
                        <option value="+">Add</option>
                        <option value="-">Subtract</option>
                    </select>
                    <label for="quantity">Enter Quantity:</label>
                    <input type="number" name="quantity" id="updateQuantity" required>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
        <script>
            function openUpdateModal(productId) {
                document.getElementById('productId').value = productId;
                document.getElementById('openModal1').style.display = 'block';
            }

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

            document.getElementsByClassName('close-btn1')[0].addEventListener('click', function() {
                document.getElementById('openModal1').style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target == document.getElementById('openModal1')) {
                    document.getElementById('openModal1').style.display = 'none';
                }
            });
        </script>
</body>
</html>
<?php
    ob_end_flush();
?>
