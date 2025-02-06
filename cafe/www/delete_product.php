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
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['produit_id'])) {
    $produit_id = $_GET['produit_id'];
    $mysqli->begin_transaction();
    $stmt_produit = $mysqli->prepare("DELETE FROM produits WHERE produit_id = ?");
    $stmt_produit->bind_param("i", $produit_id);
    $produit_deleted = $stmt_produit->execute();
    $stmt_produit->close();
    if ($produit_deleted) {
        $mysqli->commit();
        header("Location: stock.php");
        exit();
    } else {
        $mysqli->rollback();
        echo "Error have invoices: " . $mysqli->error;
    }
} else {
    echo "Invalid request.";
}
$mysqli->close();
ob_end_flush();
?>