<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: Admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AmodIndane - Admin Dashboard</title>
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
            <a href="history.php" title="History"><i class="fas fa-history"></i></a><a href="report.php" title="Reports"><i class="fas fa-chart-bar"></i></a>
            <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <div class="content-card">
            <img src="indane_logo.jpg" alt="Indane Logo" style="display: block; margin: 0 auto 20px auto; max-width: 150px;">
            <h1 class="page-header">Welcome, <b><?php echo htmlspecialchars($_SESSION["admin_username"]); ?></b>!</h1>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <img src="customer_logo.jpg" alt="Customer Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Customer Management</h3>
                    <p>Add new customers or view existing ones.</p>
                    <a href="add_customer.php" class="btn">Add Customer</a>
                    <a href="view_customers.php" class="btn" style="background-color: #6c757d; margin-top: 10px;">View Customers</a>
                </div>
                <div class="dashboard-card">
                    <img src="member_logo.jpeg" alt="Member Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Member Management</h3>
                    <p>Register new members or view existing ones.</p>
                    <a href="member_register.php" class="btn">Add Member</a>
                    <a href="view_members.php" class="btn" style="background-color: #6c757d; margin-top: 10px;">View Members</a>
                </div>
                <div class="dashboard-card">
                    <img src="money_logo.jpg" alt="Add Money Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Add Money</h3>
                    <p>Add cash deposits using a denomination calculator.</p>
                    <a href="add_money.php" class="btn">Add Money</a>
                    <a href="view_money.php" class="btn" style="background-color: #6c757d; margin-top: 10px;">View Money</a>
                </div>
                <div class="dashboard-card">
                    <img src="transaction_logo.png" alt="Transaction Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Transaction Management</h3>
                    <p>Record and view financial transactions.</p>
                    <a href="transaction_management.php" class="btn">Add Transaction</a>
                    <a href="view_transaction.php" class="btn" style="background-color: #6c757d; margin-top: 10px;">View Transactions</a>
                </div>
                <div class="dashboard-card">
                    <img src="bottle_logo.jpg" alt="Bottle Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Bottle Stock</h3>
                    <p>View and manage inventory for all cylinder types.</p>
                    <a href="add_cylinders.php" class="btn">Add Stock</a>
                    <a href="view_cylinders.php" class="btn" style="background-color: #6c757d; margin-top: 10px;">View Stock</a>
                </div>
                <div class="dashboard-card">
                    <img src="other_stock_logo.webp" alt="Other Stock Logo" style="max-width: 80px; margin-bottom: 15px;">
                    <h3>Other Stock</h3>
                    <p>Manage miscellaneous items like stoves, pipes, etc.</p>
                    <a href="add_other_stock.php" class="btn">Add Other Stock</a>
                    <a href="view_other_stock.php" class="btn" style="background-color: #6c757d; margin-top: 10px;">View Other Stock</a>
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
