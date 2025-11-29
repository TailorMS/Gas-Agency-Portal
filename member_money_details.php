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

// Check if deposit ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_money.php");
    exit;
}

$deposit_id = trim($_GET["id"]);
$deposit = null;

// Prepare a select statement to get all deposit details
$sql = "SELECT id, deposit_date, count_500, count_200, count_100, count_50, count_10, total_amount FROM cash_deposits WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $deposit_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $deposit = $result->fetch_assoc();
        } else {
            // No record found, redirect
            header("location: member_view_money.php");
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
    <title>Cash Deposit Details</title>
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
            <h1 class="page-header">Cash Deposit Details</h1>
            <?php if ($deposit): ?>
            <div class="detail-item"><strong>Deposit Date:</strong> <?php echo date("M j, Y", strtotime($deposit['deposit_date'])); ?></div>
            <div class="detail-item"><strong>₹500 Notes:</strong> <?php echo htmlspecialchars($deposit['count_500']); ?></div>
            <div class="detail-item"><strong>₹200 Notes:</strong> <?php echo htmlspecialchars($deposit['count_200']); ?></div>
            <div class="detail-item"><strong>₹100 Notes:</strong> <?php echo htmlspecialchars($deposit['count_100']); ?></div>
            <div class="detail-item"><strong>₹50 Notes:</strong> <?php echo htmlspecialchars($deposit['count_50']); ?></div>
            <div class="detail-item"><strong>₹10 Notes:</strong> <?php echo htmlspecialchars($deposit['count_10']); ?></div>
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #dddfe2;">
            <div class="detail-item"><strong>Total Amount:</strong> <span style="color: green; font-weight: bold; font-size: 1.2em;">₹<?php echo htmlspecialchars(number_format($deposit['total_amount'], 2)); ?></span></div>
            <?php endif; ?>
            <div style="margin-top: 20px;">
                <a href="edit_member_money_deposit.php?id=<?php echo $deposit['id']; ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px;" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="download_member_money_deposit.php?id=<?php echo $deposit['id']; ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #218838;" title="Download"><i class="fas fa-download"></i></a>
                <a href="delete_member_money_deposit.php?id=<?php echo $deposit['id']; ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #dc3545;" onclick="return confirm('Are you sure you want to delete this deposit?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                <a href="member_view_money.php" class="btn" style="display: inline-block; width: auto; background-color: #6c757d;" title="Back to Deposits"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>

</body>
</html>