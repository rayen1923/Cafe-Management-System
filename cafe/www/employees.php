<?php
session_start();

/if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}
include "nav.php";

require_once('Config.php');
$mysqli = new mysqli(db_host, db_user, db_password, db_database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

function getUsers($mysqli, $search_query = null) {
    if ($search_query !== null) {
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE first_name LIKE ? AND role != 'admin'");
        $search_param = "%$search_query%";
        $stmt->bind_param("s", $search_param);
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE role != 'admin'");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<table class='table table-hover'>";
        echo    "<tr>
                    <th>#</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>CIN</th>
                    <th>Birth Date</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Mail</th>
                    <th>Role</th>
                    <th>Daily Wage</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo    "<td>" . $row["user_id"] . "</td>";
            echo    "<td>" . $row["first_name"] . "</td>";
            echo    "<td>" . $row["last_name"] . "</td>";
            echo    "<td>" . $row["cin"] . "</td>";
            echo    "<td>" . $row["birth_date"] . "</td>";
            echo    "<td>" . $row["address"] . "</td>";
            echo    "<td>" . $row["phone"] . "</td>";
            echo    "<td>" . $row["mail"] . "</td>";
            echo    "<td>" . $row["role"] . "</td>";
            echo    "<td>" . $row["daily_wage"] . "</td>";
            echo    "<td>" . $row["username"] . "</td>";
            echo    "<td>*******</td>";
            echo    "<td>
                        <a href='create_update.php?user_id=" . $row["user_id"] . "'><button type='button' class='btn btn-success'><img src='img\update.png' title='Update User' style='height: 24px; width: 24px;'></button></a>
                        <a href='delete.php?user_id=" . $row["user_id"] . "'><button type='button' class='btn btn-danger'><img src='img\delete.png' title='delete User' style='height: 24px; width: 24px;'></button></a>
                        <a href='report.php?user_id=" . $row["user_id"] . "'><button type='button' class='btn btn-info'><img src='img\a3in.png' title='view profile' style='height: 24px; width: 24px;'></button></a>
                    </td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<a href='create_update.php'><button type='button' class='btn btn-primary'><img src='img\add.png' title='add User' style='height: 24px; width: 24px;'></button></a>";
    } else {
        if ($search_query !== null) {
            echo "No users found with the given first name.";
        } else {
            echo "No users found";
        }
        echo "<br><a href='create_update.php'><button type='button' class='btn btn-primary'><img src='img\add.png' title='add User' style='height: 24px; width: 24px;'></button></a>";
    }

    $stmt->close();
}
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
        .center-container {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="center-container">
        <h1>Employees</h1>
        <form method="GET" action="">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Search by First Name" name="search_query">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit"><img src='img\find.png' title='Search' style='height: 24px; width: 24px;'></button>
                </div>
            </div>
        </form>
        <?php
            if (isset($_GET['search_query'])) {
                $search_query = $_GET['search_query'];
                getUsers($mysqli, $search_query);
            } else {
                getUsers($mysqli);
            }
            
            $mysqli->close();
        ?>
    </div>
</body>
</html>
