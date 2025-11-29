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

// Get search date from URL
$search_date = $_GET['search_date'] ?? '';

// Build the query to fetch other stock records
$sql = "SELECT * FROM other_stock";

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

    // Log the action
    $log_action = "Downloaded all other stock data";
    if (!empty($search_date)) {
        $log_action .= " for date: " . $search_date;
    }
    $log_sql = "INSERT INTO member_history (member_id, member_username, action) VALUES (?, ?, ?)";
    if($log_stmt = $mysqli->prepare($log_sql)){
        $log_stmt->bind_param("iss", $_SESSION["id"], $_SESSION["member_username"], $log_action);
        $log_stmt->execute();
        $log_stmt->close();
    }

    $filename = "all_other_stock_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
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
    $mysqli->close();
    exit;
}

header("location: member_view_other_stock.php");
exit;
?>