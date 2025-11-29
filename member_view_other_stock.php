<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["member_loggedin"]) || $_SESSION["member_loggedin"] !== true){
    header("location: member_login.php");
    exit;
}
 
// Include config file
require_once "db_connect.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Other Stock - AmodIndane</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
    <link rel="stylesheet" href="dashboard_style.css">
    <link rel="stylesheet" href="table_style.css"> <!-- Assuming a shared stylesheet for tables -->
    <style>
        body { 
            background-image: url('background_bg.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .no-data {
            text-align: center;
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
            <h1 class="page-header">Other Stock Details</h1>
            <div class="action-bar" style="justify-content: space-between; margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <form action="member_view_other_stock.php" method="get" style="display: flex; gap: 10px; align-items: center; margin-bottom: 0;">
                        <div class="input-group" style="margin-bottom: 0;"><input type="date" name="search_date" value="<?php echo htmlspecialchars($_GET['search_date'] ?? ''); ?>"></div>
                        <button type="submit" class="btn" title="Search"><i class="fas fa-search"></i></button>
                    </form>
                    <a href="download_all_member_other_stock.php?search_date=<?php echo urlencode($_GET['search_date'] ?? ''); ?>" class="btn" style="background-color: #218838; width: auto;" title="Download All"><i class="fas fa-download"></i></a>
                </div>
                <a href="member_dashboard.php" class="btn" style="background-color: #6c757d; width: auto;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Stock ID</th>
                            <th>Date</th>
                            <th>Total Quantity</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $search_date = $_GET['search_date'] ?? '';
                        $sql = "SELECT id, stock_date, total_quantity FROM other_stock";
                        if (!empty($search_date)) { $sql .= " WHERE stock_date = ?"; }
                        $sql .= " ORDER BY stock_date DESC, id DESC";
                        
                        if ($stmt = mysqli_prepare($link, $sql)) {
                            if (!empty($search_date)) { mysqli_stmt_bind_param($stmt, "s", $search_date); }
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            if(mysqli_num_rows($result) > 0){
                                while($stock = mysqli_fetch_assoc($result)){
                                    echo "<tr>";
                                        echo "<td>" . $stock['id'] . "</td>";
                                        echo "<td>" . date("M j, Y", strtotime($stock['stock_date'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($stock['total_quantity']) . "</td>";
                                        echo "<td><a href='member_other_stock_detail.php?id=" . $stock['id'] . "'>View</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo '<tr><td colspan="4" class="no-data">No stock records found.</td></tr>';
                            }
                            mysqli_stmt_close($stmt);
                        }
                        mysqli_close($link);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 
</body>
</html>