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

// Define search variable
$search_term = $_GET['search'] ?? '';

// Build the query to fetch customers
$sql = "SELECT id, customer_no, name, mobile_no, aadhar_no, ration_card_no, address, bank_details, created_at FROM customers";

if (!empty($search_term)) {
    $sql .= " WHERE customer_no LIKE ? OR name LIKE ? OR mobile_no LIKE ? OR aadhar_no LIKE ?";
}

$sql .= " ORDER BY created_at DESC";

if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($search_term)) {
        $like_term = "%" . $search_term . "%";
        $stmt->bind_param("ssss", $like_term, $like_term, $like_term, $like_term);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $filename = "all_customers_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Write the header row
    $headers = array('Customer ID', 'Customer Number', 'Name', 'Mobile No', 'Aadhar Number', 'Ration Card No', 'Address', 'Bank Details', 'Registration Date');
    fputcsv($output, $headers);

    // Loop through the results and write each row
    while ($row = $result->fetch_assoc()) {
        $data_row = array(
            $row['id'], $row['customer_no'], $row['name'], $row['mobile_no'],
            $row['aadhar_no'], $row['ration_card_no'], $row['address'],
            $row['bank_details'], date("Y-m-d H:i:s", strtotime($row['created_at']))
        );
        fputcsv($output, $data_row);
    }

    fclose($output);
    $stmt->close();
    exit;
}

header("location: view_customers.php");
exit;
?>