<?php
session_start();
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){ header("location: Admin.php"); exit; }
require_once "db_connect.php";

if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_other_stock.php");
    exit;
}

$stock_id = trim($_GET["id"]);
$sql = "DELETE FROM other_stock WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $stock_id);
    $stmt->execute();
    $stmt->close();
}
$mysqli->close();
header("location: view_other_stock.php");
exit;
?>