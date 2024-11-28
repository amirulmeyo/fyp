<?php
include 'cors.php'; // CORS header for cross-origin requests (if needed)
include 'db.php';   // Database connection file

header("Content-Type: application/json; charset=UTF-8");

// Function to handle the file upload
function uploadReceipt($file) {
    $target_dir = "uploads/";  // Directory to store uploaded receipts
    $target_file = $target_dir . uniqid() . "_" . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is too large
    if ($file["size"] > 5000000) {
        return json_encode(["status" => "error", "message" => "File too large. Maximum allowed size is 5MB."]);
    }

    // Check file type (allowed types: JPG, JPEG, PNG, PDF)
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'pdf'])) {
        return json_encode(["status" => "error", "message" => "Invalid file type. Allowed types: JPG, JPEG, PNG, PDF."]);
    }

    // Try to upload the file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file; // Return the file path if upload is successful
    } else {
        return json_encode(["status" => "error", "message" => "Error uploading the file."]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $transactionId = $_POST['transaction_id'] ?? null;

    if (!$userId || !$amount || !$transactionId) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
        exit;
    }

    $paymentStatus = 'pending';
    $receiptPath = null;

    // Handle receipt file upload if available
    if (isset($_FILES['receipt'])) {
        $receipt = $_FILES['receipt'];
        $receiptPath = uploadReceipt($receipt);

        // Check if the upload was successful
        if (!str_contains($receiptPath, 'uploads/')) {
            echo $receiptPath; // Return the error message from uploadReceipt
            exit;
        }
    }

    // Insert payment details into the database
    $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, payment_status, transaction_id, receipt_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $userId, $amount, $paymentStatus, $transactionId, $receiptPath);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'transaction_id' => $transactionId,
            'message' => 'Payment recorded successfully',
            'receipt_path' => $receiptPath
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to record payment.']);
    }
}
?>
