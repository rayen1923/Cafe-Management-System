<?php
ob_start();
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}
require_once('Config.php');
$mysqli = new mysqli(db_host, db_user, db_password, db_database);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $mysqli->begin_transaction();
    $stmt_shifts = $mysqli->prepare("DELETE FROM shifts WHERE user_id = ?");
    $stmt_shifts->bind_param("i", $user_id);
    $shifts_deleted = $stmt_shifts->execute();
    $stmt_shifts->close();
    $stmt_user = $mysqli->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt_user->bind_param("i", $user_id);
    $user_deleted = $stmt_user->execute();
    $stmt_user->close();
    if ($shifts_deleted && $user_deleted) {
        $mysqli->commit();
        header("Location: employees.php");
        exit();
    } else {
        $mysqli->rollback();
        echo "Error deleting user or shifts: " . $mysqli->error;
    }
} else {
    echo "Invalid request.";
}
$mysqli->close();
ob_end_flush();
?>