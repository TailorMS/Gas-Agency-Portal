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

// Prepare a select statement to get all member details
$sql = "SELECT username, email, contact_number, dob, profile_photo, qualification, experience, address FROM members WHERE id = ?";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $member_id);
    if($stmt->execute()){
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($username, $email, $contact_number, $dob, $profile_photo, $qualification, $experience, $address);
            $stmt->fetch();
        } else {
            // No member found with that ID, redirect
            header("location: view_members.php");
            exit;
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
    <title>Member Details</title>
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
            <h1 class="page-header">Member Details</h1>
            <?php if(!empty($profile_photo) && file_exists($profile_photo)): ?>
                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 20px;">
            <?php endif; ?>
            <div class="detail-item"><strong>Member ID:</strong> <?php echo htmlspecialchars($member_id); ?></div>
            <div class="detail-item"><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></div>
            <div class="detail-item"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></div>
            <div class="detail-item"><strong>Contact Number:</strong> <?php echo htmlspecialchars($contact_number); ?></div>
            <div class="detail-item"><strong>Date of Birth:</strong> <?php echo !empty($dob) ? date("F j, Y", strtotime($dob)) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Address:</strong> <?php echo !empty($address) ? nl2br(htmlspecialchars($address)) : 'N/A'; ?></div>
            <div class="detail-item"><strong>Qualification:</strong> <?php echo htmlspecialchars($qualification); ?></div>
            <div class="detail-item"><strong>Experience:</strong> <?php echo !empty($experience) ? nl2br(htmlspecialchars($experience)) : 'N/A'; ?></div>
            <div style="margin-top: 20px;">
                <a href="edit_member.php?id=<?php echo htmlspecialchars($member_id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px;" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="download_member.php?id=<?php echo htmlspecialchars($member_id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #218838;" title="Download"><i class="fas fa-download"></i></a>
                <a href="delete_member.php?id=<?php echo htmlspecialchars($member_id); ?>" class="btn" style="display: inline-block; width: auto; margin-right: 10px; background-color: #dc3545;" onclick="return confirm('Are you sure you want to delete this member? This action cannot be undone.');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                <a href="view_members.php" class="btn" style="display: inline-block; width: auto; background-color: #6c757d;" title="Back to Members"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>

</body>
</html>