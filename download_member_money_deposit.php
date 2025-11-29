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

// Check if deposit ID is provided
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_money.php");
    exit;
}

$deposit_id = trim($_GET["id"]);
$deposit = null;

// Fetch deposit details
$sql = "SELECT id, deposit_date, count_500, count_200, count_100, count_50, count_10, total_amount FROM cash_deposits WHERE id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $deposit_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $deposit = $result->fetch_assoc();
        } else {
            exit("Deposit not found.");
        }
    }
    $stmt->close();
}
$mysqli->close();

// Set headers to download file
$filename = "cash_deposit_" . $deposit['id'] . "_" . $deposit['deposit_date'] . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Output the column headings and data
fputcsv($output, ['Date', '500', '200', '100', '50', '10', 'Total Amount']);
fputcsv($output, [$deposit['deposit_date'], $deposit['count_500'], $deposit['count_200'], $deposit['count_100'], $deposit['count_50'], $deposit['count_10'], $deposit['total_amount']]);

exit();
?>