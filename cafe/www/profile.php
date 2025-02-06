<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once('Config.php');
$mysqli = new mysqli(db_host, db_user, db_password, db_database);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$user_id =  $_SESSION['user_id'] ;
$stmt = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt = $mysqli->prepare("SELECT DISTINCT DATE_FORMAT(clock_in, '%M %Y') AS month_year FROM shifts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$month_years = array();
while ($row = $result->fetch_assoc()) {
    $month_years[] = $row['month_year'];
}

$selected_month_year = isset($_POST['month_year']) ? $_POST['month_year'] : date('F Y');

$stmt = $mysqli->prepare("SELECT * FROM shifts WHERE user_id = ? AND DATE_FORMAT(clock_in, '%M %Y') = ?");
$stmt->bind_param("is", $user_id, $selected_month_year);
$stmt->execute();
$shift_result = $stmt->get_result();
$shifts = array();
while ($row = $shift_result->fetch_assoc()) {
    $shifts[] = $row;
}

$employee_name = $user['first_name'] . '_' . $user['last_name'];

$stmt->close();
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
            margin-top: 40%;
            margin-bottom: 5%;
        }
        .container {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
        }
        .form-group,
        .btn-group {
            margin-top: 20px;
        }
        .btn-group button {
            margin-right: 10px;
        }
    </style>
</head>
<body>
<div class="container">
        <h1>Generate Reports</h1>
        <div class="form-group">
            <form method="post">
                <select name="month_year" id="month_year" class="form-control">
                    <?php foreach ($month_years as $month_year): ?>
                        <option value="<?php echo $month_year; ?>" <?php if ($month_year === $selected_month_year) echo "selected"; ?>><?php echo $month_year; ?></option>
                    <?php endforeach; ?>
                </select><br>
                <button type="submit" class="btn btn-primary">Submit</button>
                <button class="btn btn-secondary" type="button" onclick="location.href='clock.php'">GO BACK</button>
                <button class="btn btn-danger" type="button" onclick="location.href='logout.php'">Logout</button>
            </form><br>
        </div>
        <?php if (!empty($user) && !empty($shifts)) : ?>
            <div id="report" class="container">
                <h1>Report :</h1>
                <h6>Period of the report: <?php echo $selected_month_year; ?></h6><br><br>
                <h2>User Information</h2>
                <p><strong>Name:</strong> <?php echo $user['first_name'] . ' ' . $user['last_name']; ?></p>
                <p><strong>CIN:</strong> <?php echo $user['cin']; ?></p>
                <p><strong>Address:</strong> <?php echo $user['address']; ?></p>
                <p><strong>Phone:</strong> <?php echo $user['phone']; ?></p>
                <p><strong>Email:</strong> <?php echo $user['mail']; ?></p>
                <p><strong>Daily Wage:</strong> <?php echo $user['daily_wage']; ?></p><br><br>

                <h3>Calendar:</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                            <th>Sun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $firstDayOfMonth = date("Y-m-01", strtotime($selected_month_year));
                        $daysInMonth = date("t", strtotime($firstDayOfMonth));
                        $startDayOfWeek = date("w", strtotime($firstDayOfMonth));
                        $shiftStatus = array_fill(1, $daysInMonth, "gray");
                        foreach ($shifts as $shift) {
                            $dayOfMonth = date("j", strtotime($shift['clock_in']));
                            $shiftStatus[$dayOfMonth] = "green";
                        }
                        for ($i = 1; $i <= $daysInMonth; $i++) {
                            $currentDay = date("Y-m-d", strtotime("$firstDayOfMonth +$i day"));
                            if ($currentDay > date("Y-m-d")) {
                                $status = "gray"; 
                            } else {
                                $status = $shiftStatus[$i];
                            }
                            $backgroundColor = $status == "green" ? "green" : "red";
                            $additionalClass = $status == "green" ? "worked" : "absent";
                            if ($i == 1 || ($i % 7) == 1) {
                                echo "<tr>"; 
                            }
                            echo "<td class='$additionalClass' style='background-color: $backgroundColor;'>$i</td>";
                            if ($i == $daysInMonth || ($i % 7) == 0) {
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table><br><br>

                <div class="page-break"></div>

                <h3>Shifts:</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Time Worked</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalShifts = count($shifts);
                        $totalDuration = 0;
                        foreach ($shifts as $shift):
                            $clockIn = strtotime($shift['clock_in']);
                            $clockOut = strtotime($shift['clock_out']);
                            $duration = $clockOut - $clockIn;
                            $totalDuration += $duration;
                        ?>
                        <tr>
                            <td><?php echo date("Y-m-d", $clockIn); ?></td>
                            <td><?php echo date("H:i:s", $clockIn); ?></td>
                            <td><?php echo date("H:i:s", $clockOut); ?></td>
                            <td><?php echo gmdate("H:i:s", $duration); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3"><strong>Total Duration:</strong></td>
                            <td><strong><?php echo gmdate("H:i:s", $totalDuration); ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="3"><strong>Total Shifts:</strong></td>
                            <td><strong><?php echo $totalShifts; ?></strong></td>
                        </tr>
                        <?php
                        $dailyWage = $user['daily_wage'];
                        $daysWorked = $totalShifts;
                        $monthSalary = $dailyWage * $daysWorked;
                        ?>
                        <tr>
                            <td colspan="3"><strong>Month Salary:</strong></td>
                            <td><strong><?php echo $monthSalary; ?> D</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php elseif (empty($user)) : ?>
            <div class="alert alert-danger" role="alert">
                User not found!
            </div>
        <?php else : ?>
            <div class="alert alert-warning" role="alert">
                No shifts found for the selected period.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>