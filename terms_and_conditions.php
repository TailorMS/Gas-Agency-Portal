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
    <title>Terms & Conditions - AmodIndane</title>
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
            <h1 class="page-header">Terms & Conditions</h1>
            <div class="policy-content">
                <h2>1. Introduction</h2>
                <p>Welcome to AmodIndane. These terms and conditions outline the rules and regulations for the use of our application.</p>

                <h2>2. Intellectual Property Rights</h2>
                <p>Other than the content you own, under these Terms, AmodIndane and/or its licensors own all the intellectual property rights and materials contained in this application.</p>

                <h2>3. Restrictions</h2>
                <p>You are specifically restricted from all of the following:</p>
                <ul>
                    <li>Publishing any application material in any other media.</li>
                    <li>Selling, sublicensing and/or otherwise commercializing any application material.</li>
                    <li>Using this application in any way that is or may be damaging to this application.</li>
                    <li>Using this application contrary to applicable laws and regulations, or in any way may cause harm to the application, or to any person or business entity.</li>
                </ul>

                <h2>4. Your Content</h2>
                <p>In these terms and conditions, "Your Content" shall mean any audio, video text, images or other material you choose to display on this application. By displaying Your Content, you grant AmodIndane a non-exclusive, worldwide irrevocable, sub-licensable license to use, reproduce, adapt, publish, translate and distribute it in any and all media.</p>

                <h2>5. No warranties</h2>
                <p>This application is provided "as is," with all faults, and AmodIndane expresses no representations or warranties, of any kind related to this application or the materials contained on this application.</p>
            </div>
            <div class="action-bar" style="margin-top: 20px;"><a href="<?php echo $dashboard_link; ?>" class="btn" style="background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
        </div>
    </div>
</body>
</html>