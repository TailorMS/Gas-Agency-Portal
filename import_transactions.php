<?php
// Initialize the session
session_start();

// Check if the user is logged in as an admin, if not then redirect to login page
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: Admin.php");
    exit;
}

// Include db connect file
require_once "db_connect.php";

$message = '';
$status = 'error';

if(isset($_POST["import"]) && isset($_FILES["transaction_csv"])){

    $filename = $_FILES["transaction_csv"]["tmp_name"];
    $file_ext = pathinfo($_FILES["transaction_csv"]["name"], PATHINFO_EXTENSION);

    if($file_ext != 'csv'){
        $_SESSION['import_message'] = "Invalid file type. Please upload a CSV file.";
        $_SESSION['import_status'] = "error";
        header("location: transaction_management.php");
        exit;
    }

    if($_FILES["transaction_csv"]["size"] > 0) {
        $file = fopen($filename, "r");

        $success_count = 0;
        $error_count = 0;
        $duplicate_count = 0;
        $line_number = 0;
        $errors = [];

        // Skip header row
        fgetcsv($file);
        $line_number++;

        $mysqli->begin_transaction();

        try {
            $sql_check = "SELECT transaction_id FROM transactions WHERE transaction_id = ?";
            $stmt_check = $mysqli->prepare($sql_check);

            $sql_insert = "INSERT INTO transactions (transaction_id, transaction_date, transaction_type, name, description, money_in, money_out) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $mysqli->prepare($sql_insert);

            while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
                $line_number++;
                $transaction_id = trim($getData[0]);

                // Basic validation
                if (empty($transaction_id) || empty($getData[1]) || empty($getData[2])) {
                    $errors[] = "Line $line_number: Missing required data (Transaction ID, Date, or Type).";
                    $error_count++;
                    continue;
                }

                // Check for duplicates
                $stmt_check->bind_param("s", $transaction_id);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    $errors[] = "Line $line_number: Duplicate Transaction ID '$transaction_id'.";
                    $duplicate_count++;
                    continue;
                }

                $money_in = !empty($getData[5]) ? (float)$getData[5] : 0;
                $money_out = !empty($getData[6]) ? (float)$getData[6] : 0;

                $stmt_insert->bind_param("sssssdd", $transaction_id, $getData[1], $getData[2], $getData[3], $getData[4], $money_in, $money_out);
                if ($stmt_insert->execute()) {
                    $success_count++;
                } else {
                    $errors[] = "Line $line_number: Failed to insert transaction '$transaction_id'. DB Error: " . $stmt_insert->error;
                    $error_count++;
                }
            }

            $stmt_check->close();
            $stmt_insert->close();
            $mysqli->commit();

        } catch (Exception $e) {
            $mysqli->rollback();
            $errors[] = "A database transaction error occurred: " . $e->getMessage();
            $error_count += $success_count; // Revert success count
            $success_count = 0;
        }

        fclose($file);

        $message = "Import Summary:\n";
        $message .= "Successfully imported: $success_count\n";
        $message .= "Duplicates skipped: $duplicate_count\n";
        $message .= "Rows with errors: $error_count\n";
        if (!empty($errors)) {
            $message .= "\nError Details:\n" . implode("\n", array_slice($errors, 0, 10)); // Show first 10 errors
        }

        $status = ($error_count > 0 || $duplicate_count > 0) ? 'warning' : 'success';
    }
}

$_SESSION['import_message'] = $message;
$_SESSION['import_status'] = $status;
header("location: transaction_management.php");
exit;
?>