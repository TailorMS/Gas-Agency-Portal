<?php
require_once "db_connect.php";

$email = "";
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $sql = "SELECT id FROM admins WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    // Generate a secure token
                    $token = bin2hex(random_bytes(50));
                    $token_hash = hash("sha256", $token);

                    // Set token expiry to 1 hour from now
                    $expires = date("Y-m-d H:i:s", time() + 3600);

                    $update_sql = "UPDATE admins SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?";
                    if ($update_stmt = $mysqli->prepare($update_sql)) {
                        $update_stmt->bind_param("sss", $token_hash, $expires, $email);
                        $update_stmt->execute();

                        // --- Email Sending Placeholder ---
                        // For local development, we will display the link directly.
                        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/admin_reset_password.php?token=" . $token;
                        $message = "A password reset link has been generated. For local testing, please use this link: <br><a href='{$reset_link}'>{$reset_link}</a>";
                    }
                } else {
                     // Show a generic message to prevent email enumeration
                     $message = "If an account with that email exists, a reset link has been sent.";
                }
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
    <title>Admin - Forgot Password</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <h2>Forgot Admin Password</h2>
        <p>Enter your email address to receive a password reset link.</p>

        <?php 
        if(!empty($message)){
            echo '<div class="error-message" style="background-color: #e7f3ff; border-color: #1877f2; color: #1c1e21;">' . $message . '</div>';
        }
        if(!empty($error)){
            echo '<div class="error-message">' . $error . '</div>';
        }
        ?>

        <?php if(empty($message)): // Hide form after link is generated ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="input-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        <?php endif; ?>
        <p style="margin-top: 15px;"><a href="Admin.php">Back to Login</a></p>
    </div>
</body>
</html>