<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow all domains to access this resource (for development purposes)
header("Access-Control-Allow-Origin: *"); // Allows all origins (be cautious in production)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow methods (GET, POST, OPTIONS)
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respond with a 200 status code for preflight requests
    header("HTTP/1.1 200 OK");
    exit(); // Stop further processing
}

// Include the database connection file
include 'db.php';   // Database connection
include 'cors.php'; // CORS header for cross-origin requests (if needed)

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

    // Execute the query and handle any potential errors
    if (!$stmt->execute()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Query execution failed',
            'error' => $stmt->error
        ]);
        exit();
    }

    // Get the results from the executed query
    $result = $stmt->get_result();
    $reports = [];

    // Fetch data into the reports array
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }

    // Check if any data was retrieved
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
