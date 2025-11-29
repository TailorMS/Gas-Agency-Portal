<?php
// Initialize the session
session_start();

// Check if the user is logged in as an admin, if not then redirect to login page
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: Admin.php");
    exit;
}

// Include db connect file
require_once "db_connect.php";

// Check if customer ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_customers.php");
    exit;
}

$customer_id = trim($_GET["id"]);

// Prepare a delete statement
$sql = "DELETE FROM customers WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->close();
}
$mysqli->close();

header("location: view_customers.php");
exit;
?>