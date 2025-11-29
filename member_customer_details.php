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

// Prepare a select statement to get all customer details
$sql = "SELECT id, customer_no, name, mobile_no, aadhar_no, ration_card_no, address, bank_details, created_at FROM customers WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $customer_id);
    if($stmt->execute()){
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($id, $customer_no, $name, $mobile_no, $aadhar_no, $ration_card_no, $address, $bank_details, $created_at);
            $stmt->fetch();
        } else {
            header("location: member_view_customers.php");
            exit;
        }
    }
    $stmt->close();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body {
            background-image: url('background_bg.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>
<body>

    <div class="menubar">
        <div class="logo"><a href="member_dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav">
            <a href="member_dashboard.php" class="active">Dashboard</a>
            <a href="member_profile.php" title="Profile"><i class="fas fa-user"></i></a>
            <a href="member_logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        
        <div class="profile-card">
            <h1 class="page-header">Customer Details</h1>
            <div class="detail-item"><strong>Customer ID:</strong> <?php echo htmlspecialchars($id); ?></div>
            <div class="detail-item"><strong>Customer Number:</strong> <?php echo htmlspecialchars($customer_no); ?></div>
            <div class="detail-item"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></div>
            <div class="detail-item"><strong>Mobile No:</strong> <?php echo htmlspecialchars($mobile_no); ?></div>
            <div class="detail-item"><strong>Aadhar Number:</strong> <?php echo htmlspecialchars($aadhar_no); ?></div>
            <div class="detail-item"><strong>Ration Card No:</strong> <?php echo htmlspecialchars($ration_card_no); ?></div>
            <div class="detail-item"><strong>Address:</strong> <?php echo !empty($address) ? nl2br(htmlspecialchars($address)) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Bank Details:</strong> <?php echo !empty($bank_details) ? nl2br(htmlspecialchars($bank_details)) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Registration Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($created_at)); ?></div>
            <div style="margin-top: 20px;">
                <a href="edit_member_customer.php?id=<?php echo htmlspecialchars($id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px;" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="download_member_customer.php?id=<?php echo htmlspecialchars($id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #218838;" title="Download"><i class="fas fa-download"></i></a>
                <a href="delete_member_customer.php?id=<?php echo htmlspecialchars($id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #dc3545;" onclick="return confirm('Are you sure you want to delete this customer?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                <a href="member_view_customers.php" class="btn" style="display: inline-block; width: auto; background-color: #6c757d;" title="Back to Customers"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>

</body>
</html>