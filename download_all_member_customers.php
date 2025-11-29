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

// Get search term
$search_term = $_GET['search'] ?? '';

// Fetch customers from the database
$sql = "SELECT id, customer_no, name, mobile_no, aadhar_no, ration_card_no, address, bank_details, created_at FROM customers";

if (!empty($search_term)) {
    $sql .= " WHERE customer_no LIKE ? OR name LIKE ? OR mobile_no LIKE ? OR aadhar_no LIKE ?";
}

$sql .= " ORDER BY created_at DESC";

$customers = [];
if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($search_term)) {
        $like_term = "%" . $search_term . "%";
        $stmt->bind_param("ssss", $like_term, $like_term, $like_term, $like_term);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Log the action
    $log_action = "Downloaded all customer data";
    $log_sql = "INSERT INTO member_history (member_id, member_username, action) VALUES (?, ?, ?)";
    if($log_stmt = $mysqli->prepare($log_sql)){
        $log_stmt->bind_param("iss", $_SESSION["id"], $_SESSION["member_username"], $log_action);
        $log_stmt->execute();
        $log_stmt->close();
    }
}
$mysqli->close();

// Set headers to download file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customers_' . date('Y-m-d') . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('ID', 'Customer No', 'Name', 'Mobile No', 'Aadhar No', 'Ration Card No', 'Address', 'Bank Details', 'Registration Date'));

// Loop over the rows, outputting them
if (count($customers) > 0) {
    foreach ($customers as $row) {
        fputcsv($output, array(
            $row['id'], $row['customer_no'], $row['name'], $row['mobile_no'],
            $row['aadhar_no'], $row['ration_card_no'], $row['address'],
            $row['bank_details'], $row['created_at']
        ));
    }
}

exit();
?>