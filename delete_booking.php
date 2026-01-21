<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $booking_id = $data['booking_id'];
    $admin_id = $_SESSION['admin_id'];
    $stmt = $pdo->prepare("
    UPDATE bookings 
    SET deleted_at = NOW(), 
        deleted_by = ?,
        status = 'deleted'
    WHERE id = ?
");
    $stmt->execute([$admin_id, $booking_id]);

    echo json_encode(['success' => true, 'message' => 'Booking berhasil dihapus!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
