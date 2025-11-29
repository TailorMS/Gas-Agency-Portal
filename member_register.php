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
$username = $email = $password = $confirm_password = $contact_number = $dob = $qualification = $experience = $address = "";
$username_err = $email_err = $password_err = $confirm_password_err = $photo_err = "";
$profile_photo_path = "";


// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM members WHERE username = ?";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);

            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Invalid email format.";
    } else {
        $sql = "SELECT id FROM members WHERE email = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $email_err = "This email is already taken.";
                } else{
                    $email = trim($_POST["email"]);
                }
            }
            $stmt->close();
        }
    }

    // Handle file upload
    if(isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0){
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["profile_photo"]["name"];
        $filetype = $_FILES["profile_photo"]["type"];
        $filesize = $_FILES["profile_photo"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) $photo_err = "Please select a valid file format.";

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) $photo_err = "File size is larger than the allowed limit.";

        // Verify MIME type of the file
        if(in_array($filetype, $allowed)){
            // Check whether file exists before uploading it
            $new_filename = uniqid() . "." . $ext;
            $target_path = "uploads/" . $new_filename;
            if(move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_path)){
                $profile_photo_path = $target_path;
            } else{
                $photo_err = "There was an error uploading your file.";
            }
        } else{
            $photo_err = "There was a problem with your file upload.";
        }
    }

    $contact_number = trim($_POST['contact_number']);
    $dob = trim($_POST['dob']);
    $qualification = trim($_POST['qualification']);
    $experience = trim($_POST['experience']);
    $address = trim($_POST['address']);

    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if(empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($photo_err)){

        $sql = "INSERT INTO members (username, email, password, contact_number, dob, profile_photo, qualification, experience, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sssssssss", $param_username, $param_email, $param_password, $param_contact, $param_dob, $param_photo, $param_qual, $param_exp, $param_addr);

            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_contact = $contact_number;
            $param_dob = $dob;
            $param_photo = $profile_photo_path;
            $param_qual = $qualification;
            $param_exp = $experience;
            $param_addr = $address;

            if($stmt->execute()){
                // Show success alert and refresh the page
                echo "<script>alert('New member added successfully.'); window.location.href='member_register.php';</script>";
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
    <title>AmodIndane - Member Registration</title>
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
        
        <div class="profile-card">
            <h1 class="page-header">Register New Member</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
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
                <div class="input-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>">
                </div>
                <div class="input-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
                </div>
                <div class="input-group">
                    <label>Address</label>
                    <textarea name="address" rows="3" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
                <div class="input-group">
                    <label>Qualification</label>
                    <input type="text" name="qualification" value="<?php echo htmlspecialchars($qualification); ?>">
                </div>
                <div class="input-group">
                    <label>Experience</label>
                    <textarea name="experience" rows="3" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;"><?php echo htmlspecialchars($experience); ?></textarea>
                </div>
                <div class="input-group">
                    <label>Profile Photo</label>
                    <input type="file" name="profile_photo">
                    <span style="color: #fa383e;"><?php echo $photo_err; ?></span>
                </div>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #dddfe2;">
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password">
                    <span style="color: #fa383e;"><?php echo $password_err; ?></span>
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password">
                    <span style="color: #fa383e;"><?php echo $confirm_password_err; ?></span>
                </div>
                <button type="submit" class="btn">Register Member</button>
            </form>
            <a href="dashboard.php" class="btn" style="display: block; width: fit-content; margin: 20px auto 0; background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
        </div>
    </div>
</body>
</html>