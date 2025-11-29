<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: Admin.php");
    exit;
}

// Include db connect file
require_once "db_connect.php";

// Check if deposit ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_money.php");
    exit;
}

$deposit_id = trim($_GET["id"]);

// Prepare a delete statement
$sql = "DELETE FROM cash_deposits WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $deposit_id);
    if($stmt->execute()){
        echo "<script>alert('Deposit deleted successfully.'); window.location.href='view_money.php';</script>";
    } else{
        echo "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}
$mysqli->close();
?>