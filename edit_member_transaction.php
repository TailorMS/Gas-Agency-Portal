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

// Check if transaction ID is provided in the URL
if(!isset($_GET["transaction_id"]) || empty(trim($_GET["transaction_id"]))){
    header("location: member_view_transaction.php");
    exit;
}

$original_transaction_id = trim($_GET["transaction_id"]);

// Define variables and initialize with empty values
$transaction_id = $transaction_date = $transaction_type = $name = $description = $money_in = $money_out = "";
$transaction_id_err = $transaction_date_err = $transaction_type_err = "";
$update_success = "";

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

    // Validate new transaction ID
    $new_transaction_id = trim($_POST["transaction_id"]);
    if(empty($new_transaction_id)){
        $transaction_id_err = "Transaction ID cannot be empty.";
    } elseif ($new_transaction_id != $original_transaction_id) {
        // If the ID has changed, check if the new one is already in use
        $sql_check = "SELECT transaction_id FROM transactions WHERE transaction_id = ?";
        if($stmt_check = $mysqli->prepare($sql_check)){
            $stmt_check->bind_param("s", $new_transaction_id);
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

    // Check input errors before updating in database
    if(empty($transaction_date_err) && empty($transaction_type_err) && empty($transaction_id_err)){

        $sql = "UPDATE transactions SET transaction_id=?, transaction_date=?, transaction_type=?, name=?, description=?, money_in=?, money_out=? WHERE transaction_id=?";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssssddss", $new_transaction_id, $transaction_date, $transaction_type, $name, $description, $money_in, $money_out, $original_transaction_id);

            if($stmt->execute()){

                // Log the action
                $log_action = "Edited transaction ID: " . $original_transaction_id . " to " . $new_transaction_id;
                $log_sql = "INSERT INTO member_history (member_id, member_username, action, target_type, target_id) VALUES (?, ?, ?, 'Transaction', ?)";
                if($log_stmt = $mysqli->prepare($log_sql)){
                    $log_stmt->bind_param("isss", $_SESSION["id"], $_SESSION["member_username"], $log_action, $new_transaction_id);
                    $log_stmt->execute();
                    $log_stmt->close();
                }
                // Show success alert and redirect
                echo "<script>alert('Transaction updated successfully!'); window.location.href='member_transaction_details.php?transaction_id=" . urlencode($new_transaction_id) . "';</script>";
                exit;
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

// Fetch current transaction data to display in the form
$sql = "SELECT transaction_id, transaction_date, transaction_type, name, description, money_in, money_out FROM transactions WHERE transaction_id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("s", $original_transaction_id);
    if($stmt->execute()){
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($transaction_id, $transaction_date, $transaction_type, $name, $description, $money_in, $money_out);
            $stmt->fetch();
        } else {
            header("location: member_view_transaction.php");
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
    <title>Edit Transaction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css"><style>
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
        

        <?php 
        if(!empty($update_success)){
            echo '<div class="error-message" style="background-color: #e7f3ff; border-color: #1877f2; color: #1c1e21; text-align: center; padding: 10px; margin-bottom: 20px; border-radius: 6px;">' . $update_success . '</div>';
        }        
        ?>

        <div class="profile-card">
            <h1 class="page-header">Edit Transaction</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?transaction_id=" . urlencode($original_transaction_id)); ?>" method="post">
                <div class="input-group">
                    <label>Transaction ID</label>
                    <input type="text" name="transaction_id" value="<?php echo htmlspecialchars($transaction_id); ?>" required>
                    <span style="color: #fa383e;"><?php echo $transaction_id_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Transaction Date</label>
                    <input type="date" name="transaction_date" value="<?php echo htmlspecialchars($transaction_date); ?>" required>
                    <span style="color: #fa383e;"><?php echo $transaction_date_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Transaction Type</label>
                    <select name="transaction_type" required style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;">
                        <option value="">-- Select Type --</option>
                        <option value="Cash" <?php if($transaction_type == 'Cash') echo 'selected'; ?>>Cash</option>
                        <option value="Online" <?php if($transaction_type == 'Online') echo 'selected'; ?>>Online</option>
                        <option value="UPI" <?php if($transaction_type == 'UPI') echo 'selected'; ?>>UPI</option>
                        <option value="Bank Transfer" <?php if($transaction_type == 'Bank Transfer') echo 'selected'; ?>>Bank Transfer</option>
                        <option value="Other" <?php if($transaction_type == 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                    <span style="color: #fa383e;"><?php echo $transaction_type_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Name (Optional)</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="input-group">
                    <label>Description</label>
                    <textarea name="description" rows="2" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div class="input-group" style="flex: 1;">
                        <label>Money In (Credit)</label>
                        <input type="number" name="money_in" step="0.01" placeholder="0.00" value="<?php echo htmlspecialchars($money_in); ?>">
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <label>Money Out (Debit)</label>
                        <input type="number" name="money_out" step="0.01" placeholder="0.00" value="<?php echo htmlspecialchars($money_out); ?>">
                    </div>
                </div>
                <button type="submit" class="btn">Update Transaction</button>
                <p style="text-align: center; margin-top: 20px;"><a href="member_transaction_details.php?transaction_id=<?php echo urlencode($original_transaction_id); ?>">Back to Details</a></p>
            </form>
        </div>
    </div>

</body>
</html>