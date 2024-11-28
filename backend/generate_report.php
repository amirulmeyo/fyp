<?php
include 'cors.php';
include 'db.php';

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'];
    $report_type = $data['report_type'];

    $stmt = $conn->prepare("SELECT * FROM reports WHERE user_id = ? AND report_type = ?");
    $stmt->bind_param("is", $user_id, $report_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'reports' => $reports]);
}
?>
