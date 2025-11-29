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

// Check if transaction ID is provided in the URL
if(!isset($_GET["transaction_id"]) || empty(trim($_GET["transaction_id"]))){
    header("location: view_transaction.php");
    exit;
}

$transaction_id = trim($_GET["transaction_id"]);

// Prepare a delete statement
$sql = "DELETE FROM transactions WHERE transaction_id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("s", $transaction_id);
    if($stmt->execute()){
        // Show success alert and redirect
        echo "<script>alert('Transaction deleted successfully!'); window.location.href='view_transaction.php';</script>";
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}
$mysqli->close();
exit;
?>