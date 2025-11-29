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

// Fetch all transactions
$search_term = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'date'; // Default sort by date

// New filter parameters
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';

$transactions = [];
$sql = "SELECT  transaction_id, transaction_date, transaction_type, name, description, money_in, money_out, created_at FROM transactions";

$where_clauses = [];
$bind_types = "";
$bind_params = [];

if (!empty($search_term)) {
    $where_clauses[] = "(transaction_type LIKE ? OR name LIKE ? OR description LIKE ?)";
    $bind_types .= "sss";
    $like_term = "%" . $search_term . "%";
    $bind_params[] = $like_term;
    $bind_params[] = $like_term;
    $bind_params[] = $like_term;
}

if (!empty($from_date)) { $where_clauses[] = "transaction_date >= ?"; $bind_types .= "s"; $bind_params[] = $from_date; }
if (!empty($to_date)) { $where_clauses[] = "transaction_date <= ?"; $bind_types .= "s"; $bind_params[] = $to_date; }
if (!empty($filter_type)) { $where_clauses[] = "transaction_type = ?"; $bind_types .= "s"; $bind_params[] = $filter_type; }

// Add filter for money_in or money_out based on sort selection
if ($sort_by == 'money_in') {
    $where_clauses[] = "money_in > 0";
} elseif ($sort_by == 'money_out') {
    $where_clauses[] = "money_out > 0";
}

if (!empty($where_clauses)) { $sql .= " WHERE " . implode(" AND ", $where_clauses); }
// Determine ORDER BY clause based on sort parameter
$order_by_clause = " ORDER BY transaction_date DESC, created_at DESC"; // Default
switch ($sort_by) {
    case 'type':
        $order_by_clause = " ORDER BY transaction_type ASC, transaction_date DESC";
        break;
    case 'money_in':
        $order_by_clause = " ORDER BY CAST(money_in AS DECIMAL(10,2)) DESC, transaction_date DESC";
        break;
    case 'money_out':
        $order_by_clause = " ORDER BY CAST(money_out AS DECIMAL(10,2)) DESC, transaction_date DESC";
        break;
    case 'date':
    default:
        // The default is already set
        break;
}
 
$sql .= $order_by_clause;
 
if ($stmt = $mysqli->prepare($sql)) { // Prepare the statement
    if (!empty($bind_params)) { // Bind parameters if there are any
        $stmt->bind_param($bind_types, ...$bind_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Transactions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body {
            background-image: url('background_bg.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        /* New style for search-container to handle multiple inputs */
        .search-container form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Space between elements */
            align-items: flex-end; /* Align items to the bottom */
        }
        .search-container .input-group {
            flex-grow: 1; /* Allow input groups to grow */
            min-width: 150px; /* Minimum width for input groups */
        }
        .search-container .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .search-container .btn {
            height: 44px; /* Match height of select/input fields */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .action-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
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
            <h1 class="page-header">Transaction History</h1>
            <div class="search-container">
                <div class="action-bar">
                    <form action="member_view_transaction.php" method="get" class="action-bar" style="flex-grow: 1;">
                        <div class="input-group" style="flex-grow: 1;">
                            <input type="text" name="search" placeholder="Search transactions..." value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                        <button type="submit" class="btn" title="Search"><i class="fas fa-search"></i></button>
                    </form>
                    <?php $query_params = http_build_query(array_filter(['search' => $search_term, 'sort' => $sort_by, 'from_date' => $from_date, 'to_date' => $to_date, 'filter_type' => $filter_type])); ?>
                    <a href="filter_member.php?<?php echo $query_params; ?>" class="btn" style="width: auto;" title="Filter and Sort"><i class="fas fa-filter"></i></a>
                    <a href="download_all_member_transactions.php?<?php echo $query_params; ?>" class="btn" style="background-color: #218838; width: auto;" title="Download All"><i class="fas fa-download"></i></a>
                    <a href="member_dashboard.php" class="btn" style="background-color: #6c757d; width: auto;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Money In</th>
                            <th>Money Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No transactions found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                <td><?php echo date("M j, Y", strtotime($transaction['transaction_date'])); ?></td>
                                <td><a href="member_transaction_details.php?transaction_id=<?php echo urlencode($transaction['transaction_id']); ?>"><?php echo !empty($transaction['name']) ? htmlspecialchars($transaction['name']) : '(View Details)'; ?></a></td>
                                <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                <td style="color: green;"><?php echo htmlspecialchars(number_format($transaction['money_in'], 2)); ?></td>
                                <td style="color: red;"><?php echo htmlspecialchars(number_format($transaction['money_out'], 2)); ?></td>
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