<?php
    require_once('Config.php');
    $mysqli = new mysqli(db_host, db_user, db_password, db_database);
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    session_start();
    $error_message = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $stmt = $mysqli->prepare("SELECT user_id, password, role FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                $_SESSION['user_id'] = $user["user_id"];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $user["role"];
                if ($user["role"] == "admin") {
                    header("Location: admin.php");
                    exit();
                } else {
                    header("Location: clock.php");
                    exit();
                }
            } else {
                $error_message = "Invalid username or password";
            }
        } else {
            $error_message = "Invalid username or password";
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
            width: 25%;
        }
    </style>
</head>
<body>
    <div class="center-container">
        <form method="post">
            <fieldset>
                <legend>Identify yourself</legend>
                <?php if ($error_message): ?>
                    <p style="color: red; font-size: smaller;"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <p>
                    <label for="username" class="form-label">username :</label>
                    <input type="text" name="username" id="username" value="" class="form-control" required>
                </p>
                <p>
                    <label for="password" class="form-label">Password :</label>
                    <input type="password" name="password" id="password" value="" class="form-control" required><br>
                    <input class="btn btn-primary" type="submit" name="submit" value="Log in">
                </p>
            </fieldset>
        </form>
    </div>
</body>
</html>
