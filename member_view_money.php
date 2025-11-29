<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){
    header("location: member_login.php");
    exit;
}

// Include db connect file
require_once "db_connect.php";

// Get search date
$search_date = $_GET['search_date'] ?? '';

// Fetch all cash deposit records
$deposits = [];
$sql = "SELECT id, deposit_date, count_500, count_200, count_100, count_50, count_10, total_amount FROM cash_deposits";

$where_clauses = [];
$bind_types = "";
$bind_params = [];

if (!empty($search_date)) {
    $where_clauses[] = "deposit_date = ?";
    $bind_types .= "s";
    $bind_params[] = $search_date;
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY deposit_date DESC, id DESC";

if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($bind_params)) { $stmt->bind_param($bind_types, ...$bind_params); }
    $stmt->execute();
    $result = $stmt->get_result();
    $deposits = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Money Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body {
            background-image: url('background_bg.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .data-table td, .data-table th {
            text-align: right;
        }
        .data-table th:first-child, .data-table td:first-child {
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="menubar">
        <div class="logo"><a href="member_dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav">
            <a href="member_dashboard.php" class="active">Dashboard</a>
            <a href="member_profile.php" title="Profile"><i class="fas fa-user"></i></a>
            <a href="member_logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Cash Deposit Details</h1>
            <div class="action-bar" style="justify-content: space-between; margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <form action="member_view_money.php" method="get" style="display: flex; gap: 10px; align-items: center; margin-bottom: 0;">
                        <div class="input-group" style="margin-bottom: 0;">
                            <input type="date" name="search_date" value="<?php echo htmlspecialchars($search_date); ?>">
                        </div>
                        <button type="submit" class="btn" title="Search"><i class="fas fa-search"></i></button>
                    </form>
                    <a href="download_member_money_deposits.php?search_date=<?php echo urlencode($search_date); ?>" class="btn" style="background-color: #218838; width: auto;" title="Download All"><i class="fas fa-download"></i></a>
                </div>
                <a href="member_dashboard.php" class="btn" style="background-color: #6c757d; width: auto;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>₹500</th>
                            <th>₹200</th>
                            <th>₹100</th>
                            <th>₹50</th>
                            <th>₹10</th>
                            <th>Total Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($deposits)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No cash deposits found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($deposits as $deposit): ?>
                            <tr>
                                <td><?php echo date("M j, Y", strtotime($deposit['deposit_date'])); ?></td>
                                <td><?php echo htmlspecialchars($deposit['count_500']); ?></td>
                                <td><?php echo htmlspecialchars($deposit['count_200']); ?></td>
                                <td><?php echo htmlspecialchars($deposit['count_100']); ?></td>
                                <td><?php echo htmlspecialchars($deposit['count_50']); ?></td>
                                <td><?php echo htmlspecialchars($deposit['count_10']); ?></td>
                                <td>
                                    <strong>
                                        <a href="member_money_details.php?id=<?php echo $deposit['id']; ?>" title="View Details"><?php echo htmlspecialchars(number_format($deposit['total_amount'], 2)); ?></a>
                                    </strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>