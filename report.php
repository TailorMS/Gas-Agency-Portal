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

// Get selected date range from URL, if any
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$page_title = "Financial Report (Last 30 Days)";
$report_data = [];
$pie_data_in = $pie_data_out = [];

// Build WHERE clause for dates
$where_clauses = [];
$bind_types = "";
$bind_params = [];

if (!empty($from_date) && !empty($to_date)) {
    $where_clauses[] = "transaction_date BETWEEN ? AND ?";
    $bind_types .= "ss";
    array_push($bind_params, $from_date, $to_date);
    $page_title = "Financial Report from " . date("M j, Y", strtotime($from_date)) . " to " . date("M j, Y", strtotime($to_date));
} elseif (!empty($from_date)) {
    $where_clauses[] = "transaction_date >= ?";
    $bind_types .= "s";
    $bind_params[] = $from_date;
    $page_title = "Financial Report from " . date("M j, Y", strtotime($from_date));
} elseif (!empty($to_date)) {
    $where_clauses[] = "transaction_date <= ?";
    $bind_types .= "s";
    $bind_params[] = $to_date;
    $page_title = "Financial Report up to " . date("M j, Y", strtotime($to_date));
} else {
    $where_clauses[] = "transaction_date >= CURDATE() - INTERVAL 30 DAY";
}

$where_sql = " WHERE " . implode(" AND ", $where_clauses);

// --- Fetch data for Bar Chart ---
$sql_bar = "SELECT transaction_date, SUM(money_in) as total_in, SUM(money_out) as total_out FROM transactions" . $where_sql . " GROUP BY transaction_date ORDER BY transaction_date ASC";
if ($stmt = $mysqli->prepare($sql_bar)) {
    if (!empty($bind_params)) { $stmt->bind_param($bind_types, ...$bind_params); }
    $stmt->execute();
    $report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// --- Fetch data for Pie Charts ---
$sql_pie_in = "SELECT transaction_type, SUM(money_in) as total FROM transactions" . $where_sql . " AND money_in > 0 GROUP BY transaction_type";
if ($stmt = $mysqli->prepare($sql_pie_in)) {
    if (!empty($bind_params)) { $stmt->bind_param($bind_types, ...$bind_params); }
    $stmt->execute();
    $pie_data_in = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$sql_pie_out = "SELECT transaction_type, SUM(money_out) as total FROM transactions" . $where_sql . " AND money_out > 0 GROUP BY transaction_type";
if ($stmt = $mysqli->prepare($sql_pie_out)) {
    if (!empty($bind_params)) { $stmt->bind_param($bind_types, ...$bind_params); }
    $stmt->execute();
    $pie_data_out = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$mysqli->close();

// Prepare data for Chart.js
$labels = [];
$money_in_data = [];
$money_out_data = [];

foreach($report_data as $data) {
    $labels[] = date("M j, Y", strtotime($data['transaction_date']));
    $money_in_data[] = $data['total_in'];
    $money_out_data[] = $data['total_out'];
}

// Prepare data for Pie Charts
$pie_labels_in = array_column($pie_data_in, 'transaction_type');
$pie_values_in = array_column($pie_data_in, 'total');

$pie_labels_out = array_column($pie_data_out, 'transaction_type');
$pie_values_out = array_column($pie_data_out, 'total');

// Function to generate random colors for pie charts
function generate_colors($count) {
    $colors = [];
    for ($i = 0; $i < $count; $i++) {
        $colors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
    return $colors;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-image: url('background_bg.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .pie-charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .filter-form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>

    <div class="menubar">
        <div class="logo"><a href="dashboard.php">AmodIndane</a></div>
        <nav class="menubar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>

    <div class="page-container">
        <a href="dashboard.php" class="btn" style="display: inline-block; width: auto; margin-bottom: 20px; background-color: #6c757d;" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
        
        <div class="content-card">
            <h1 class="page-header"><?php echo htmlspecialchars($page_title); ?></h1>
            
            <div class="filter-form">
                <form action="report.php" method="get" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="input-group" style="margin-bottom: 0;"><label>From</label><input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" style="padding: 10px; border-radius: 6px; border: 1px solid #dddfe2;"></div>
                    <div class="input-group" style="margin-bottom: 0;"><label>To</label><input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" style="padding: 10px; border-radius: 6px; border: 1px solid #dddfe2;"></div>
                    <button type="submit" class="btn">Apply Filter</button>
                </form>
                <a href="report.php" class="btn" style="background-color: #ffc107; color: #212529; margin-bottom: 0;">View Last 30 Days</a>
            </div>

            <div class="chart-container">
                <canvas id="transactionChart"></canvas>
            </div>

            <div class="pie-charts-container">
                <div class="chart-container">
                    <h3 style="text-align: center;">Money In by Type</h3>
                    <canvas id="moneyInPieChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3 style="text-align: center;">Money Out by Type</h3>
                    <canvas id="moneyOutPieChart"></canvas>
                </div>
            </div>

        </div>
    </div>

<script>
const ctx = document.getElementById('transactionChart').getContext('2d');
const transactionChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Money In (Credit)',
            data: <?php echo json_encode($money_in_data); ?>,
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }, {
            label: 'Money Out (Debit)',
            data: <?php echo json_encode($money_out_data); ?>,
            backgroundColor: 'rgba(220, 53, 69, 0.7)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<script>
const pieCtxIn = document.getElementById('moneyInPieChart').getContext('2d');
const moneyInPieChart = new Chart(pieCtxIn, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($pie_labels_in); ?>,
        datasets: [{
            label: 'Money In by Type',
            data: <?php echo json_encode($pie_values_in); ?>,
            backgroundColor: <?php echo json_encode(generate_colors(count($pie_labels_in))); ?>,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } }
    }
});

const pieCtxOut = document.getElementById('moneyOutPieChart').getContext('2d');
const moneyOutPieChart = new Chart(pieCtxOut, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($pie_labels_out); ?>,
        datasets: [{
            label: 'Money Out by Type',
            data: <?php echo json_encode($pie_values_out); ?>,
            backgroundColor: <?php echo json_encode(generate_colors(count($pie_labels_out))); ?>,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } }
    }
});
</script>

</body>
</html>