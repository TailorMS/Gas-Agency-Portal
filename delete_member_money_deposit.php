<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){
    header("location: member_login.php");
    exit;
}

// Include db connect file
require_once "db_connect.php";

// Check if deposit ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_money.php");
    exit;
}

$deposit_id = trim($_GET["id"]);

// Prepare a delete statement
$sql = "DELETE FROM cash_deposits WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $deposit_id);
    if($stmt->execute()){
        // Log the action
        $log_action = "Deleted cash deposit ID: " . $deposit_id;
        $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Cash Deposit', ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("isss", $_SESSION["id"], $_SESSION["member_username"], $log_action, $deposit_id);
            $log_stmt->execute();
            $log_stmt->close();
        }
        echo "<script>alert('Deposit deleted successfully.'); window.location.href='member_view_money.php';</script>";
    } else{
        echo "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}
$mysqli->close();
?>