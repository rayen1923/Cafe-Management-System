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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $cin = $_POST["cin"];
    $birth_date = $_POST["birth_date"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];
    $mail = $_POST["mail"];
    $role = $_POST["role"];
    $daily_wage = $_POST["daily_wage"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $username = $_POST["first_name"] . $_POST["last_name"];
    if (empty($first_name) || empty($last_name) || empty($cin) || empty($birth_date) || empty($address) || empty($phone) || empty($mail) || empty($role) || empty($daily_wage) || empty($password) || empty($username)) {
        echo "Please fill in all the required fields.";
        exit();
    }

    if (isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];

        $stmt = $mysqli->prepare("UPDATE users SET first_name=?, last_name=?, cin=?, birth_date=?, address=?, phone=?, mail=?, role=?, daily_wage=?, password=?, username=? WHERE user_id=?");
        $stmt->bind_param("ssisssssisss", $first_name, $last_name, $cin, $birth_date, $address, $phone, $mail, $role, $daily_wage, $password, $username, $user_id);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: employees.php");
            exit();
        } else {
            echo "Error updating user: " . $mysqli->error;
        }
    } else {
        $stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, cin, birth_date, address, phone, mail, role, daily_wage, password, username) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssssiss", $first_name, $last_name, $cin, $birth_date, $address, $phone, $mail, $role, $daily_wage, $password, $username);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: employees.php");
            exit();
        } else {
            echo "Error creating user: " . $mysqli->error;
        }
    }
}
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userDetails = $result->fetch_assoc();
    $stmt->close();
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
        .center-container {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            width: 50%;
            margin-top: 10%;
            margin-bottom: 1%;
        }
    </style>
</head>
<body>
    <div class="center-container">
        <form action="create_update.php" method="post">
            <h2><?php echo isset($userDetails) ? 'Update User' : 'Create User'; ?></h2>

            <?php if (isset($userDetails)): ?>
                <input type="hidden" name="user_id" value="<?php echo $userDetails['user_id']; ?>">
            <?php endif; ?>

            <label for="first_name" class="form-label">First Name:</label>
            <input type="text" class="form-control" name="first_name" value="<?php echo isset($userDetails) ? $userDetails['first_name'] : ''; ?>" required><br>

            <label for="last_name" class="form-label">Last Name:</label>
            <input type="text" class="form-control" name="last_name" value="<?php echo isset($userDetails) ? $userDetails['last_name'] : ''; ?>" required><br>

            <label for="cin" class="form-label">CIN:</label>
            <input type="number" class="form-control" name="cin" value="<?php echo isset($userDetails) ? $userDetails['cin'] : ''; ?>" required><br>

            <label for="birth_date" class="form-label">Birth Date:</label>
            <input type="date" class="form-control" name="birth_date" value="<?php echo isset($userDetails) ? $userDetails['birth_date'] : ''; ?>" required><br>

            <label for="address" class="form-label">Address:</label>
            <input type="text" class="form-control" name="address" value="<?php echo isset($userDetails) ? $userDetails['address'] : ''; ?>" required><br>

            <label for="phone" class="form-label">Phone:</label>
            <input type="tel" class="form-control" name="phone" value="<?php echo isset($userDetails) ? $userDetails['phone'] : ''; ?>" required><br>

            <label for="mail" class="form-label">Mail:</label>
            <input type="email" class="form-control" name="mail" value="<?php echo isset($userDetails) ? $userDetails['mail'] : ''; ?>" required><br>

            <label for="role" class="form-label">Role:</label><br>
            <select name="role" class="form-select" required>
                <option value="employee" <?php echo isset($userDetails) && $userDetails['role'] === 'Employee' ? 'selected' : ''; ?>>Employee</option>
                <option value="admin" <?php echo isset($userDetails) && $userDetails['role'] === 'Admin' ? 'selected' : ''; ?>>admin</option>
            </select><br>

            <label for="daily_wage" class="form-label">Daily Wage:</label>
            <input type="number" class="form-control" name="daily_wage" value="<?php echo isset($userDetails) ? $userDetails['daily_wage'] : ''; ?>" required><br>

            <label for="password" class="form-label">Password:</label>
            <input type="text" class="form-control" name="password" <?php echo isset($userDetails) ? 'placeholder="Leave blank to keep the current password"' : 'required'; ?>><br>

            <button type="submit" class="btn btn-primary" name="<?php echo isset($userDetails) ? 'update' : 'create'; ?>">
                <?php echo isset($userDetails) ? 'Update' : 'Create'; ?>
            </button>
        </form>
    </div>
</body>
</html>
<?php
    ob_end_flush();
?>