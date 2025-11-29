<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){
    header("location: member_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AmodIndane - Member Dashboard</title>
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
        <div class="content-card">
            <img src="indane_logo.jpg" alt="Indane Logo" style="display: block; margin: 0 auto 20px auto; max-width: 150px;">
            <h1 class="page-header">Welcome, <b><?php echo htmlspecialchars($_SESSION["member_username"]); ?></b>!</h1>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <img src="customer_logo.jpg" alt="Customer Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Customer Information</h3>
                    <p>View existing customer details.</p>
                    <a href="member_view_customers.php" class="btn">View Customers</a>
                </div>
                <div class="dashboard-card">
                    <img src="transaction_logo.png" alt="Transaction Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Transaction History</h3>
                    <p>View all financial transactions.</p>
                    <a href="member_view_transaction.php" class="btn">View Transactions</a>
                </div>
                                <div class="dashboard-card">
                    <img src="money_logo.jpg" alt="Money Details Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>View Money</h3>
                    <p>View  history of cash deposits.</p>
                    <a href="member_view_money.php" class="btn">View Money</a>
                </div>
                <div class="dashboard-card">
                    <img src="bottle_logo.jpg" alt="Cylinder Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Cylinder Stock</h3>
                    <p>View current and historical cylinder stock.</p>
                    <a href="member_view_cylinders.php" class="btn">View Stock</a>
                </div>
                <div class="dashboard-card">
                    <img src="report_logo.png" alt="Report Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Report Dashboard</h3>
                    <p>View all financial reports and charts.</p>
                    <a href="member_report.php" class="btn" style="background-color: #17a2b8">View Reports</a>
                </div>
                <div class="dashboard-card">
                    <img src="other_stock_logo.webp" alt="Other Stock Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Other Stock</h3>
                    <p>View other stock like regulators, pipes, etc.</p>
                    <a href="member_view_other_stock.php" class="btn">View Other Stock</a>
                </div>

            </div>
            <div class="dashboard-footer" style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #dddfe2;">
                <a href="terms_and_conditions.php" style="margin: 0 15px; text-decoration: none; color: #4b4f56;">Terms & Conditions</a>
                <a href="privacy_policy.php" style="margin: 0 15px; text-decoration: none; color: #4b4f56;">Privacy Policy</a>
                <p style="margin-top: 15px; color: #8a8d91;">&copy; 2025 AmodIndane. All rights reserved.</p>
            </div>
        </div>
    </div>

</body>
</html>