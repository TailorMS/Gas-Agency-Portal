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

// Define variables and initialize with empty values
$stock_date = date("Y-m-d");
$cylinder_counts = [
    'qty_5_kg' => 0,
    'qty_10_kg_composite' => 0,
    'qty_xtralite' => 0,
    'qty_14_2_kg' => 0,
    'qty_19_kg' => 0,
    'qty_xtratej' => 0,
    'qty_47_5_kg' => 0,
    'qty_425_kg_jumbo' => 0,
];
$success_message = $error_message = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $stock_date = $_POST['stock_date'];

    $total_quantity = 0;
    foreach ($cylinder_counts as $key => &$value) {
        $value = (int)($_POST[$key] ?? 0);
        $total_quantity += $value;
    }
    unset($value);

    // Check if a record for this date already exists
    $sql_check = "SELECT id FROM cylinder_stock WHERE stock_date = ?";
    if($stmt_check = $mysqli->prepare($sql_check)){
        $stmt_check->bind_param("s", $stock_date);
        $stmt_check->execute();
        $stmt_check->store_result();

        if($stmt_check->num_rows > 0){
            $error_message = "Stock for this date has already been added. You can edit it from the view page.";
        } else {
            // Prepare an insert statement
            $sql = "INSERT INTO cylinder_stock (stock_date, qty_5_kg, qty_10_kg_composite, qty_xtralite, qty_14_2_kg, qty_19_kg, qty_xtratej, qty_47_5_kg, qty_425_kg_jumbo, total_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if($stmt = $mysqli->prepare($sql)){
                // Add total_quantity to the array for binding
                $params_to_bind = array_merge([$stock_date], array_values($cylinder_counts), [$total_quantity]);

                if($stmt->bind_param("siiiiiiiii", ...$params_to_bind) && $stmt->execute()){
                    $success_message = "Cylinder stock added successfully!";
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
    <title>Add Cylinder Stock</title>
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
        <nav class="menubar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="history.php" title="History"><i class="fas fa-history"></i></a>
            <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Add Daily Cylinder Stock</h1>

            <?php if(!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <label for="stock_date">Stock Date</label>
                    <input type="date" id="stock_date" name="stock_date" value="<?php echo $stock_date; ?>" required>
                </div>

                <div class="form-grid">
                    <div class="input-group">
                        <label for="qty_5_kg">5 kg</label>
                        <input type="number" name="qty_5_kg" value="0" min="0">
                    </div>
                    <div class="input-group">
                        <label for="qty_10_kg_composite">10 kg Composite</label>
                        <input type="number" name="qty_10_kg_composite" value="0" min="0">
                    </div>
                    <div class="input-group">
                        <label for="qty_xtralite">Xtralite</label>
                        <input type="number" name="qty_xtralite" value="0" min="0">
                    </div>
                    <div class="input-group">
                        <label for="qty_14_2_kg">14.2 kg</label>
                        <input type="number" name="qty_14_2_kg" value="0" min="0">
                    </div>
                    <div class="input-group">
                        <label for="qty_19_kg">19 kg</label>
                        <input type="number" name="qty_19_kg" value="0" min="0">
                    </div>
                    <div class="input-group">
                        <label for="qty_xtratej">Xtratej</label>
                        <input type="number" name="qty_xtratej" value="0" min="0">
                    </div>
                    <div class="input-group">
                        <label for="qty_47_5_kg">47.5 kg</label>
                        <input type="number" name="qty_47_5_kg" value="0" min="0">
                    </div>
                    <div class="input-group">
                        <label for="qty_425_kg_jumbo">425 kg JUMBO</label>
                        <input type="number" name="qty_425_kg_jumbo" value="0" min="0">
                    </div>
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