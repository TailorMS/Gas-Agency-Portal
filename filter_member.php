<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){
    header("location: member_login.php");
    exit;
}

// Get current filter values from GET to pre-fill the form
$search_term = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'date';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';

// Build the query string for the back button
$query_params = http_build_query(array_filter([
    'search' => $search_term,
    'sort' => $sort_by,
    'from_date' => $from_date,
    'to_date' => $to_date,
    'filter_type' => $filter_type
]));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Filter Transactions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body {
            background-image: url('background_bg.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
    </style>
</head>
<body>

    <div class="menubar">
        <div class="logo"><a href="member_dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav">
            <a href="member_dashboard.php">Dashboard</a>
            <a href="member_profile.php" title="Profile"><i class="fas fa-user"></i></a>
            <a href="member_logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Filter & Sort Transactions</h1>
            <form action="member_view_transaction.php" method="get" class="profile-card" style="max-width: 100%;">
                <div class="filter-grid">
                    <div class="input-group">
                        <label>Sort By</label>
                        <select name="sort" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;">
                            <option value="date" <?php if ($sort_by == 'date' || $sort_by == '') echo 'selected'; ?>>None</option>
                            <option value="money_in" <?php if ($sort_by == 'money_in') echo 'selected'; ?>>Sort by Money In</option>
                            <option value="money_out" <?php if ($sort_by == 'money_out') echo 'selected'; ?>>Sort by Money Out</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>From Date</label>
                        <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
                    </div>
                    <div class="input-group">
                        <label>To Date</label>
                        <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
                    </div>
                    <div class="input-group">
                        <label>Transaction Type</label>
                        <select name="filter_type" style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; box-sizing: border-box;">
                            <option value="">All Types</option>
                            <option value="Cash" <?php if ($filter_type == 'Cash') echo 'selected'; ?>>Cash</option>
                            <option value="Online" <?php if ($filter_type == 'Online') echo 'selected'; ?>>Online</option>
                            <option value="UPI" <?php if ($filter_type == 'UPI') echo 'selected'; ?>>UPI</option>
                            <option value="Bank Transfer" <?php if ($filter_type == 'Bank Transfer') echo 'selected'; ?>>Bank Transfer</option>
                            <option value="Other" <?php if ($filter_type == 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap;">
                    <button type="submit" class="btn" style="flex-grow: 1;"><i class="fas fa-check"></i> Apply Filters</button>
                    <a href="member_view_transaction.php" class="btn" style="background-color: #ffc107; color: #212529; flex-grow: 1;"><i class="fas fa-undo"></i> Reset Filters</a>
                    <a href="member_view_transaction.php?<?php echo $query_params; ?>" class="btn" style="background-color: #6c757d; flex-grow: 1;"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>