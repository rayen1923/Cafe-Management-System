<?php
require_once('Config.php');
$mysqli = new mysqli(db_host, db_user, db_password, db_database);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["clock_action"])) {
    $clockAction = $_POST["clock_action"];
    if ($clockAction === "clock_in") {
        clockIn($mysqli, $user_id);
    } elseif ($clockAction === "clock_out") {
        clockOut($mysqli, $user_id);
    }
}
function clockIn($conn, $user_id) {
    $stmt = $conn->prepare("INSERT INTO shifts (user_id, clock_in) VALUES (?, NOW())");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error in preparing the statement: " . $conn->error;
    }
}
function clockOut($conn, $user_id) {
    $stmt = $conn->prepare("UPDATE shifts SET clock_out = NOW() WHERE user_id = ? AND clock_out IS NULL");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}
function isClockedIn($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM shifts WHERE user_id = ? AND clock_out IS NULL");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    } else {
        return false;
    }
}
function getClockInTime($conn, $user_id) {
    $stmt = $conn->prepare("SELECT clock_in FROM shifts WHERE user_id = ? AND clock_out IS NULL");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $shift = $result->fetch_assoc();
            return $shift["clock_in"];
        }
    } else {
        echo "Error in preparing the statement: " . $conn->error;
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="lib/bootstrap.min.css">
    <link rel="stylesheet" href="lib/docs.css">
    <script src="lib/bootstrap.bundle.min.js"></script>
    <title>CAFE</title>
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
            text-align: center;
        }
        .centered {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .btn-group {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="center-container">
        <form method="post">
            <fieldset>
                <h1 class="centered">Welcome <?php echo htmlspecialchars($username); ?></h1>
                <h4 id="current-time" class="centered">Current Time: </h4>
                <h4 id="status">
                    <?php
                    $clockedIn = isClockedIn($mysqli, $user_id);
                    if ($clockedIn) {
                        echo "Click CLOCK OUT to END your shift";
                    } else {
                        echo "Click CLOCK IN to START your shift";
                    }
                    ?>
                </h4>
                <h4 id="clockInTime" class="centered">
                    <?php
                    if ($clockedIn) {
                        $clockInTime = getClockInTime($mysqli, $user_id);
                        echo "Clock In Time: " . $clockInTime;
                    }
                    ?>
                </h4>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="clock_action" value="<?php echo $clockedIn ? 'clock_out' : 'clock_in'; ?>">
                    <button class="btn btn-primary" type="submit"><?php echo $clockedIn ? 'Clock Out' : 'Clock In'; ?></button><br>
                    <div class="btn-group">
                        <a class="btn btn-success" style="margin-right: 10px;" href="profile.php?user_id=<?php echo $row['user_id']; ?>">Profile</a>
                        <button class="btn btn-danger" type="button" onclick="location.href='logout.php'">Logout</button>
                    </div>
                </form>
            </fieldset>
        </form>
    </div>
    <script>
        function updateTime() {
            var currentTime = new Date();
            var hours = currentTime.getHours();
            var minutes = currentTime.getMinutes();
            var seconds = currentTime.getSeconds();
            hours = (hours < 10 ? "0" : "") + hours;
            minutes = (minutes < 10 ? "0" : "") + minutes;
            seconds = (seconds < 10 ? "0" : "") + seconds;
            document.getElementById('current-time').innerHTML = "Current Time: " + hours + ":" + minutes + ":" + seconds;
            setTimeout(updateTime, 1000);
        }
        updateTime();
    </script>
</body>
</html>
<?php
$mysqli->close();
?>