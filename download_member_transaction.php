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

// Check if transaction ID is provided in the URL
if(!isset($_GET["transaction_id"]) || empty(trim($_GET["transaction_id"]))){
    header("location: member_view_transaction.php");
    exit;
}

$transaction_id = trim($_GET["transaction_id"]);

// Fetch transaction from the database
$sql = "SELECT transaction_id, transaction_date, transaction_type, name, description, money_in, money_out FROM transactions WHERE transaction_id = ?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close();

    if ($transaction) {
        // Log the action
        $log_action = "Downloaded details for transaction ID: " . $transaction_id;
        $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Transaction', ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("issi", $_SESSION["id"], $_SESSION["member_username"], $log_action, $transaction_id);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
}

if (!$transaction) {
    header("location: member_view_transaction.php");
    exit;
}

// Set headers to download file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transaction_' . $transaction['transaction_id'] . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('Transaction ID', 'Transaction Date', 'Transaction Type', 'Name', 'Description', 'Money In', 'Money Out'));

// Output the transaction data
fputcsv($output, array(
    $transaction['transaction_id'], $transaction['transaction_date'], $transaction['transaction_type'],
    $transaction['name'], $transaction['description'], $transaction['money_in'], $transaction['money_out']
));

$mysqli->close();
exit();
?>