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

// Define variables and initialize with empty values
$username = $email = $contact_number = $dob = $profile_photo = $qualification = $experience = $address = "";
$new_password = $confirm_password = "";
$password_err = $photo_err = $update_success = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // --- Update Profile Photo ---
    if(isset($_POST['update_photo']) && isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0){
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png", "webp" => "image/webp"];
        $filename = $_FILES["profile_photo"]["name"];
        $filetype = $_FILES["profile_photo"]["type"];
        $filesize = $_FILES["profile_photo"]["size"];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) $photo_err = "Please select a valid file format.";

        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) $photo_err = "File size is larger than the allowed limit (5MB).";

        if(empty($photo_err) && in_array($filetype, $allowed)){
            $new_filename = "uploads/" . uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $new_filename)){
                // Get old photo path to delete it
                $sql_old_photo = "SELECT profile_photo FROM members WHERE id = ?";
                if($stmt_old = $mysqli->prepare($sql_old_photo)){
                    $stmt_old->bind_param("i", $_SESSION["id"]);
                    $stmt_old->execute();
                    $stmt_old->bind_result($old_photo_path);
                    if($stmt_old->fetch() && !empty($old_photo_path) && file_exists($old_photo_path)) {
                        unlink($old_photo_path);
                    }
                    $stmt_old->close();
                }

                // Update database with new photo path
                $sql_update = "UPDATE members SET profile_photo = ? WHERE id = ?";
                if($stmt_update = $mysqli->prepare($sql_update)){
                    $stmt_update->bind_param("si", $new_filename, $_SESSION["id"]);if($stmt_update->execute()){
                        echo "<script>alert('Profile photo updated successfully!'); window.location.href='member_profile.php';</script>";
                        exit;
                    }
                    $stmt_update->close();
                }
            } else { $photo_err = "There was an error uploading your file."; }
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
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE members SET password = ? WHERE id = ?";

            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("si", $new_password_hash, $_SESSION["id"]);
                if($stmt->execute()){
                    echo "<script>alert('Password updated successfully!'); window.location.href='member_profile.php';</script>";
                    exit;
                    // Log this action
                    $log_action = "Updated their password";
                    $mysqli->query("INSERT INTO member_history (member_id, member_username, action) VALUES ('".$_SESSION["id"]."', '".$_SESSION["member_username"]."', '".$log_action."')");
                } else {
                    $password_err = "Oops! Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    }
}

// Prepare a select statement to get member details
$sql = "SELECT username, email, contact_number, dob, profile_photo, qualification, experience, address FROM members WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $_SESSION["id"]);
    if($stmt->execute()){
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($username, $email, $contact_number, $dob, $profile_photo, $qualification, $experience, $address);
            $stmt->fetch();
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
    <title>Member Profile</title>
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
        <div class="logo"><a href="member_dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav">
            <a href="member_dashboard.php">Dashboard</a>
            <a href="member_profile.php" class="active" title="Profile"><i class="fas fa-user"></i></a>
            <a href="member_logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <a href="member_dashboard.php" class="btn" style="display: inline-block; width: auto; margin-bottom: 20px; background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
        
        <div class="profile-card">
            <h1 class="page-header">Your Profile</h1>
            <?php if(!empty($profile_photo) && file_exists($profile_photo)): ?>
            <img 
                src="<?= htmlspecialchars($profile_photo); ?>"
                alt="Profile Photo" 
                style="display: block; margin: 0 auto 20px; width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" style="text-align: center; margin-bottom: 30px;">
                <div class="form-section" style="margin-top: 0;">
                    <h3>Change Profile Photo</h3>
                    <div class="input-group">
                        <label for="profile_photo_upload" style="display: none;">Profile Photo</label>
                        <input type="file" name="profile_photo" id="profile_photo_upload" required>
                        <span style="color: #fa383e;"><?php echo $photo_err; ?></span>
                    </div>
                    <button type="submit" name="update_photo" class="btn">Upload Photo</button>
                </div>
            </form>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #dddfe2;">

            <div class="detail-item"><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></div>
            <div class="detail-item"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></div>
            <div class="detail-item"><strong>Contact Number:</strong> <?php echo htmlspecialchars($contact_number); ?></div>
            <div class="detail-item"><strong>Date of Birth:</strong> <?php echo !empty($dob) ? date("F j, Y", strtotime($dob)) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Address:</strong> <?php echo !empty($address) ? nl2br(htmlspecialchars($address)) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Qualification:</strong> <?php echo htmlspecialchars($qualification); ?></div>
            <div class="detail-item"><strong>Experience:</strong> <?php echo !empty($experience) ? nl2br(htmlspecialchars($experience)) : 'N/A'; ?></div>
            <?php 
            if(!empty($update_success)){
                echo '<div class="error-message" style="background-color: #e7f3ff; border-color: #1877f2; color: #1c1e21; text-align: center; padding: 10px; margin: 10px 0; border-radius: 6px;">' . $update_success . '</div>';
            }        
            ?>
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