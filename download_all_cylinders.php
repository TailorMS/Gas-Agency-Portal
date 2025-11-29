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

// Get search date from URL
$search_date = $_GET['search_date'] ?? '';

// Build the query to fetch cylinder stock records
$sql = "SELECT * FROM cylinder_stock";

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

    $filename = "all_cylinder_stock_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Write the header row by fetching column names
    $fields = $result->fetch_fields();
    $headers = array_map(fn($field) => $field->name, $fields);
    fputcsv($output, $headers);

    // Loop through the results and write each row
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    $stmt->close();
    exit;
}

header("location: view_cylinders.php");
exit;
?>