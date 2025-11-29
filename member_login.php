<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to dashboard page
if(isset($_SESSION["member_loggedin"]) && $_SESSION["member_loggedin"] === true){
    header("location: member_dashboard.php");
    exit;
}

// Include db connect file
require_once "db_connect.php";

// Define variables and initialize with empty values
$username = $password = "";
$login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["username"]))){
        $login_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }

    if(empty(trim($_POST["password"]))){
        $login_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    if(empty($login_err)){
        $sql = "SELECT id, username, password FROM members WHERE username = ?";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if($stmt->execute()){
                $stmt->store_result();

                if($stmt->num_rows == 1){
                    $stmt->bind_result($id, $username, $hashed_password);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            // session_start() is already at the top of the file.

                            // Log the login action
                            $log_action = "Logged in";
                            $log_sql = "INSERT INTO member_history (member_id, member_username, action) VALUES (?, ?, ?)";
                            if($log_stmt = $mysqli->prepare($log_sql)){
                                $log_stmt->bind_param("iss", $id, $username, $log_action);
                                $log_stmt->execute();
                                $log_stmt->close();
                            }

                            $_SESSION["member_loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["member_username"] = $username;

                            header("location: member_dashboard.php");
                        } else{
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid username or password.";
                }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AmodIndane - Member Login</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <img src="indane_logo.jpg" alt="Amod Indane Logo" style="max-width: 150px; margin-bottom: 20px;">
        <h2>Amod Indane Member Login</h2>
        
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
            <p style="margin-top: 15px;"><a href="forgot_password.php">Forgot Password?</a></p>
        </form>
    </div>
</body>
</html>