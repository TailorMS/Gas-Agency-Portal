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

$stock_date = date("Y-m-d");
$success_message = $error_message = "";

$stock_items = [
    'gas_stove_1_burner' => 0, 'gas_stove_2_burner' => 0, 'gas_stove_3_burner' => 0,
    'gas_stove_4_burner' => 0, 'regulator' => 0, 'hose_pipe' => 0, 'fire_ball' => 0,
    'apron' => 0, 'lighter' => 0, 'cylinder_stand' => 0, 'fire_bottle' => 0,
];

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $stock_date = $_POST['stock_date'];
    $total_quantity = 0;

    foreach ($stock_items as $key => &$value) {
        $value = (int)($_POST[$key] ?? 0);
        $total_quantity += $value;
    }
    unset($value);

    // Check if a record for this date already exists
    $sql_check = "SELECT id FROM other_stock WHERE stock_date = ?";
    if($stmt_check = $mysqli->prepare($sql_check)){
        $stmt_check->bind_param("s", $stock_date);
        $stmt_check->execute();
        $stmt_check->store_result();

        if($stmt_check->num_rows > 0){
            $error_message = "Stock for this date has already been added. You can edit it from the view page.";
        } else {
            $columns = implode(", ", array_keys($stock_items));
            $placeholders = implode(", ", array_fill(0, count($stock_items), '?'));
            $sql = "INSERT INTO other_stock (stock_date, $columns, total_quantity) VALUES (?, $placeholders, ?)";

            if($stmt = $mysqli->prepare($sql)){
                $types = 's' . str_repeat('i', count($stock_items)) . 'i';
                $params_to_bind = array_merge([$stock_date], array_values($stock_items), [$total_quantity]);

                if($stmt->bind_param($types, ...$params_to_bind) && $stmt->execute()){
                    $success_message = "Other stock added successfully!";
                } else{
                    $error_message = "Oops! Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
        $stmt_check->close();
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Other Stock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body { background-image: url('background_bg.webp'); background-size: cover; background-position: center; background-attachment: fixed; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
    <div class="menubar">
        <div class="logo"><a href="dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav"><a href="dashboard.php">Dashboard</a><a href="history.php" title="History"><i class="fas fa-history"></i></a><a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a><a href="logout.php" class="logout">Logout</a></nav>
    </div>
    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Add Daily Other Stock</h1>
            <?php if(!empty($success_message)): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
            <?php if(!empty($error_message)): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group"><label for="stock_date">Stock Date</label><input type="date" id="stock_date" name="stock_date" value="<?php echo $stock_date; ?>" required></div>
                <div class="form-grid">
                    <?php
                    $labels = [
                        'gas_stove_1_burner' => 'Stove 1 Burner', 'gas_stove_2_burner' => 'Stove 2 Burner', 'gas_stove_3_burner' => 'Stove 3 Burner',
                        'gas_stove_4_burner' => 'Stove 4 Burner', 'regulator' => 'Regulator', 'hose_pipe' => 'Hose Pipe', 'fire_ball' => 'Fire Ball',
                        'apron' => 'Apron', 'lighter' => 'Lighter', 'cylinder_stand' => 'Cylinder Stand', 'fire_bottle' => 'Fire Bottle'
                    ];
                    foreach ($labels as $key => $label): ?>
                    <div class="input-group">
                        <label for="<?php echo $key; ?>"><?php echo $label; ?></label>
                        <input type="number" name="<?php echo $key; ?>" value="0" min="0">
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="action-bar" style="margin-top: 20px;">
                    <button type="submit" class="btn">Add Stock</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>