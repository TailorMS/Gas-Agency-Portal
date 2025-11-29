<?php
require_once "db_connect.php";

$token = $_GET["token"] ?? '';
$password_err = $confirm_password_err = "";
$message = "";
$show_form = false;

if (empty($token)) {
    header("location: Admin.php");
    exit;
}

$token_hash = hash("sha256", $token);

$sql = "SELECT id FROM admins WHERE reset_token = ? AND reset_token_expires_at > NOW()";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $show_form = true;
        $stmt->bind_result($user_id);
        $stmt->fetch();
    } else {
        $message = "Link has expired or is invalid. Please try again.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $show_form) {
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }

    if (empty($password_err) && empty($confirm_password_err)) {
        // NOTE: As per system design, admin password is not hashed.
        // For a production system, you should hash this password.
        $new_password = $password;
        $update_sql = "UPDATE admins SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?";
        
        if ($update_stmt = $mysqli->prepare($update_sql)) {
            $update_stmt->bind_param("si", $new_password, $user_id);
            if ($update_stmt->execute()) {
                $message = "Your password has been reset successfully. You can now <a href='Admin.php'>login</a>.";
                $show_form = false;
            } else {
                $message = "Oops! Something went wrong. Please try again later.";
            }
            $update_stmt->close();
        }
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Reset Password</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <h2>Reset Admin Password</h2>

        <?php if (!empty($message)): ?>
            <div class="error-message" style="background-color: #e7f3ff; border-color: #1877f2; color: #1c1e21;"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($show_form): ?>
        <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="password">
                <span style="color: #fa383e;"><?php echo $password_err; ?></span>
            </div>
            <div class="input-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password">
                <span style="color: #fa383e;"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="input-group">
                <button type="submit" class="btn">Reset Password</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>