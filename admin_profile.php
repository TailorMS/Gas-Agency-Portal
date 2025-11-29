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
$username = $email = "";
$username_err = $email_err = $password_err = "";
$update_success = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $admin_id = $_SESSION["admin_id"];

    // --- Update Profile Details ---
    if(isset($_POST['update_profile'])){
        // Validate and update username
        $new_username = trim($_POST["username"]);
        if(empty($new_username)){
            $username_err = "Please enter a username.";
        } else {
            $sql = "SELECT id FROM admins WHERE username = ? AND id != ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("si", $new_username, $admin_id);
                if($stmt->execute()){
                    $stmt->store_result();
                    if($stmt->num_rows > 0){
                        $username_err = "This username is already taken.";
                    }
                }
                $stmt->close();
            }
        }

        // Validate and update email
        $new_email = trim($_POST["email"]);
        if(empty($new_email)){
            $email_err = "Please enter an email.";
        } elseif(!filter_var($new_email, FILTER_VALIDATE_EMAIL)){
            $email_err = "Invalid email format.";
        } else {
            $sql = "SELECT id FROM admins WHERE email = ? AND id != ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("si", $new_email, $admin_id);
                if($stmt->execute()){
                    $stmt->store_result();
                    if($stmt->num_rows > 0){
                        $email_err = "This email is already taken.";
                    }
                }
                $stmt->close();
            }
        }

        // If no errors, update the database
        if(empty($username_err) && empty($email_err)){
            $sql = "UPDATE admins SET username = ?, email = ? WHERE id = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("ssi", $new_username, $new_email, $admin_id);
                if($stmt->execute()){
                    $_SESSION["admin_username"] = $new_username; // Update session username
                    echo "<script>alert('Profile updated successfully!'); window.location.href='admin_profile.php';</script>";
                    exit;
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    }

    // --- Update Password ---
    if(isset($_POST['update_password'])){
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if(empty($new_password)){
            $password_err = "Please enter a new password.";
        } elseif(strlen($new_password) < 6){
            $password_err = "Password must have at least 6 characters.";
        } elseif($new_password != $confirm_password){
            $password_err = "Passwords do not match.";
        } else {
            // Since admin password is not hashed, we just update it directly.
            // For a secure system, you would hash this password.
            $sql = "UPDATE admins SET password = ? WHERE id = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("si", $new_password, $admin_id);
                if($stmt->execute()){
                    echo "<script>alert('Password updated successfully!'); window.location.href='admin_profile.php';</script>";
                    exit;
                } else {
                    $password_err = "Oops! Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    }
}

// Fetch current admin data to display in the form
$sql = "SELECT username, email FROM admins WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $_SESSION["admin_id"]);
    if($stmt->execute()){
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($username, $email);
            $stmt->fetch();
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
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
            <a href="dashboard.php">Dashboard</a>
            <a href="history.php" title="History"><i class="fas fa-history"></i></a>
            <a href="admin_profile.php" class="active" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <a href="dashboard.php" class="btn" style="display: inline-block; width: auto; margin-bottom: 20px; background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>

        <?php 
        if(!empty($update_success)){
            echo '<div class="error-message" style="background-color: #e7f3ff; border-color: #1877f2; color: #1c1e21;">' . $update_success . '</div>';
        }        
        ?>

        <div class="profile-card">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h1 class="page-header">Admin Profile</h1>
                <div class="form-section">
                    <h3>Profile Details</h3>
                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        <span style="color: #fa383e;"><?php echo $username_err; ?></span>
                    </div>
                    <div class="input-group">
                        <label>Email</label>
                        <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <span style="color: #fa383e;"><?php echo $email_err; ?></span>
                    </div>
                    <button type="submit" name="update_profile" class="btn">Update Profile</button>
                </div>
            </form>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-section">
                    <h3>Change Password</h3>
                    <div class="input-group">
                        <label>New Password</label>
                        <input type="password" name="new_password">
                    </div>
                    <div class="input-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password">
                        <span style="color: #fa383e;"><?php echo $password_err; ?></span>
                    </div>
                    <button type="submit" name="update_password" class="btn">Update Password</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>