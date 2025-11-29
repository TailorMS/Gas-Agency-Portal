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

// Check if target details are provided in the URL
if(!isset($_GET["target_type"]) || empty(trim($_GET["target_type"])) || !isset($_GET["target_id"]) || empty(trim($_GET["target_id"]))){
    header("location: history.php");
    exit;
}

$target_type = trim($_GET["target_type"]);
$target_id = trim($_GET["target_id"]);
$details_html = "";
$page_title = "Item Details";

if ($target_type === 'Customer') {
    $page_title = "Customer Details";
    $sql = "SELECT id, customer_no, name, mobile_no, aadhar_no, ration_card_no, address, bank_details, created_at FROM customers WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $target_id);
        if($stmt->execute()){
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $customer = $result->fetch_assoc();
                $details_html .= '<div class="detail-item"><strong>Customer ID:</strong> ' . htmlspecialchars($customer['id']) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Customer Number:</strong> ' . htmlspecialchars($customer['customer_no']) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Name:</strong> ' . htmlspecialchars($customer['name']) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Mobile No:</strong> ' . htmlspecialchars($customer['mobile_no']) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Aadhar Number:</strong> ' . htmlspecialchars($customer['aadhar_no']) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Ration Card No:</strong> ' . htmlspecialchars($customer['ration_card_no']) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Address:</strong> ' . (!empty($customer['address']) ? nl2br(htmlspecialchars($customer['address'])) : 'N/A') . '</div>';
                $details_html .= '<div class="detail-item"><strong>Bank Details:</strong> ' . (!empty($customer['bank_details']) ? nl2br(htmlspecialchars($customer['bank_details'])) : 'N/A') . '</div>';
                $details_html .= '<div class="detail-item"><strong>Registration Date:</strong> ' . date("F j, Y, g:i a", strtotime($customer['created_at'])) . '</div>';
            } else {
                $details_html = "<p>Customer not found.</p>";
            }
        }
        $stmt->close();
    }
} elseif ($target_type === 'Transaction') {
    $page_title = "Transaction Details";
    $sql = "SELECT id, transaction_date, transaction_type, name, description, money_in, money_out, created_at FROM transactions WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $target_id);
        if($stmt->execute()){
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $transaction = $result->fetch_assoc();
                $details_html .= '<div class="detail-item"><strong>Transaction ID:</strong> ' . htmlspecialchars($transaction['id']) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Transaction Date:</strong> ' . date("M j, Y", strtotime($transaction['transaction_date'])) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Name:</strong> ' . (!empty($transaction['name']) ? htmlspecialchars($transaction['name']) : 'N/A') . '</div>';
                $details_html .= '<div class="detail-item"><strong>Transaction Type:</strong> ' . htmlspecialchars($transaction['transaction_type']) . '</div>';
                $details_html .= '<div class="detail-item"><strong>Description:</strong> ' . (!empty($transaction['description']) ? nl2br(htmlspecialchars($transaction['description'])) : 'N/A') . '</div>';
                $details_html .= '<div class="detail-item"><strong>Money In (Credit):</strong> <span style="color: green;">' . htmlspecialchars(number_format($transaction['money_in'], 2)) . '</span></div>';
                $details_html .= '<div class="detail-item"><strong>Money Out (Debit):</strong> <span style="color: red;">' . htmlspecialchars(number_format($transaction['money_out'], 2)) . '</span></div>';
                $details_html .= '<div class="detail-item"><strong>Recorded On:</strong> ' . date("F j, Y, g:i a", strtotime($transaction['created_at'])) . '</div>';
            } else {
                $details_html = "<p>Transaction not found.</p>";
            }
        }
        $stmt->close();
    }
} else {
    // Invalid target type
    header("location: history.php");
    exit;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
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
        <div class="logo"><a href="dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="history.php" class="active" title="History"><i class="fas fa-history"></i></a>
            <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <h1 class="page-header"><?php echo $page_title; ?></h1>
        <div class="profile-card">
            <?php echo $details_html; ?>
            <div style="margin-top: 20px;">
                <a href="history.php" class="btn" style="display: inline-block; width: auto; background-color: #6c757d;" title="Back to History"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>

</body>
</html>