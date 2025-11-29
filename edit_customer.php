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

// Check if customer ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_customers.php");
    exit;
}
$customer_id = trim($_GET["id"]);

// Define variables and initialize with empty values
$customer_no = $name = $mobile_no = $aadhar_no = $ration_card_no = $address = $bank_details = "";
$customer_no_err = $name_err = $mobile_no_err = "";
$update_success = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate Customer Number
    if(empty(trim($_POST["customer_no"]))){
        $customer_no_err = "Please enter a customer number.";
    } else {
        $sql = "SELECT id FROM customers WHERE customer_no = ? AND id != ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("si", $_POST["customer_no"], $customer_id);
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
        $sql = "SELECT id FROM customers WHERE mobile_no = ? AND id != ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("si", $_POST["mobile_no"], $customer_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0) $mobile_no_err = "This mobile number is already taken.";
            else $mobile_no = trim($_POST["mobile_no"]);
        }
    }

    // If no errors, update the database
    if(empty($name_err) && empty($mobile_no_err) && empty($customer_no_err)){
        $sql = "UPDATE customers SET customer_no = ?, name = ?, mobile_no = ?, aadhar_no = ?, ration_card_no = ?, address = ?, bank_details = ? WHERE id = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sssssssi", 
                $_POST["customer_no"], $_POST["name"], $_POST["mobile_no"], 
                $_POST["aadhar_no"], $_POST["ration_card_no"], $_POST["address"], 
                $_POST["bank_details"], $customer_id
            );
            if($stmt->execute()){
                echo "<script>alert('Customer details updated successfully!'); window.location.href='customer_details.php?id=" . $customer_id . "';</script>";
                exit;
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

// Fetch current customer data to display in the form
$sql = "SELECT customer_no, name, mobile_no, aadhar_no, ration_card_no, address, bank_details FROM customers WHERE id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $customer_id);
    if($stmt->execute()){
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($customer_no, $name, $mobile_no, $aadhar_no, $ration_card_no, $address, $bank_details);
            $stmt->fetch();
        } else {
            header("location: view_customers.php");
            exit;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer</title>
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
            <a href="admin_profile.php">Profile</a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        

        <?php 
        if(!empty($update_success)){
            echo '<div class="error-message" style="background-color: #e7f3ff; border-color: #1877f2; color: #1c1e21; max-width: 600px; margin: 0 auto 20px auto;">' . $update_success . '</div>';
        }        
        ?>

        <div class="profile-card">
            <h1 class="page-header">Edit Customer: <?php echo htmlspecialchars($name); ?></h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $customer_id; ?>" method="post">
                <div class="input-group"><label>Customer Number</label><input type="text" name="customer_no" value="<?php echo htmlspecialchars($customer_no); ?>"><span style="color: #fa383e;"><?php echo $customer_no_err; ?></span></div>
                <div class="input-group"><label>Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>"><span style="color: #fa383e;"><?php echo $name_err; ?></span></div>
                <div class="input-group"><label>Mobile No</label><input type="text" name="mobile_no" value="<?php echo htmlspecialchars($mobile_no); ?>"><span style="color: #fa383e;"><?php echo $mobile_no_err; ?></span></div>
                <div class="input-group"><label>Aadhar Number</label><input type="text" name="aadhar_no" value="<?php echo htmlspecialchars($aadhar_no); ?>"></div>
                <div class="input-group"><label>Ration Card No</label><input type="text" name="ration_card_no" value="<?php echo htmlspecialchars($ration_card_no); ?>"></div>
                <div class="input-group"><label>Address</label><textarea name="address" rows="3" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($address); ?></textarea></div>
                <div class="input-group"><label>Bank Details</label><textarea name="bank_details" rows="3" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($bank_details); ?></textarea></div>
                <button type="submit" class="btn">Update Customer</button>
            </form>
            <p style="text-align: center; margin-top: 20px;"><a href="customer_details.php?id=<?php echo $customer_id; ?>">Back to Details</a></p>
        </div>
    </div>

</body>
</html>