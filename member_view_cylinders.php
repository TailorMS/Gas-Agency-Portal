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

// Fetch all cylinder stock records
$stocks = [];
$sql = "SELECT * FROM cylinder_stock";

if (!empty($search_date)) {
    $sql .= " WHERE stock_date = ?";
}

$sql .= " ORDER BY stock_date DESC, id DESC";

if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($search_date)) {
        $stmt->bind_param("s", $search_date);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stocks = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Cylinder Stock</title>
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
            padding: 8px 12px;
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
            <h1 class="page-header">Cylinder Stock Details</h1>
            <div class="action-bar" style="justify-content: space-between; margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <form action="member_view_cylinders.php" method="get" style="display: flex; gap: 10px; align-items: center; margin-bottom: 0;">
                        <div class="input-group" style="margin-bottom: 0;">
                            <input type="date" name="search_date" value="<?php echo htmlspecialchars($search_date); ?>">
                        </div>
                        <button type="submit" class="btn" title="Search"><i class="fas fa-search"></i></button>
                    </form>
                    <a href="download_all_cylinders.php?search_date=<?php echo urlencode($search_date); ?>" class="btn" style="background-color: #218838; width: auto;" title="Download All"><i class="fas fa-download"></i></a>
                </div>
                <a href="member_dashboard.php" class="btn" style="background-color: #6c757d; width: auto;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>5 kg</th>
                            <th>10 kg Comp.</th>
                            <th>Xtralite</th>
                            <th>14.2 kg</th>
                            <th>19 kg</th>
                            <th>Xtratej</th>
                            <th>47.5 kg</th>
                            <th>425 kg Jumbo</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($stocks as $stock): ?>
                        <tr>
                            <td><?php echo date("M j, Y", strtotime($stock['stock_date'])); ?></td>
                            <td><?php echo htmlspecialchars($stock['qty_5_kg']); ?></td>
                            <td><?php echo htmlspecialchars($stock['qty_10_kg_composite']); ?></td>
                            <td><?php echo htmlspecialchars($stock['qty_xtralite']); ?></td>
                            <td><?php echo htmlspecialchars($stock['qty_14_2_kg']); ?></td>
                            <td><?php echo htmlspecialchars($stock['qty_19_kg']); ?></td>
                            <td><?php echo htmlspecialchars($stock['qty_xtratej']); ?></td>
                            <td><?php echo htmlspecialchars($stock['qty_47_5_kg']); ?></td>
                            <td><?php echo htmlspecialchars($stock['qty_425_kg_jumbo']); ?></td>
                            <td>
                                <strong>
                                    <a href="member_cylinder_detail.php?id=<?php echo $stock['id']; ?>"><?php echo htmlspecialchars($stock['total_quantity']); ?></a>
                                </strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>