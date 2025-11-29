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

// Check if deposit ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_money.php");
    exit;
}

$deposit_id = trim($_GET["id"]);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $count_500 = (int)($_POST['denom_500'] ?? 0);
    $count_200 = (int)($_POST['denom_200'] ?? 0);
    $count_100 = (int)($_POST['denom_100'] ?? 0);
    $count_50 = (int)($_POST['denom_50'] ?? 0);
    $count_10 = (int)($_POST['denom_10'] ?? 0);
    $total_amount = trim($_POST["total_amount"]);

    if(!empty($total_amount) && $total_amount >= 0){
        $sql = "UPDATE cash_deposits SET count_500 = ?, count_200 = ?, count_100 = ?, count_50 = ?, count_10 = ?, total_amount = ? WHERE id = ?";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("iiiiidi", $count_500, $count_200, $count_100, $count_50, $count_10, $total_amount, $deposit_id);

            if($stmt->execute()){
                echo "<script>alert('Deposit updated successfully.'); window.location.href='money_details.php?id=" . $deposit_id . "';</script>";
                exit;
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('Total amount cannot be empty.');</script>";
    }
}

// Fetch current deposit data to display in the form
$deposit = null;
$sql = "SELECT id, deposit_date, count_500, count_200, count_100, count_50, count_10, total_amount FROM cash_deposits WHERE id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $deposit_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $deposit = $result->fetch_assoc();
        } else {
            header("location: view_money.php");
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
    <title>Edit Cash Deposit</title>
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
        <h1 class="page-header">Edit Cash Deposit</h1>

        <div class="profile-card">
            <h2 style="text-align: center; margin-bottom: 20px;">Update Denomination Counts</h2>
            <p style="text-align: center; margin-bottom: 20px;">Deposit Date: <strong><?php echo date("M j, Y", strtotime($deposit['deposit_date'])); ?></strong></p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $deposit_id); ?>" method="post" id="cash-form">
                <div class="calculator-grid">
                    <label>Denomination (₹)</label>
                    <label>Count</label>
                    <label>Total (₹)</label>

                    <?php
                    $denominations = [500, 200, 100, 50, 10];
                    foreach ($denominations as $denom) {
                        $count = $deposit['count_' . $denom] ?? 0;
                        echo '<label>₹ ' . $denom . '</label>';
                        echo '<input type="number" class="denom-count" name="denom_' . $denom . '" data-value="' . $denom . '" min="0" value="' . $count . '">';
                        echo '<input type="text" class="denom-total" id="total_' . $denom . '" value="0.00" readonly>';
                    }
                    ?>
                </div>

                <div class="total-row">
                    Grand Total: ₹ <span id="grand-total">0.00</span>
                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                </div>

                <button type="submit" class="btn" style="margin-top: 20px;">Update Deposit</button>
                <a href="money_details.php?id=<?php echo $deposit_id; ?>" class="btn" style="display: block; width: fit-content; margin: 20px auto 0; background-color: #6c757d;" title="Back to Details"><i class="fas fa-arrow-left"></i></a>
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
            document.getElementById('total_' .concat(value)).value = total.toFixed(2);
        });
        grandTotalSpan.textContent = grandTotal.toFixed(2);
        totalAmountInput.value = grandTotal.toFixed(2);
    }

    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('denom-count')) {
            calculateTotal();
        }
    });

    calculateTotal(); // Initial calculation on page load
});
</script>

</body>
</html>