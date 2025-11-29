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

$stock_id = trim($_GET["id"] ?? '');
$stock_date = "";
$cylinder_counts = [];
$error_message = $success_message = "";

// Redirect if ID is not provided
if(empty($stock_id)){
    header("location: member_view_cylinders.php");
    exit;
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $stock_id = $_POST['id'];
    $stock_date = $_POST['stock_date'];

    $cylinder_counts = [
        'qty_5_kg' => (int)($_POST['qty_5_kg'] ?? 0),
        'qty_10_kg_composite' => (int)($_POST['qty_10_kg_composite'] ?? 0),
        'qty_xtralite' => (int)($_POST['qty_xtralite'] ?? 0),
        'qty_14_2_kg' => (int)($_POST['qty_14_2_kg'] ?? 0),
        'qty_19_kg' => (int)($_POST['qty_19_kg'] ?? 0),
        'qty_xtratej' => (int)($_POST['qty_xtratej'] ?? 0),
        'qty_47_5_kg' => (int)($_POST['qty_47_5_kg'] ?? 0),
        'qty_425_kg_jumbo' => (int)($_POST['qty_425_kg_jumbo'] ?? 0),
    ];

    $total_quantity = array_sum($cylinder_counts);

    // Prepare an update statement
    $sql = "UPDATE cylinder_stock SET stock_date=?, qty_5_kg=?, qty_10_kg_composite=?, qty_xtralite=?, qty_14_2_kg=?, qty_19_kg=?, qty_xtratej=?, qty_47_5_kg=?, qty_425_kg_jumbo=?, total_quantity=? WHERE id=?";

    if($stmt = $mysqli->prepare($sql)){
        $params_to_bind = array_merge([$stock_date], array_values($cylinder_counts), [$total_quantity, $stock_id]);
        
        if($stmt->bind_param("siiiiiiiiii", ...$params_to_bind) && $stmt->execute()){
            // Log the action
            $log_action = "Edited cylinder stock for date: " . $stock_date;
            $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Cylinder Stock', ?)";
            if($log_stmt = $mysqli->prepare($log_sql)){
                $log_stmt->bind_param("isss", $_SESSION["id"], $_SESSION["member_username"], $log_action, $stock_id);
                $log_stmt->execute();
                $log_stmt->close();
            }

            // Redirect to the details page upon successful update
            header("location: member_cylinder_detail.php?id=" . $stock_id);
            exit;
        } else{
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
} else {
    // Fetch existing data to pre-fill the form
    $sql = "SELECT * FROM cylinder_stock WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $stock_id);
        if($stmt->execute()){
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                $stock_date = $row['stock_date'];
                $cylinder_counts = [
                    'qty_5_kg' => $row['qty_5_kg'],
                    'qty_10_kg_composite' => $row['qty_10_kg_composite'],
                    'qty_xtralite' => $row['qty_xtralite'],
                    'qty_14_2_kg' => $row['qty_14_2_kg'],
                    'qty_19_kg' => $row['qty_19_kg'],
                    'qty_xtratej' => $row['qty_xtratej'],
                    'qty_47_5_kg' => $row['qty_47_5_kg'],
                    'qty_425_kg_jumbo' => $row['qty_425_kg_jumbo'],
                ];
            } else {
                $error_message = "No record found with that ID.";
            }
        } else {
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Cylinder Stock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body { background-image: url('background_bg.webp'); background-size: cover; background-position: center; background-attachment: fixed; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
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
            <h1 class="page-header">Edit Cylinder Stock</h1>

            <?php if(!empty($error_message)): ?>
                <div class="error-message" style="background-color: #f8d7da; color: #721c24;"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $stock_id; ?>" method="post">
                <input type="hidden" name="id" value="<?php echo $stock_id; ?>"/>
                <div class="input-group">
                    <label for="stock_date">Stock Date</label>
                    <input type="date" id="stock_date" name="stock_date" value="<?php echo htmlspecialchars($stock_date); ?>" required>
                </div>

                <div class="form-grid">
                    <?php
                    $labels = [
                        'qty_5_kg' => '5 kg', 'qty_10_kg_composite' => '10 kg Composite', 'qty_xtralite' => 'Xtralite',
                        'qty_14_2_kg' => '14.2 kg', 'qty_19_kg' => '19 kg', 'qty_xtratej' => 'Xtratej',
                        'qty_47_5_kg' => '47.5 kg', 'qty_425_kg_jumbo' => '425 kg JUMBO'
                    ];
                    foreach ($labels as $key => $label): ?>
                    <div class="input-group">
                        <label for="<?php echo $key; ?>"><?php echo $label; ?></label>
                        <input type="number" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($cylinder_counts[$key] ?? 0); ?>" min="0">
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="action-bar" style="margin-top: 20px;">
                    <button type="submit" class="btn">Update Stock</button>
                    <a href="member_cylinder_detail.php?id=<?php echo $stock_id; ?>" class="btn" style="background-color: #6c757d;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>