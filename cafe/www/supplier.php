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

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_supplier'])) {
    $supplier_name = $_POST['supplier_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $sql = "INSERT INTO suppliers (supplier_name, phone, email) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sss", $supplier_name, $phone, $email);

    if ($stmt->execute()) {
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $mysqli->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_supplier'])) {
    $supplier_id = $_POST['supplier_id'];
    $supplier_name = $_POST['supplier_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $sql = "UPDATE suppliers SET supplier_name=?, phone=?, email=? WHERE supplier_id=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssi", $supplier_name, $phone, $email, $supplier_id);

    if ($stmt->execute()) {
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error updating record: " . $mysqli->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete_supplier'])) {
    $supplier_id = $_GET['delete_supplier'];

    $sql = "DELETE FROM suppliers WHERE supplier_id=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $supplier_id);

    if ($stmt->execute()) {
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . $mysqli->error;
    }
}

$suppliers = [];
$sql = "SELECT * FROM suppliers";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers</title>
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
        <h1>Suppliers</h1>
        <table class='table table-hover'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><?php echo $supplier["supplier_id"]; ?></td>
                        <td><?php echo $supplier["supplier_name"]; ?></td>
                        <td><?php echo $supplier["phone"]; ?></td>
                        <td><?php echo $supplier["email"]; ?></td>
                        <td>
                            <button type='button' class='btn btn-success' onclick='openUpdateModal(<?php echo $supplier["supplier_id"]; ?>, "<?php echo $supplier["supplier_name"]; ?>", "<?php echo $supplier["phone"]; ?>", "<?php echo $supplier["email"]; ?>")'><img src='img\update.png' title='Update supplier' style='height: 24px; width: 24px;'></button>
                            <a href='delete_supplier.php?supplier_id=<?php echo $supplier["supplier_id"]; ?>'><button type='button' class='btn btn-danger'><img src='img\delete.png' title='Delete supplier' style='height: 24px; width: 24px;'></button></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button id="openModalBtn" class="btn btn-primary"><img src='img\add.png' title='add qte' style='height: 24px; width: 24px;'></button>
    </div>
    <div id="openModal" class="modal-container">
        <div class="modal-content">
            <a href="#" class="close-btn">&times;</a>
            <h2>Add Supplier</h2>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="needs-validation" novalidate>
                <div class="form-group">
                    <label for="add_supplier_name">Name:</label>
                    <input type="text" class="form-control" id="add_supplier_name" name="supplier_name" required>
                    <div class="invalid-feedback">Please provide a name.</div>
                </div>
                <div class="form-group">
                    <label for="add_phone">Phone:</label>
                    <input type="text" class="form-control" id="add_phone" name="phone" required>
                    <div class="invalid-feedback">Please provide a phone number.</div>
                </div>
                <div class="form-group">
                    <label for="add_email">Email:</label>
                    <input type="email" class="form-control" id="add_email" name="email" required>
                    <div class="invalid-feedback">Please provide a valid email.</div>
                </div>
                <button type="submit" class="btn btn-primary" name="add_supplier">Add</button>
            </form>
        </div>
    </div>

    <div id="openModal1" class="modal-container">
        <div class="modal-content">
            <a href="#" class="close-btn1">&times;</a>
            <h2>Update Supplier</h2>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="updateForm" class="needs-validation" novalidate>
                <input type="hidden" id="update_supplier_id" name="supplier_id">
                <div class="form-group">
                    <label for="update_supplier_name">Name:</label>
                    <input type="text" class="form-control" id="update_supplier_name" name="supplier_name" required>
                    <div class="invalid-feedback">Please provide a name.</div>
                </div>
                <div class="form-group">
                    <label for="update_phone">Phone:</label>
                    <input type="text" class="form-control" id="update_phone" name="phone" required>
                    <div class="invalid-feedback">Please provide a phone number.</div>
                </div>
                <div class="form-group">
                    <label for="update_email">Email:</label>
                    <input type="email" class="form-control" id="update_email" name="email" required>
                    <div class="invalid-feedback">Please provide a valid email.</div>
                </div>
                <button type="submit" class="btn btn-primary" name="update_supplier">Update</button>
            </form>
        </div>
    </div>

    <script>
        function openUpdateModal(id, name, phone, email) {
            document.getElementById('update_supplier_id').value = id;
            document.getElementById('update_supplier_name').value = name;
            document.getElementById('update_phone').value = phone;
            document.getElementById('update_email').value = email;
            document.getElementById('openModal1').style.display = 'block';
        }

        document.getElementById('openModalBtn').addEventListener('click', function() {
            document.getElementById('openModal').style.display = 'block';
        });

        document.getElementsByClassName('close-btn')[0].addEventListener('click', function() {
            document.getElementById('openModal').style.display = 'none';
        });

        document.getElementsByClassName('close-btn1')[0].addEventListener('click', function() {
            document.getElementById('openModal1').style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == document.getElementById('openModal')) {
                document.getElementById('openModal').style.display = 'none';
            } else if (event.target == document.getElementById('openModal1')) {
                document.getElementById('openModal1').style.display = 'none';
            }
        });
    </script>
</body>
</html>
<?php
ob_end_flush();
?>
