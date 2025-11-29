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

// Check if stock ID is provided
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_cylinders.php");
    exit;
}

$stock_id = trim($_GET["id"]);
$stock = null;

// Fetch stock details
$sql = "SELECT * FROM cylinder_stock WHERE id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $stock_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $stock = $result->fetch_assoc();

            // Log the action
            $log_action = "Downloaded cylinder stock for date: " . $stock['stock_date'];
            $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Cylinder Stock', ?)";
            if($log_stmt = $mysqli->prepare($log_sql)){
                $log_stmt->bind_param("isss", $_SESSION["id"], $_SESSION["member_username"], $log_action, $stock_id);
                $log_stmt->execute();
                $log_stmt->close();
            }
        } else {
            exit("Stock record not found.");
        }
    }
    $stmt->close();
}
$mysqli->close();

// Set headers to download file
$filename = "cylinder_stock_" . $stock['stock_date'] . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array_keys($stock));

// Output the data row
fputcsv($output, $stock);

exit();
?>