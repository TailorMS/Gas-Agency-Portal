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
$description = $money_in = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Set default values
    $transaction_date = date('Y-m-d');
    $transaction_type = "Cash";
    $name = "Cash Deposit";
    
    $money_in = trim($_POST["total_amount"]);

    // Build description from denomination counts
    $description_parts = [];
    $denominations = [500, 200, 100, 50, 10];
    foreach ($denominations as $denom) {
        if (!empty($_POST['denom_' . $denom])) {
            $count = (int)$_POST['denom_' . $denom];
            $description_parts[] = "₹" . $denom . " x " . $count;
        }
    }
    $description = implode(', ', $description_parts);

    // Check input errors before inserting in database
    if(!empty($money_in) && $money_in > 0){

        // Start transaction
        $mysqli->begin_transaction();

        try {
            // Insert into cash_deposits table
            $count_500 = (int)($_POST['denom_500'] ?? 0); // Null coalescing operator for safety
            $count_200 = (int)($_POST['denom_200'] ?? 0);
            $count_100 = (int)($_POST['denom_100'] ?? 0);
            $count_50 = (int)($_POST['denom_50'] ?? 0);
            $count_10 = (int)($_POST['denom_10'] ?? 0);

            $sql_deposit = "INSERT INTO cash_deposits (deposit_date, count_500, count_200, count_100, count_50, count_10, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_deposit = $mysqli->prepare($sql_deposit);
            $stmt_deposit->bind_param("siiiiid", $transaction_date, $count_500, $count_200, $count_100, $count_50, $count_10, $money_in);
            $stmt_deposit->execute();
            $stmt_deposit->close();

            // If all good, commit the transaction
            $mysqli->commit();

            echo "<script>alert('Cash deposit added successfully.'); window.location.href='add_money.php';</script>";
            exit;

        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            echo "Oops! Something went wrong. Please try again later.";
            // You might want to log the error: error_log($exception->getMessage());
        }

    } else {
        echo "<script>alert('Total amount must be greater than zero.');</script>";
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Money</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body { background-image: url('background_bg.webp'); background-size: cover; background-position: center; background-attachment: fixed; }
        .calculator-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; align-items: center; gap: 15px; margin-bottom: 15px; }
        .calculator-grid label { font-weight: bold; }
        .calculator-grid input { text-align: right; }
        .total-row { font-size: 1.2em; font-weight: bold; text-align: right; margin-top: 20px; }
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
        <h1 class="page-header">Cash Deposit Calculator</h1>

        <div class="profile-card">
            <h2 style="text-align: center; margin-bottom: 20px;">Calculate and Add Cash Deposit</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="cash-form">
                <h3 style="margin-top: 30px; margin-bottom: 15px;">Enter Denomination Counts</h3>
                <div class="calculator-grid">
                    <label>Denomination (₹)</label>
                    <label>Count</label>
                    <label>Total (₹)</label>

                    <?php
                    $denominations = [500, 200, 100, 50, 10];
                    foreach ($denominations as $denom) {
                        echo '<label>₹ ' . $denom . '</label>';
                        echo '<input type="number" class="denom-count" name="denom_' . $denom . '" data-value="' . $denom . '" min="0" placeholder="0">';
                        echo '<input type="text" class="denom-total" id="total_' . $denom . '" value="0.00" readonly>';
                    }
                    ?>
                </div>

                <div class="total-row">
                    Grand Total: ₹ <span id="grand-total">0.00</span>
                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                </div>

                <button type="submit" class="btn" style="margin-top: 20px;">Add Money</button>
                <a href="dashboard.php" class="btn" style="display: block; width: fit-content; margin: 20px auto 0; background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cash-form');
    const grandTotalSpan = document.getElementById('grand-total');
    const totalAmountInput = document.getElementById('total_amount');

    function calculateTotal() {
        let grandTotal = 0;
        const counts = form.querySelectorAll('.denom-count');
        counts.forEach(input => {
            const value = parseFloat(input.dataset.value);
            const count = parseInt(input.value) || 0;
            const total = value * count;
            grandTotal += total;
            document.getElementById('total_' + value).value = total.toFixed(2);
        });
        grandTotalSpan.textContent = grandTotal.toFixed(2);
        totalAmountInput.value = grandTotal.toFixed(2);
    }

    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('denom-count')) {
            calculateTotal();
        }
    });

    calculateTotal(); // Initial calculation
});
</script>

</body>
</html>