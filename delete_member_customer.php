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

// Check if customer ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_customers.php");
    exit;
}

$customer_id = trim($_GET["id"]);

// First, get customer name for logging before deleting
$customer_name = "ID: " . $customer_id;
$sql_select = "SELECT name FROM customers WHERE id = ?";
if($stmt_select = $mysqli->prepare($sql_select)){
    $stmt_select->bind_param("i", $customer_id);
    if($stmt_select->execute()){
        $result = $stmt_select->get_result();
        if($row = $result->fetch_assoc()){
            $customer_name = $row['name'];
        }
    }
    $stmt_select->close();
}

// Prepare a delete statement
$sql_delete = "DELETE FROM customers WHERE id = ?";
if($stmt_delete = $mysqli->prepare($sql_delete)){
    $stmt_delete->bind_param("i", $customer_id);
    if($stmt_delete->execute()){
        // Log the action
        $log_action = "Deleted customer: " . $customer_name;
        $log_sql = "INSERT INTO member_history (member_id, member_username, action) VALUES (?, ?, ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("iss", $_SESSION["id"], $_SESSION["member_username"], $log_action);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
    $stmt_delete->close();
}
$mysqli->close();

header("location: member_view_customers.php");
exit;
?>