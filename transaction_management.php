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
$transaction_id = $transaction_date = $transaction_type = $name = $description = $money_in = $money_out = "";
$transaction_id_err = $transaction_date_err = $transaction_type_err = "";

// Check for import messages from session
$import_message = $_SESSION['import_message'] ?? '';
$import_status = $_SESSION['import_status'] ?? '';
if (!empty($import_message)) {
    unset($_SESSION['import_message']);
    unset($_SESSION['import_status']);
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate transaction date
    if(empty(trim($_POST["transaction_date"]))){
        $transaction_date_err = "Please enter the transaction date.";
    } else{
        $transaction_date = trim($_POST["transaction_date"]);
    }

    // Validate transaction type
    if(empty(trim($_POST["transaction_type"]))){
        $transaction_type_err = "Please select a transaction type.";
    } else{
        $transaction_type = trim($_POST["transaction_type"]);
    }

    // Validate custom transaction ID
    $transaction_id = trim($_POST["transaction_id"]);
    if(empty($transaction_id)){
        $transaction_id_err = "Please enter a Transaction ID.";
    } else {
        $sql_check = "SELECT transaction_id FROM transactions WHERE transaction_id = ?";
        if($stmt_check = $mysqli->prepare($sql_check)){
            $stmt_check->bind_param("s", $transaction_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if($stmt_check->num_rows > 0) $transaction_id_err = "This Transaction ID is already in use.";
            $stmt_check->close();
        }
    }

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $money_in = !empty($_POST["money_in"]) ? trim($_POST["money_in"]) : 0;
    $money_out = !empty($_POST["money_out"]) ? trim($_POST["money_out"]) : 0;

    // Check input errors before inserting in database
    if(empty($transaction_date_err) && empty($transaction_type_err) && empty($transaction_id_err)){

        $sql = "INSERT INTO transactions (transaction_id, transaction_date, transaction_type, name, description, money_in, money_out) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sssssdd", $transaction_id, $transaction_date, $transaction_type, $name, $description, $money_in, $money_out);

            if($stmt->execute()){
                // Redirect to transaction management page to prevent form resubmission
                // Show success alert and refresh the page
                echo "<script>alert('New transaction added successfully.'); window.location.href='transaction_management.php';</script>";
                exit; // Important to prevent further execution
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Transaction</title>
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
            <a href="history.php" title="History"><i class="fas fa-history"></i></a>
            <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <h1 class="page-header">Add New Transaction</h1>

        <?php 
        if(!empty($import_message)){
            $style = 'background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;'; // Default to error
            if ($import_status === 'success') {
                $style = 'background-color: #d4edda; color: #155724; border-color: #c3e6cb;';
            } elseif ($import_status === 'warning') {
                $style = 'background-color: #fff3cd; color: #856404; border-color: #ffeeba;';
            }
            echo '<div style="max-width: 800px; margin: 0 auto 20px auto; padding: 15px; border: 1px solid transparent; border-radius: .25rem; ' . $style . '">' . nl2br($import_message) . '</div>';
        }        
        ?>

        <!-- Add Transaction Form -->
        <div class="profile-card" style="margin-bottom: 40px;">
            <h2 style="text-align: center; margin-bottom: 20px;">Add New Transaction</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <label>Transaction ID</label>
                    <input type="text" name="transaction_id" value="<?php echo htmlspecialchars($transaction_id); ?>" required>
                    <span style="color: #fa383e;"><?php echo $transaction_id_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Transaction Date</label>
                    <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                    <span style="color: #fa383e;"><?php echo $transaction_date_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Transaction Type</label>
                    <select name="transaction_type" required style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;">
                        <option value="">-- Select Type --</option>
                        <option value="Cash">Cash</option>
                        <option value="Online">Online</option>
                        <option value="UPI">UPI</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Other">Other</option>
                    </select>
                    <span style="color: #fa383e;"><?php echo $transaction_type_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Name (Optional)</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="input-group">
                    <label>Description</label>
                    <textarea name="description" rows="2" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"></textarea>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div class="input-group" style="flex: 1;">
                        <label>Money In (Credit)</label>
                        <input type="number" name="money_in" step="0.01" placeholder="0.00">
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <label>Money Out (Debit)</label>
                        <input type="number" name="money_out" step="0.01" placeholder="0.00">
                    </div>
                </div>
                <button type="submit" class="btn">Add Transaction</button>
                <a href="dashboard.php" class="btn" style="display: block; width: fit-content; margin: 20px auto 0; background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
            </form>
        </div>

        <!-- Import Transactions Form -->
        <div class="profile-card" style="margin-top: 40px;">
            <h2 class="page-header" style="font-size: 22px; margin-bottom: 10px;">Import Transactions from Excel (CSV)</h2>
            <p style="text-align: left; margin-bottom: 15px; font-size: 14px;">
                Upload a CSV file with the following columns in order: <br>
                <strong>Transaction ID, Transaction Date (YYYY-MM-DD), Transaction Type, Name, Description, Money In, Money Out</strong>
            </p>
            <form action="import_transactions.php" method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <label>Select CSV File</label><input type="file" name="transaction_csv" accept=".csv" required>
                </div>
                <button type="submit" name="import" class="btn">Import Transactions</button>
            </form>
        </div>
    </div>

</body>
</html>