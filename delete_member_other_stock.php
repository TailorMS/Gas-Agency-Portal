<?php
session_start();
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){ header("location: member_login.php"); exit; }
require_once "db_connect.php";

if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_other_stock.php");
    exit;
}

$stock_id = trim($_GET["id"]);

$sql = "DELETE FROM other_stock WHERE id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $stock_id);
    if($stmt->execute()){
        // Log the action
        $log_action = "Deleted other stock record ID: " . $stock_id;
        $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Other Stock', ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("isss", $_SESSION["id"], $_SESSION["member_username"], $log_action, $stock_id);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
    $stmt->close();
}
$mysqli->close();

echo "<script>alert('Stock record deleted successfully.'); window.location.href='member_view_other_stock.php';</script>";
exit;
?>