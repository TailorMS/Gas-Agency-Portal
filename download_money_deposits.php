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

// Get search date
$search_date = $_GET['search_date'] ?? '';

// Fetch cash deposit records
$sql = "SELECT deposit_date, count_500, count_200, count_100, count_50, count_10, total_amount FROM cash_deposits";

$bind_params = [];
if (!empty($search_date)) {
    $sql .= " WHERE deposit_date = ?";
    $bind_params[] = $search_date;
}

$sql .= " ORDER BY deposit_date DESC, id DESC";

if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($bind_params)) {
        $stmt->bind_param("s", ...$bind_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $deposits = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$mysqli->close();

// Set headers to download file
$filename = "cash_deposits_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, ['Date', '500', '200', '100', '50', '10', 'Total Amount']);

// Loop over the rows, outputting them
if (count($deposits) > 0) {
    foreach ($deposits as $row) {
        fputcsv($output, [
            $row['deposit_date'], $row['count_500'], $row['count_200'],
            $row['count_100'], $row['count_50'], $row['count_10'],
            $row['total_amount']
        ]);
    }
}

exit();
?>