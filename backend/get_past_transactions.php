<?php
include 'cors.php';
include 'db.php';

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if user_id is provided in the request
    if (!isset($data['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        exit();
    }

    // Get user_id from the request
    $user_id = $data['user_id'];

    // Prepare the SQL query to fetch past transactions from the payments table
    $stmt = $conn->prepare("SELECT id, amount, payment_status, transaction_id, DATE_FORMAT(payment_date, '%Y-%m-%d %H:%i:%s') AS payment_date FROM payments WHERE user_id = ?");
    $stmt->bind_param("i", $user_id); // Bind user_id parameter

    // Execute the query
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $transactions = [];

        // Fetch all transactions for the given user_id
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }

        // Return the transactions as a JSON response
        echo json_encode(['status' => 'success', 'transactions' => $transactions]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error fetching transactions']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>