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

// Check if customer ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_customers.php");
    exit;
}

$customer_id = trim($_GET["id"]);

// Prepare a select statement to get all customer details
$sql = "SELECT id, customer_no, name, mobile_no, aadhar_no, ration_card_no, address, bank_details, created_at FROM customers WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $customer_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $customer = $result->fetch_assoc();

            $filename = "customer_" . $customer['customer_no'] . "_" . date('Ymd') . ".csv";

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Define and write the header row
            $headers = array('Customer ID', 'Customer Number', 'Name', 'Mobile No', 'Aadhar Number', 'Ration Card No', 'Address', 'Bank Details', 'Registration Date');
            fputcsv($output, $headers);

            // Prepare the data row
            $data_row = array(
                $customer['id'],
                $customer['customer_no'],
                $customer['name'],
                $customer['mobile_no'],
                $customer['aadhar_no'],
                $customer['ration_card_no'],
                $customer['address'],
                $customer['bank_details'],
                date("Y-m-d H:i:s", strtotime($customer['created_at']))
            );
            fputcsv($output, $data_row);

            fclose($output);
            exit;
        }
    }
}
// If we get here, something went wrong or customer not found
header("location: view_customers.php");
exit;
?>