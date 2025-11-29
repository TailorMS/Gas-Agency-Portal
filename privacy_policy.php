<?php
session_start();

$dashboard_link = "Admin.php"; // Default fallback
$is_admin = isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true;
$is_member = isset($_SESSION["member_loggedin"]) && $_SESSION["member_loggedin"] === true;

if ($is_admin) {
    $dashboard_link = "dashboard.php";
} elseif ($is_member) {
    $dashboard_link = "member_dashboard.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy - AmodIndane</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body { background-image: url('background_bg.webp'); background-size: cover; background-position: center; background-attachment: fixed; }
        .policy-content h2 { margin-top: 20px; font-size: 1.5em; color: #333; }
        .policy-content p, .policy-content ul { margin-bottom: 15px; line-height: 1.6; }
        .policy-content ul { list-style-type: disc; padding-left: 20px; }
    </style>
</head>
<body>
    <div class="menubar">
        <div class="logo"><a href="<?php echo $dashboard_link; ?>">AmodIndane</a></div>
    </div>

    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Privacy Policy</h1>
            <div class="policy-content">
                <h2>Information Collection and Use</h2>
                <p>We collect several different types of information for various purposes to provide and improve our Service to you. This may include personal data such as email address, name, phone number, and address.</p>

                <h2>Log Data</h2>
                <p>We may also collect information that your browser sends whenever you visit our Service or when you access the Service by or through a mobile device ("Log Data"). This Log Data may include information such as your computer's Internet Protocol ("IP") address, browser type, browser version, the pages of our Service that you visit, the time and date of your visit, the time spent on those pages, and other statistics.</p>

                <h2>Use of Data</h2>
                <p>AmodIndane uses the collected data for various purposes:</p>
                <ul>
                    <li>To provide and maintain the Service</li>
                    <li>To notify you about changes to our Service</li>
                    <li>To allow you to participate in interactive features of our Service when you choose to do so</li>
                    <li>To provide customer care and support</li>
                    <li>To provide analysis or valuable information so that we can improve the Service</li>
                    <li>To monitor the usage of the Service</li>
                    <li>To detect, prevent and address technical issues</li>
                </ul>
            </div>
            <div class="action-bar" style="margin-top: 20px;"><a href="<?php echo $dashboard_link; ?>" class="btn" style="background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
        </div>
    </div>
</body>
</html>