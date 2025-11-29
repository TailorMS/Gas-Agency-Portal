<?php
session_start();
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){ header("location: member_login.php"); exit; }
require_once "db_connect.php";

$stock_id = trim($_GET["id"] ?? '');
$error_message = "";
$stock_date = "";
$stock_items = [];

if(empty($stock_id)){ header("location: member_view_other_stock.php"); exit; }

$labels = [
    'gas_stove_1_burner' => 'Stove 1 Burner', 'gas_stove_2_burner' => 'Stove 2 Burner', 'gas_stove_3_burner' => 'Stove 3 Burner',
    'gas_stove_4_burner' => 'Stove 4 Burner', 'regulator' => 'Regulator', 'hose_pipe' => 'Hose Pipe', 'fire_ball' => 'Fire Ball',
    'apron' => 'Apron', 'lighter' => 'Lighter', 'cylinder_stand' => 'Cylinder Stand', 'fire_bottle' => 'Fire Bottle'
];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $stock_date = $_POST['stock_date'];
    $total_quantity = 0;
    $update_values = [];

    foreach ($labels as $key => $label) {
        $update_values[$key] = (int)($_POST[$key] ?? 0);
        $total_quantity += $update_values[$key];
    }

    $set_clause = implode(", ", array_map(fn($col) => "$col = ?", array_keys($update_values)));
    $sql = "UPDATE other_stock SET stock_date = ?, $set_clause, total_quantity = ? WHERE id = ?";

    if($stmt = $mysqli->prepare($sql)){
        $types = 's' . str_repeat('i', count($update_values)) . 'ii';
        $params = array_merge([$stock_date], array_values($update_values), [$total_quantity, $stock_id]);
        
        if($stmt->bind_param($types, ...$params) && $stmt->execute()){
            // Log the action
            $log_action = "Edited other stock for date: " . $stock_date;
            $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Other Stock', ?)";
            if($log_stmt = $mysqli->prepare($log_sql)){
                $log_stmt->bind_param("isss", $_SESSION["id"], $_SESSION["member_username"], $log_action, $stock_id);
                $log_stmt->execute();
                $log_stmt->close();
            }
            header("location: member_other_stock_detail.php?id=" . $stock_id);
            exit;
        } else { $error_message = "Oops! Something went wrong."; }
        $stmt->close();
    }
} else {
    $sql = "SELECT * FROM other_stock WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $stock_id);
        if($stmt->execute()){
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $stock_items = $result->fetch_assoc();
                $stock_date = $stock_items['stock_date'];
            } else { $error_message = "No record found."; }
        } else { $error_message = "Oops! Something went wrong."; }
        $stmt->close();
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Other Stock</title>
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
        <nav class="menubar-nav"><a href="member_dashboard.php">Dashboard</a><a href="member_profile.php" title="Profile"><i class="fas fa-user"></i></a><a href="member_logout.php" class="logout">Logout</a></nav>
    </div>
    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Edit Other Stock</h1>
            <?php if(!empty($error_message)): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $stock_id; ?>" method="post">
                <div class="input-group"><label for="stock_date">Stock Date</label><input type="date" id="stock_date" name="stock_date" value="<?php echo htmlspecialchars($stock_date); ?>" required></div>
                <div class="form-grid">
                    <?php foreach ($labels as $key => $label): ?>
                    <div class="input-group">
                        <label for="<?php echo $key; ?>"><?php echo $label; ?></label>
                        <input type="number" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($stock_items[$key] ?? 0); ?>" min="0">
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="action-bar" style="margin-top: 20px;">
                    <button type="submit" class="btn">Update Stock</button>
                    <a href="member_other_stock_detail.php?id=<?php echo $stock_id; ?>" class="btn" style="background-color: #6c757d;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>