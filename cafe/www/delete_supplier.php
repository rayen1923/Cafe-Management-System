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
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['supplier_id'])) {
    $supplier_id = $_GET['supplier_id'];
    $mysqli->begin_transaction();
    $stmt_supplier = $mysqli->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
    $stmt_supplier->bind_param("i", $supplier_id);
    $supplier_deleted = $stmt_supplier->execute();
    $stmt_supplier->close();
    if ($supplier_deleted) {
        $mysqli->commit();
        header("Location: supplier.php");
        exit();
    } else {
        $mysqli->rollback();
        echo "Error have invoices : " . $mysqli->error;
    }
} else {
    echo "Invalid request.";
}
$mysqli->close();
ob_end_flush();
?>