<?php
// Initialize the session
session_start();

// Check if the user is logged in as a member, if not then redirect to login page
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){
    header("location: member_login.php");
    exit;
}

// Include db connect file
require_once "db_connect.php";

// Check if transaction ID is provided in the URL
if(!isset($_GET["transaction_id"]) || empty(trim($_GET["transaction_id"]))){
    header("location: member_view_transaction.php");
    exit;
}

$transaction_id = trim($_GET["transaction_id"]);

// Prepare a delete statement
$sql = "DELETE FROM transactions WHERE transaction_id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("s", $transaction_id);
    if($stmt->execute()){
        // Log the action
        $log_action = "Deleted transaction ID: " . $transaction_id;
        $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Transaction', ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("isss", $_SESSION["id"], $_SESSION["member_username"], $log_action, $transaction_id);
            $log_stmt->execute();
            $log_stmt->close();
        }
        // Show success alert and redirect
        echo "<script>alert('Transaction deleted successfully!'); window.location.href='member_view_transaction.php';</script>";
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}
$mysqli->close();
exit;
?>