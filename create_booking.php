<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

try {
    $room_id = $_POST['room_id'];
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $purpose = trim($_POST['purpose']);

    // ✅ FIXED: Get booking_date from form (not hardcoded to today)
    $booking_date = $_POST['booking_date'] ?? date('Y-m-d');
    $today = date('Y-m-d');
    $admin_id = $_SESSION['admin_id'];

    if ($end_time <= $start_time) {
        echo json_encode(['success' => false, 'message' => 'Waktu selesai harus lebih besar dari waktu mulai']);
        exit;
    }

    // ✅ FIXED: Only check ACTIVE bookings for conflicts
    $stmt = $pdo->prepare("
    SELECT * FROM bookings 
    WHERE room_id = ? 
    AND booking_date = ? 
    AND status = 'active'
    AND (
        (start_time < ? AND end_time > ?) OR
        (start_time < ? AND end_time > ?) OR
        (start_time >= ? AND end_time <= ?)
    )
");
    $stmt->execute([$room_id, $booking_date, $end_time, $start_time, $end_time, $start_time, $start_time, $end_time]);


    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Ruangan sudah dibooking pada waktu tersebut']);
        exit;
    }

    // Generate permit number based on selected date
    $date_code = date('Ymd', strtotime($booking_date));
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE booking_date = ?");
    $stmt->execute([$booking_date]);
    $count = $stmt->fetch()['count'] + 1;
    $permit_number = 'BK-' . $date_code . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

    // ✅ FIXED: Insert with status = 'active' and use booking_date from form
    $stmt = $pdo->prepare("
        INSERT INTO bookings (room_id, nim, nama, booking_date, start_time, end_time, purpose, permit_number, created_by, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$room_id, $nim, $nama, $booking_date, $start_time, $end_time, $purpose, $permit_number, $admin_id]);

    echo json_encode(['success' => true, 'message' => 'Booking berhasil dibuat! Permit: ' . $permit_number]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
