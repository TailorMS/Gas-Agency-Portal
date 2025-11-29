<?php
// 
// Amod Indane - Admin Login 
// 

// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to dashboard page
if(isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

// Include db connect file
require_once "db_connect.php";

// Define variables and initialize with empty values
$username = $password = "";
$login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $login_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $login_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if(empty($login_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, email, password FROM admins WHERE username = ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();

                // Check if username exists, if yes then verify password
                if($stmt->num_rows == 1){
                    // Bind result variables
                    $stmt->bind_result($id, $db_username, $email, $db_password);
                    if($stmt->fetch()){
                        // Note: $password is the plain-text password from the form
                        if($password === $db_password){
                            // Password is correct, so start a new session
                            // session_start() is already called at the top of the file

                            // Store data in session variables
                            $_SESSION["admin_loggedin"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $db_username;
                            // Redirect user to dashboard page
                            header("location: dashboard.php");
                            exit; // It's good practice to exit after a header redirect
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AmodIndane - Admin Login</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <img src="indane_logo.jpg" alt="Amod Indane Logo" style="max-width: 150px; margin-bottom: 20px;">
        <h2>Amod Indane Admin Login</h2>
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="error-message">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
            <p style="margin-top: 15px;"><a href="admin_forgot_password.php">Forgot Password?</a></p>
        </form>
    </div>
</body>
</html>
