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

$cylinder_stock = null;
$error_message = "";

// Check if ID is set in the URL
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $stock_id = trim($_GET["id"]);

    // Prepare a select statement
    $sql = "SELECT * FROM cylinder_stock WHERE id = ?";

    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $stock_id);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows == 1){
                // Fetch result row as an associative array.
                $cylinder_stock = $result->fetch_assoc();
            } else{
                // URL doesn't contain valid id.
                $error_message = "No record found with that ID.";
            }
        } else{
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
} else {
    // URL doesn't contain id parameter.
    $error_message = "No ID was specified.";
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cylinder Stock Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body { background-image: url('background_bg.webp'); background-size: cover; background-position: center; background-attachment: fixed; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .detail-item { background-color: #f9f9f9; padding: 10px; border-radius: 5px; }
        .detail-item label { font-weight: bold; color: #333; display: block; }
        .detail-item span { font-size: 1.1em; color: #555; }
    </style>
</head>
<body>

    <div class="menubar">
        <div class="logo"><a href="dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="history.php" title="History"><i class="fas fa-history"></i></a>
            <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Cylinder Stock Details</h1>

            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php elseif($cylinder_stock): ?>
                <div class="input-group">
                    <label>Stock Date</label>
                    <p><strong><?php echo htmlspecialchars($cylinder_stock['stock_date']); ?></strong></p>
                </div>

                <div class="form-grid">
                    <div class="detail-item"><label>5 kg:</label> <span><?php echo $cylinder_stock['qty_5_kg']; ?></span></div>
                    <div class="detail-item"><label>10 kg Composite:</label> <span><?php echo $cylinder_stock['qty_10_kg_composite']; ?></span></div>
                    <div class="detail-item"><label>Xtralite:</label> <span><?php echo $cylinder_stock['qty_xtralite']; ?></span></div>
                    <div class="detail-item"><label>14.2 kg:</label> <span><?php echo $cylinder_stock['qty_14_2_kg']; ?></span></div>
                    <div class="detail-item"><label>19 kg:</label> <span><?php echo $cylinder_stock['qty_19_kg']; ?></span></div>
                    <div class="detail-item"><label>Xtratej:</label> <span><?php echo $cylinder_stock['qty_xtratej']; ?></span></div>
                    <div class="detail-item"><label>47.5 kg:</label> <span><?php echo $cylinder_stock['qty_47_5_kg']; ?></span></div>
                    <div class="detail-item"><label>425 kg JUMBO:</label> <span><?php echo $cylinder_stock['qty_425_kg_jumbo']; ?></span></div>
                </div>

                <div class="input-group" style="margin-top: 20px;">
                    <label>Total Quantity</label>
                    <p><strong><?php echo htmlspecialchars($cylinder_stock['total_quantity']); ?></strong></p>
                </div>

                <div class="action-bar" style="margin-top: 20px;">
                    <a href="edit_cylinder_stock.php?id=<?php echo $cylinder_stock['id']; ?>" class="btn" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="download_cylinder_stock.php?id=<?php echo $cylinder_stock['id']; ?>" class="btn" style="background-color: #218838;" title="Download"><i class="fas fa-download"></i></a>
                    <a href="delete_cylinder_stock.php?id=<?php echo $cylinder_stock['id']; ?>" class="btn" style="background-color: #dc3545;" onclick="return confirm('Are you sure you want to delete this stock entry?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                    <a href="view_cylinders.php" class="btn" style="background-color: #6c757d;" title="Back to Stock List"><i class="fas fa-arrow-left"></i></a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>