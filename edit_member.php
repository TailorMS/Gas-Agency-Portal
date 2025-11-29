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

// Check if member ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: view_members.php");
    exit;
}
$member_id = trim($_GET["id"]);

// Define variables and initialize with empty values
$username = $email = $contact_number = $dob = $qualification = $experience = $address = $profile_photo = "";
$username_err = $email_err = $password_err = $photo_err = "";
$update_success = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // --- Update Profile Details ---
    if(isset($_POST['update_profile'])){
        // Validate and update username
        $new_username = trim($_POST["username"]);
        if(empty($new_username)){
            $username_err = "Please enter a username.";
        } else {
            $sql = "SELECT id FROM members WHERE username = ? AND id != ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("si", $new_username, $member_id);
                if($stmt->execute()){
                    $stmt->store_result();
                    if($stmt->num_rows > 0) $username_err = "This username is already taken.";
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
            $sql = "SELECT id FROM members WHERE email = ? AND id != ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("si", $new_email, $member_id);
                if($stmt->execute()){
                    $stmt->store_result();
                    if($stmt->num_rows > 0) $email_err = "This email is already taken.";
                }
                $stmt->close();
            }
        }

        // If no errors, update the database
        if(empty($username_err) && empty($email_err)){
            $sql = "UPDATE members SET username = ?, email = ?, contact_number = ?, dob = ?, qualification = ?, experience = ?, address = ? WHERE id = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("sssssssi", 
                    $_POST["username"], $_POST["email"], $_POST["contact_number"], 
                    $_POST["dob"], $_POST["qualification"], $_POST["experience"], 
                    $_POST["address"], $member_id
                );
                if($stmt->execute()){
                    echo "<script>alert('Member details updated successfully!'); window.location.href='member_details.php?id=" . $member_id . "';</script>";
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
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE members SET password = ? WHERE id = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("si", $new_password_hash, $member_id);
                if($stmt->execute()){
                    echo "<script>alert('Password updated successfully!'); window.location.href='member_details.php?id=" . $member_id . "';</script>";
                    exit;
                } else {
                    $password_err = "Oops! Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    }

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
                    $stmt_old->bind_param("i", $member_id);
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
                    $stmt_update->bind_param("si", $new_filename, $member_id);if($stmt_update->execute()){
                        echo "<script>alert('Profile photo updated successfully!'); window.location.href='member_details.php?id=" . $member_id . "';</script>";
                        exit;
                    }
                    $stmt_update->close();
                }
            } else { $photo_err = "There was an error uploading your file."; }
        }
    }
}

// Fetch current member data to display in the form
$sql = "SELECT username, email, contact_number, dob, qualification, experience, address, profile_photo FROM members WHERE id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $member_id);
    if($stmt->execute()){
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($username, $email, $contact_number, $dob, $qualification, $experience, $address, $profile_photo);
            $stmt->fetch();
        } else {
            header("location: view_members.php");
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
    <title>Edit Member</title>
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
        if(!empty($update_success)){
            echo '<div class="error-message" style="background-color: #e7f3ff; border-color: #1877f2; color: #1c1e21; max-width: 600px; margin: 0 auto 20px auto;">' . $update_success . '</div>';
        }        
        ?>

        <div class="profile-card">
            <h1 class="page-header">Edit Member: <?php echo htmlspecialchars($username); ?></h1>
            <?php if(!empty($profile_photo) && file_exists($profile_photo)): ?>
            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" style="display: block; margin: 0 auto 20px; width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $member_id; ?>" method="post" enctype="multipart/form-data">
                <div class="form-section">
                    <h3>Change Profile Photo</h3>
                    <div class="input-group">
                        <input type="file" name="profile_photo" required>
                        <span style="color: #fa383e;"><?php echo $photo_err; ?></span>
                    </div>
                    <button type="submit" name="update_photo" class="btn">Upload Photo</button>
                </div>
            </form>


            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $member_id; ?>" method="post">
                <div class="form-section">
                    <h3>Profile Details</h3>
                    <div class="input-group"><label>Username</label><input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>"><span style="color: #fa383e;"><?php echo $username_err; ?></span></div>
                    <div class="input-group"><label>Email</label><input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"><span style="color: #fa383e;"><?php echo $email_err; ?></span></div>
                    <div class="input-group"><label>Contact Number</label><input type="text" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>"></div>
                    <div class="input-group"><label>Date of Birth</label><input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>"></div>
                    <div class="input-group"><label>Address</label><textarea name="address" rows="3" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($address); ?></textarea></div>
                    <div class="input-group"><label>Qualification</label><input type="text" name="qualification" value="<?php echo htmlspecialchars($qualification); ?>"></div>
                    <div class="input-group"><label>Experience</label><textarea name="experience" rows="3" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($experience); ?></textarea></div>
                    <button type="submit" name="update_profile" class="btn">Update Details</button>
                </div>
            </form>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $member_id; ?>" method="post">
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
            <a href="member_details.php?id=<?php echo $member_id; ?>" style="display: inline-block; margin-top: 20px;">Back to Details</a>
        </div>
    </div>

</body>
</html>