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

// Check if stock ID is provided
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_cylinders.php");
    exit;
}

$stock_id = trim($_GET["id"]);

// Fetch stock details
$sql = "SELECT * FROM cylinder_stock WHERE id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $stock_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $stock = $result->fetch_assoc();

            $filename = "cylinder_stock_" . $stock['stock_date'] . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);

            $output = fopen('php://output', 'w');

            // Output the column headings
            fputcsv($output, array_keys($stock));

            // Output the data row
            fputcsv($output, $stock);

            fclose($output);
            exit();
        }
    }
    $stmt->close();
}
$mysqli->close();

header("location: cylinders_detail.php?id=" . $stock_id);
exit;
?>