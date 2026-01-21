<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['bookings' => []]);
    exit;
}

require_once 'config/database.php';

$date = $_GET['date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT bookings.*, rooms.room_name 
    FROM bookings 
    JOIN rooms ON bookings.room_id = rooms.id 
    WHERE bookings.booking_date = ?
    AND bookings.status = 'active'
");
$stmt->execute([$date]);
$bookings = $stmt->fetchAll();

echo json_encode(['bookings' => $bookings]);
?>
