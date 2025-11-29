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

// Define search variable
$search_term = $_GET['search'] ?? '';

// Fetch all customers from the database
$customers = [];
$sql = "SELECT id, customer_no, name, mobile_no, aadhar_no, ration_card_no, created_at FROM customers";

if (!empty($search_term)) {
    $sql .= " WHERE customer_no LIKE ? OR name LIKE ? OR mobile_no LIKE ? OR aadhar_no LIKE ?";
}

$sql .= " ORDER BY created_at DESC";

if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($search_term)) {
        $like_term = "%" . $search_term . "%";
        $stmt->bind_param("ssss", $like_term, $like_term, $like_term, $like_term);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Customers</title>
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
        <div class="logo"><a href="member_dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav">
            <a href="member_dashboard.php" class="active">Dashboard</a>
            <a href="member_profile.php" title="Profile"><i class="fas fa-user"></i></a>
            <a href="member_logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <div class="content-card">
            <h1 class="page-header">Registered Customers</h1>
            <div class="search-container">
                <form action="member_view_customers.php" method="get">
                    <div class="input-group">
                        <input type="text" name="search" placeholder="Search by Customer No, Name, Mobile, or Aadhar..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    <button type="submit" class="btn" style="width: auto;" title="Search"><i class="fas fa-search"></i></button>
                    <a href="download_all_member_customers.php?search=<?php echo urlencode($search_term); ?>" class="btn" style="background-color: #218838; width: auto;" title="Download All"><i class="fas fa-download"></i></a>
                    <a href="member_dashboard.php" class="btn" style="background-color: #6c757d; width: auto;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
                </form>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer No</th>
                            <th>Name</th>
                            <th>Mobile No</th>
                            <th>Aadhar No</th>
                            <th>Ration Card No</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($customers as $customer): ?>
                        <tr>
                            <td><?php echo $customer['id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['customer_no']); ?></td>
                            <td><a href="member_customer_details.php?id=<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></a></td>
                            <td><?php echo htmlspecialchars($customer['mobile_no']); ?></td>
                            <td><?php echo htmlspecialchars($customer['aadhar_no']); ?></td>
                            <td><?php echo htmlspecialchars($customer['ration_card_no']); ?></td>
                            <td><?php echo date("F j, Y", strtotime($customer['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>