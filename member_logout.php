<?php
// Initialize the session
session_start();

// Log the logout action before destroying the session
if(isset($_SESSION["member_loggedin"]) && $_SESSION["member_loggedin"] === true){
    require_once "db_connect.php";
    $log_action = "Logged out";
    $log_sql = "INSERT INTO member_history (member_id, member_username, action) VALUES (?, ?, ?)";
    if($log_stmt = $mysqli->prepare($log_sql)){
        $log_stmt->bind_param("iss", $_SESSION["id"], $_SESSION["member_username"], $log_action);
        $log_stmt->execute();
        $log_stmt->close();
    }
    $mysqli->close();
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: member_login.php");
exit;
?>