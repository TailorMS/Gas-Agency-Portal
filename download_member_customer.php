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

// Check if customer ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_customers.php");
    exit;
}

$customer_id = trim($_GET["id"]);

// Fetch customer from the database
$sql = "SELECT id, customer_no, name, mobile_no, aadhar_no, ration_card_no, address, bank_details, created_at FROM customers WHERE id = ?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();

    if ($customer) {
        // Log the action
        $log_action = "Downloaded details for customer: " . $customer['name'];
        $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Customer', ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("issi", $_SESSION["id"], $_SESSION["member_username"], $log_action, $customer_id);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
}
$mysqli->close();

if (!$customer) {
    header("location: member_view_customers.php");
    exit;
}

// Set headers to download file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customer_' . $customer['id'] . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('ID', 'Customer No', 'Name', 'Mobile No', 'Aadhar No', 'Ration Card No', 'Address', 'Bank Details', 'Registration Date'));

// Output the customer data
fputcsv($output, array(
    $customer['id'], $customer['customer_no'], $customer['name'], $customer['mobile_no'],
    $customer['aadhar_no'], $customer['ration_card_no'], $customer['address'],
    $customer['bank_details'], $customer['created_at']
));

exit();
?>