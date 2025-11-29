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

// Check if stock ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_cylinders.php");
    exit;
}

$stock_id = trim($_GET["id"]);

// Prepare a delete statement
$sql = "DELETE FROM cylinder_stock WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $stock_id);

    if($stmt->execute()){
        // Log the action
        $log_action = "Deleted cylinder stock record ID: " . $stock_id;
        $log_sql = "INSERT INTO member_history (member_id, member_username, action) VALUES (?, ?, ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("iss", $_SESSION["id"], $_SESSION["member_username"], $log_action);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
    $stmt->close();
}
$mysqli->close();

// Redirect to the view page
header("location: member_view_cylinders.php");
exit;
?>