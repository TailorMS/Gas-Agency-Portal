<?php
session_start();
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){ header("location: Admin.php"); exit; }
require_once "db_connect.php";

$stock = null;
$error_message = "";

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $stock_id = trim($_GET["id"]);
    $sql = "SELECT * FROM other_stock WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $stock_id);
        if($stmt->execute()){
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $stock = $result->fetch_assoc();
            } else{ $error_message = "No record found with that ID."; }
        } else{ $error_message = "Oops! Something went wrong."; }
        $stmt->close();
    }
} else { $error_message = "No ID was specified."; }
$mysqli->close();

$labels = [
    'gas_stove_1_burner' => 'Stove 1 Burner', 'gas_stove_2_burner' => 'Stove 2 Burner', 'gas_stove_3_burner' => 'Stove 3 Burner',
    'gas_stove_4_burner' => 'Stove 4 Burner', 'regulator' => 'Regulator', 'hose_pipe' => 'Hose Pipe', 'fire_ball' => 'Fire Ball',
    'apron' => 'Apron', 'lighter' => 'Lighter', 'cylinder_stand' => 'Cylinder Stand', 'fire_bottle' => 'Fire Bottle'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Other Stock Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body { background-image: url('background_bg.webp'); background-size: cover; background-position: center; background-attachment: fixed; }
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }
        .detail-item { background-color: #f9f9f9; padding: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .detail-item strong { display: block; color: #333; margin-bottom: 5px; }
        .detail-item span { font-size: 1.5em; color: #1877f2; font-weight: bold; }
    </style>
</head>
<body>
    <div class="menubar">
        <div class="logo"><a href="dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav"><a href="dashboard.php">Dashboard</a><a href="history.php" title="History"><i class="fas fa-history"></i></a><a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a><a href="logout.php" class="logout">Logout</a></nav>
    </div>
    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Other Stock Details</h1>
            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php elseif($stock): ?>
                <p><strong>Stock Date: <?php echo date("M j, Y", strtotime($stock['stock_date'])); ?></strong></p>
                <div class="detail-grid">
                    <?php foreach ($labels as $key => $label): ?>
                    <div class="detail-item">
                        <strong><?php echo $label; ?>:</strong>
                        <span><?php echo htmlspecialchars($stock[$key]); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr style="margin: 20px 0;">
                <div class="detail-item" style="background-color: #e7f3ff;">
                    <strong>Total Quantity:</strong>
                    <span><?php echo htmlspecialchars($stock['total_quantity']); ?></span>
                </div>
                <div class="action-bar" style="margin-top: 20px;">
                    <a href="edit_other_stock.php?id=<?php echo $stock['id']; ?>" class="btn" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="download_other_stock.php?id=<?php echo $stock['id']; ?>" class="btn" style="background-color: #218838;" title="Download"><i class="fas fa-download"></i></a>
                    <a href="delete_other_stock.php?id=<?php echo $stock['id']; ?>" class="btn" style="background-color: #dc3545;" onclick="return confirm('Are you sure?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                    <a href="view_other_stock.php" class="btn" style="background-color: #6c757d;" title="Back to Stock List"><i class="fas fa-arrow-left"></i></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>