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
$name = $mobile_no = $customer_no = $aadhar_no = $ration_card_no = $address = $bank_details = "";
$name_err = $mobile_no_err = $customer_no_err = "";

// Check for import messages from session
$import_message = $_SESSION['import_message'] ?? '';
$import_status = $_SESSION['import_status'] ?? '';
if (!empty($import_message)) {
    unset($_SESSION['import_message']);
    unset($_SESSION['import_status']);
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate Customer Number
    if(empty(trim($_POST["customer_no"]))){
        $customer_no_err = "Please enter a customer number.";
    } else {
        $sql = "SELECT id FROM customers WHERE customer_no = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $_POST["customer_no"]);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0) $customer_no_err = "This customer number is already taken.";
            else $customer_no = trim($_POST["customer_no"]);
        }
    }

    // Validate name
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter a name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate mobile number
    if(empty(trim($_POST["mobile_no"]))){
        $mobile_no_err = "Please enter a mobile number.";
    } else {
        $mobile_no = trim($_POST["mobile_no"]);
    }

    $aadhar_no = trim($_POST['aadhar_no']);
    $ration_card_no = trim($_POST['ration_card_no']);
    $address = trim($_POST['address']);
    $bank_details = trim($_POST['bank_details']);

    // Check input errors before inserting in database
    if(empty($name_err) && empty($mobile_no_err) && empty($customer_no_err)){

        $sql = "INSERT INTO customers (customer_no, name, mobile_no, aadhar_no, ration_card_no, address, bank_details) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sssssss", $param_customer_no, $param_name, $param_mobile, $param_aadhar, $param_ration, $param_address, $param_bank);

            $param_customer_no = $customer_no;
            $param_name = $name;
            $param_mobile = $mobile_no;
            $param_aadhar = $aadhar_no;
            $param_ration = $ration_card_no;
            $param_address = $address;
            $param_bank = $bank_details;

            if($stmt->execute()){
                echo "<script>alert('New customer added successfully.'); window.location.href='add_customer.php';</script>";
                exit;
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Customer</title>
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
        

        <?php 
        if(!empty($import_message)){
            $msg_class = 'error-message';
            if ($import_status === 'success') {
                $msg_class .= ' success-message'; // You can add a .success-message class to your CSS for green color
            } elseif ($import_status === 'warning') {
                $msg_class .= ' warning-message'; // And a .warning-message for yellow/orange
            }
            // For now, we'll use inline styles for demonstration
            $style = ($import_status === 'success') ? 'background-color: #d4edda; color: #155724; border-color: #c3e6cb;' : 'background-color: #fff3cd; color: #856404; border-color: #ffeeba;';
            echo '<div class="' . $msg_class . '" style="max-width: 600px; margin: 0 auto 20px auto; ' . $style . '">' . $import_message . '</div>';
        }        
        ?>

        <div class="profile-card">
            <h1 class="page-header">Add New Customer</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <label>Customer Number</label>
                    <input type="text" name="customer_no" value="<?php echo htmlspecialchars($customer_no); ?>">
                    <span style="color: #fa383e;"><?php echo $customer_no_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
                    <span style="color: #fa383e;"><?php echo $name_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Mobile No</label>
                    <input type="text" name="mobile_no" value="<?php echo htmlspecialchars($mobile_no); ?>">
                    <span style="color: #fa383e;"><?php echo $mobile_no_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Aadhar Number</label>
                    <input type="text" name="aadhar_no" value="<?php echo htmlspecialchars($aadhar_no); ?>">
                </div>
                <div class="input-group">
                    <label>Ration Card No</label>
                    <input type="text" name="ration_card_no" value="<?php echo htmlspecialchars($ration_card_no); ?>">
                </div>
                <div class="input-group">
                    <label>Address</label>
                    <textarea name="address" rows="3" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
                <div class="input-group">
                    <label>Bank Details</label>
                    <textarea name="bank_details" rows="3" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($bank_details); ?></textarea>
                </div>
                <button type="submit" class="btn">Add Customer</button>
            </form>
            <a href="dashboard.php" class="btn" style="display: block; width: fit-content; margin: 20px auto 0; background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
        </div>

        <div class="profile-card" style="margin-top: 40px;">
            <h2 class="page-header" style="font-size: 22px; margin-bottom: 10px;">Import Customers from Excel (CSV)</h2>
            <p style="text-align: left; margin-bottom: 15px; font-size: 14px;">
                Upload a CSV file with the following columns in order: <br>
                <strong>Customer Number, Name, Mobile No, Aadhar Number, Ration Card No, Address, Bank Details</strong>
            </p>
            <form action="import_customers.php" method="post" enctype="multipart/form-data">
                <div class="input-group"><label>Select CSV File</label><input type="file" name="customer_csv" accept=".csv" required></div>
                <button type="submit" name="import" class="btn">Import Customers</button>
            </form>
        </div>
    </div>
</body>
</html>