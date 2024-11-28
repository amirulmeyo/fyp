<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow all domains to access this resource
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Include the database connection file
include 'db.php'; // Database connection

// Set the content type for the response
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare the SQL query to retrieve trainee payment records
    $stmt = $conn->prepare("
        SELECT u.username AS trainee_username, p.amount, p.payment_status, p.transaction_id, p.payment_date
        FROM payments p
        JOIN users u ON p.user_id = u.id
        WHERE u.role = 'trainee'
    ");

    if (!$stmt->execute()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Query execution failed',
            'error' => $stmt->error
        ]);
        exit();
    }

    $result = $stmt->get_result();
    $reports = [];

    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }

    if (count($reports) > 0) {
        echo json_encode([
            'status' => 'success',
            'financial_reports' => $reports
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No trainee payment records found'
        ]);
    }
}
?>
