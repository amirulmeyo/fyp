<?php
include 'cors.php'; // CORS header for cross-origin requests
include 'db.php';   // Database connection file

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Read raw input data (from PUT request)
    $data = json_decode(file_get_contents('php://input'), true);

    // Extract transaction_id and status
    $transactionId = $data['transaction_id'] ?? null;
    $status = $data['status'] ?? null;

    // Debug: Output received data
    error_log("Received transaction_id: " . $transactionId);
    error_log("Received status: " . $status);

    // Validate inputs
    if (!$transactionId || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
        exit;
    }

    // Validate status values allowed in the database
    if (!in_array($status, ['pending', 'completed', 'failed'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status value. Allowed values are "pending", "completed", or "failed".']);
        exit;
    }

    // Check if transaction exists
    $stmt = $conn->prepare("SELECT id FROM payments WHERE transaction_id = ?");
    if ($stmt === false) {
        error_log("Error preparing SELECT statement: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Database error while checking transaction.']);
        exit;
    }

    $stmt->bind_param("s", $transactionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_log("Transaction not found for ID: " . $transactionId);
        echo json_encode(['status' => 'error', 'message' => 'Transaction ID not found.']);
        exit;
    }

    error_log("Transaction found for ID: " . $transactionId);

    // Update payment status
    $stmt = $conn->prepare("UPDATE payments SET payment_status = ? WHERE transaction_id = ?");
    if ($stmt === false) {
        error_log("Error preparing UPDATE statement: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Database error while updating payment status.']);
        exit;
    }

    $stmt->bind_param("ss", $status, $transactionId);
    if (!$stmt->execute()) {
        error_log("Error executing UPDATE statement: " . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update payment status.']);
        exit;
    }

    // Debug: Rows affected
    error_log("Rows affected: " . $stmt->affected_rows);
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Payment status updated successfully.']);
    } else {
        error_log("No rows were affected during the update. Was the status already the same?");
        echo json_encode(['status' => 'error', 'message' => 'No changes made to the payment status.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
