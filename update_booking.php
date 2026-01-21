<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

try {
    $booking_id = $_POST['booking_id'];
    $room_id = $_POST['room_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $purpose = trim($_POST['purpose']);
    $today = date('Y-m-d');
    
    if ($end_time <= $start_time) {
        echo json_encode(['success' => false, 'message' => 'Waktu selesai harus lebih besar dari waktu mulai']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM bookings 
        WHERE room_id = ? 
        AND booking_date = ? 
        AND id != ?
        AND (
            (start_time < ? AND end_time > ?) OR
            (start_time < ? AND end_time > ?) OR
            (start_time >= ? AND end_time <= ?)
        )
    ");
    $stmt->execute([$room_id, $today, $booking_id, $end_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Ruangan sudah dibooking pada waktu tersebut']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET room_id = ?, start_time = ?, end_time = ?, purpose = ?
        WHERE id = ?
    ");
    $stmt->execute([$room_id, $start_time, $end_time, $purpose, $booking_id]);
    
    echo json_encode(['success' => true, 'message' => 'Booking berhasil diupdate!']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
