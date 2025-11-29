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

// Check if stock ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: member_view_cylinders.php");
    exit;
}

$stock_id = trim($_GET["id"]);
$stock = null;

// Prepare a select statement to get all stock details
$sql = "SELECT * FROM cylinder_stock WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $stock_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $stock = $result->fetch_assoc();
        } else {
            // No record found, redirect
            header("location: member_view_cylinders.php");
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
    <title>AmodIndane - Cylinder Details</title>
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
            <a href="member_dashboard.php">Dashboard</a>
            <a href="member_profile.php" title="Profile"><i class="fas fa-user"></i></a>
            <a href="member_logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <div class="content-card">
            <?php if ($stock): ?>
            <h1 class="page-header">Cylinder Stock for <?php echo date("M j, Y", strtotime($stock['stock_date'])); ?></h1>

            <div class="dashboard-grid" style="margin-top: 30px;">
                <div class="dashboard-card"><h3>5 kg</h3><p style="font-size: 2em; color: #1877f2; margin: 10px 0;"><?php echo htmlspecialchars($stock['qty_5_kg']); ?></p></div>
                <div class="dashboard-card"><h3>10 kg Comp.</h3><p style="font-size: 2em; color: #1877f2; margin: 10px 0;"><?php echo htmlspecialchars($stock['qty_10_kg_composite']); ?></p></div>
                <div class="dashboard-card"><h3>Xtralite</h3><p style="font-size: 2em; color: #1877f2; margin: 10px 0;"><?php echo htmlspecialchars($stock['qty_xtralite']); ?></p></div>
                <div class="dashboard-card"><h3>14.2 kg</h3><p style="font-size: 2em; color: #1877f2; margin: 10px 0;"><?php echo htmlspecialchars($stock['qty_14_2_kg']); ?></p></div>
                <div class="dashboard-card"><h3>19 kg</h3><p style="font-size: 2em; color: #1877f2; margin: 10px 0;"><?php echo htmlspecialchars($stock['qty_19_kg']); ?></p></div>
                <div class="dashboard-card"><h3>Xtratej</h3><p style="font-size: 2em; color: #1877f2; margin: 10px 0;"><?php echo htmlspecialchars($stock['qty_xtratej']); ?></p></div>
                <div class="dashboard-card"><h3>47.5 kg</h3><p style="font-size: 2em; color: #1877f2; margin: 10px 0;"><?php echo htmlspecialchars($stock['qty_47_5_kg']); ?></p></div>
                <div class="dashboard-card"><h3>425 kg Jumbo</h3><p style="font-size: 2em; color: #1877f2; margin: 10px 0;"><?php echo htmlspecialchars($stock['qty_425_kg_jumbo']); ?></p></div>
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #dddfe2;">
            
            <div style="text-align: center; margin: 30px 0;">
                <h3 style="color: #4b4f56; margin-bottom: 10px;">Total Quantity</h3>
                <p style="font-size: 2.5em; color: green; font-weight: bold; margin: 0;"><?php echo htmlspecialchars($stock['total_quantity']); ?></p>
            </div>

            <?php endif; ?>

            <div style="margin-top: 20px;">
                <a href="edit_member_cylinder_stock.php?id=<?php echo $stock['id']; ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px;" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="download_member_cylinder.php?id=<?php echo $stock['id']; ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #218838;" title="Download"><i class="fas fa-download"></i></a>
                <a href="delete_member_cylinder_stock.php?id=<?php echo $stock['id']; ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #dc3545;" onclick="return confirm('Are you sure you want to delete this stock record?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                <a href="member_view_cylinders.php" class="btn" style="display: inline-block; width: auto; background-color: #6c757d;" title="Back to Stock List"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>

</body>
</html>