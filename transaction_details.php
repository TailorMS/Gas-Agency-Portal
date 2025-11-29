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

// Check if transaction ID is provided in the URL
if(!isset($_GET["transaction_id"]) || empty(trim($_GET["transaction_id"]))){
    header("location: view_transaction.php");
    exit;
}

$transaction_id = trim($_GET["transaction_id"]);

// Prepare a select statement to get all transaction details
$sql = "SELECT  transaction_id, transaction_date, transaction_type, name, description, money_in, money_out, created_at FROM transactions WHERE transaction_id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("s", $transaction_id);
    if($stmt->execute()){
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($transaction_id, $transaction_date, $transaction_type, $name, $description, $money_in, $money_out, $created_at);
            $stmt->fetch();
        } else {
            header("location: view_transaction.php");
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
    <title>Transaction Details</title>
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
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="history.php" title="History"><i class="fas fa-history"></i></a>
            <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        
        <div class="profile-card">
            <h1 class="page-header">Transaction Details</h1>
            <div class="detail-item"><strong>Transaction ID:</strong> <?php echo !empty($transaction_id) ? htmlspecialchars($transaction_id) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Transaction Date:</strong> <?php echo date("M j, Y", strtotime($transaction_date)); ?></div>
            <div class="detail-item"><strong>Name:</strong> <?php echo !empty($name) ? htmlspecialchars($name) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Transaction Type:</strong> <?php echo htmlspecialchars($transaction_type); ?></div>
            <div class="detail-item"><strong>Description:</strong> <?php echo !empty($description) ? nl2br(htmlspecialchars($description)) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Money In (Credit):</strong> <span style="color: green;"><?php echo htmlspecialchars(number_format($money_in, 2)); ?></span></div>
            <div class="detail-item"><strong>Money Out (Debit):</strong> <span style="color: red;"><?php echo htmlspecialchars(number_format($money_out, 2)); ?></span></div>
            <div class="detail-item"><strong>Recorded On:</strong> <?php echo date("F j, Y, g:i a", strtotime($created_at)); ?></div>
            <div style="margin-top: 20px;">
                <a href="edit_transaction.php?transaction_id=<?php echo urlencode($transaction_id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px;" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="download_transaction.php?transaction_id=<?php echo urlencode($transaction_id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #218838;" title="Download"><i class="fas fa-download"></i></a>
                <a href="delete_transaction.php?transaction_id=<?php echo urlencode($transaction_id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #dc3545;" onclick="return confirm('Are you sure you want to delete this transaction?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                <a href="view_transaction.php" class="btn" style="display: inline-block; width: auto; background-color: #6c757d;" title="Back to Transactions"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>

</body>
</html>