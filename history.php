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

// Fetch all history records
$history_logs = [];
$sql = "SELECT id, member_id, member_username, action, target_type, target_id, action_timestamp FROM member_history ORDER BY action_timestamp DESC";

if ($result = $mysqli->query($sql)) {
    $history_logs = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Activity History</title>
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
            <a href="dashboard.php">Dashboard</a>
            <a href="history.php" class="active" title="History"><i class="fas fa-history"></i></a>
            <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <a href="dashboard.php" class="btn" style="display: inline-block; width: auto; margin-bottom: 20px; background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
        
        
        <div class="table-container">
            <h1 class="page-header">Member Activity History</h1>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>Member</th>
                        <th>Action</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history_logs)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No member activity recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; ?>
                        <?php foreach($history_logs as $log): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><a href="member_details.php?id=<?php echo $log['member_id']; ?>"><?php echo htmlspecialchars($log['member_username']); ?></a></td>
                            <td>
                                <?php 
                                echo htmlspecialchars($log['action']);
                                if (!empty($log['target_type']) && !empty($log['target_id'])) {
                                    $link = '';
                                    switch ($log['target_type']) {
                                        case 'Cash Deposit':
                                            $link = 'money_details.php?id=' . urlencode($log['target_id']) . '&source=history';
                                            break;
                                        default:
                                            $link = 'history_detail.php?target_type=' . urlencode($log['target_type']) . '&target_id=' . urlencode($log['target_id']);
                                    }
                                    echo ' (<a href="' . $link . '">View Item Details</a>)';
                                }
                                ?>
                            </td>
                            <td><?php echo date("F j, Y, g:i a", strtotime($log['action_timestamp'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>