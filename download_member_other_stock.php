<?php
session_start();
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){ header("location: member_login.php"); exit; }
require_once "db_connect.php";

if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_other_stock.php");
    exit;
}

$stock_id = trim($_GET["id"]);
$stock = null;

$sql = "SELECT * FROM other_stock WHERE id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $stock_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $stock = $result->fetch_assoc();

            // Log the action
            $log_action = "Downloaded other stock for date: " . $stock['stock_date'];
            $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Other Stock', ?)";
            if($log_stmt = $mysqli->prepare($log_sql)){
                $log_stmt->bind_param("isss", $_SESSION["id"], $_SESSION["member_username"], $log_action, $stock_id);
                $log_stmt->execute();
                $log_stmt->close();
            }

            $filename = "other_stock_" . $stock['stock_date'] . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($stock));
            fputcsv($output, $stock);
            fclose($output);
            exit();
        }
    }
    $stmt->close();
}
$mysqli->close();

header("location: member_other_stock_detail.php?id=" . $stock_id);
exit;
?>