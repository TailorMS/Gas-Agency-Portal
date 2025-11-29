<?php
session_start();
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: Admin.php");
    exit;
}

require_once "db_connect.php";

$search_date = $_GET['search_date'] ?? '';

$sql = "SELECT id, stock_date, total_quantity FROM other_stock";
if (!empty($search_date)) {
    $sql .= " WHERE stock_date = ?";
}
$sql .= " ORDER BY stock_date DESC, id DESC";

if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($search_date)) {
        $stmt->bind_param("s", $search_date);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $filename = "other_stock_data_" . date('Y-m-d') . ".csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, array('Stock ID', 'Date', 'Total Quantity'));

    // Add data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    $stmt->close();
}
$mysqli->close();
exit();