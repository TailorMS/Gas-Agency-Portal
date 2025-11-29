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

// Get search term
$search_term = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'date'; // Default sort by date
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';

// Fetch transactions from the database
$sql = "SELECT transaction_id, transaction_date, transaction_type, name, description, money_in, money_out FROM transactions";

$where_clauses = [];
$bind_types = "";
$bind_params = [];

$transactions = [];

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

// Determine ORDER BY clause based on sort parameter and append to SQL
switch ($sort_by) {
    case 'type':
        $sql .= " ORDER BY transaction_type ASC, transaction_date DESC";
        break;
    case 'money_in':
        $sql .= " ORDER BY CAST(money_in AS DECIMAL(10,2)) DESC, transaction_date DESC";
        break;
    case 'money_out':
        $sql .= " ORDER BY CAST(money_out AS DECIMAL(10,2)) DESC, transaction_date DESC";
        break;
    case 'date':
    default:
        $sql .= " ORDER BY transaction_date DESC, created_at DESC";
        break;
}

if ($stmt = $mysqli->prepare($sql)) {
    if (!empty($bind_params)) { $stmt->bind_param($bind_types, ...$bind_params); }
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    // Log the action
    $log_action = "Downloaded all transaction data";
    $log_sql = "INSERT INTO member_history (member_id, member_username, action) VALUES (?, ?, ?)";
    if($log_stmt = $mysqli->prepare($log_sql)){
        $log_stmt->bind_param("iss", $_SESSION["id"], $_SESSION["member_username"], $log_action);
        $log_stmt->execute();
        $log_stmt->close();
    }
    $stmt->close();
}
$mysqli->close();

// Set headers to download file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transactions_' . date('Y-m-d') . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('transaction_id', 'Transaction Date', 'Transaction Type', 'Name', 'Description', 'Money In', 'Money Out'));

// Loop over the rows, outputting them
if (count($transactions) > 0) {
    foreach ($transactions as $row) {
        fputcsv($output, array(
            $row['transaction_id'], $row['transaction_date'], $row['transaction_type'],
            $row['name'], $row['description'], $row['money_in'], $row['money_out']
        ));
    }
}

exit();
?>