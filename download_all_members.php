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

// Build the query to fetch members
$sql = "SELECT id, username, email, contact_number, dob, qualification, experience, address, created_at FROM members";

if (!empty($search_term)) {
    $sql .= " WHERE username LIKE ? OR email LIKE ? OR contact_number LIKE ?";
}

$sql .= " ORDER BY created_at DESC";

if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($search_term)) {
        $like_term = "%" . $search_term . "%";
        $stmt->bind_param("sss", $like_term, $like_term, $like_term);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $filename = "all_members_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Write the header row
    $headers = array('Member ID', 'Username', 'Email', 'Contact Number', 'Date of Birth', 'Qualification', 'Experience', 'Address', 'Registration Date');
    fputcsv($output, $headers);

    // Loop through the results and write each row
    while ($row = $result->fetch_assoc()) {
        $data_row = array(
            $row['id'], $row['username'], $row['email'], $row['contact_number'], $row['dob'],
            $row['qualification'], $row['experience'], $row['address'], date("Y-m-d H:i:s", strtotime($row['created_at']))
        );
        fputcsv($output, $data_row);
    }

    fclose($output);
    $stmt->close();
    exit;
}

header("location: view_members.php");
exit;
?>