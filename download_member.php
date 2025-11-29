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

// Check if member ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_members.php");
    exit;
}

$member_id = trim($_GET["id"]);

// Prepare a select statement to get all member details
$sql = "SELECT id, username, email, contact_number, dob, qualification, experience, address, created_at FROM members WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $member_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $member = $result->fetch_assoc();

            $filename = "member_" . $member['username'] . "_" . date('Ymd') . ".csv";

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Define and write the header row
            $headers = array('Member ID', 'Username', 'Email', 'Contact Number', 'Date of Birth', 'Qualification', 'Experience', 'Address', 'Registration Date');
            fputcsv($output, $headers);

            // Prepare the data row
            $data_row = array(
                $member['id'], $member['username'], $member['email'], $member['contact_number'],
                $member['dob'], $member['qualification'], $member['experience'],
                $member['address'], date("Y-m-d H:i:s", strtotime($member['created_at']))
            );
            fputcsv($output, $data_row);

            fclose($output);
            exit;
        }
    }
}
// If we get here, something went wrong or member not found
header("location: view_members.php");
exit;
?>