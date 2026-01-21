<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php';

$today = date('Y-m-d');

$pdo->query("
    UPDATE bookings 
    SET status = 'expired'
    WHERE booking_date < '$today' 
    AND status = 'active'
");

$today_display = date('d/m/Y');
$current_time = date('H:i');

// ✅ FIXED: Fetch rooms for dropdown
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_name");
$rooms = $stmt->fetchAll();

// ✅ FIXED: Define time options for dropdown
$time_options = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

$stmt = $pdo->query("
    SELECT bookings.*, rooms.room_name 
    FROM bookings 
    JOIN rooms ON bookings.room_id = rooms.id 
    WHERE bookings.status = 'active'
    ORDER BY bookings.booking_date ASC, bookings.start_time ASC
");
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Booking - Room Booking System</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="dashboard-page">
    <div class="top-nav">
        <div class="nav-tabs">
            <a href="dashboard.php" class="nav-tab inactive">Dashboard</a>
            <a href="manage_bookings.php" class="nav-tab active">Manage</a>
            <a href="booking_history.php" class="nav-tab inactive">History</a>
        </div>

        <div class="nav-info">
            <div class="time-display"><?php echo $current_time; ?></div>
            <div class="date-display"><?php echo $today_display; ?></div>
            <button class="logout-btn" onclick="window.location.href='logout.php'">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </button>
        </div>
    </div>

    <div class="dashboard-container">
        <h1 class="dashboard-title">Manage Booking</h1>
        <div class="booking-list-container">
            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-info">
                            <div class="booking-row">
                                <span class="booking-label">Booking ID</span>
                                <span class="booking-colon">:</span>
                                <span class="booking-value"><?php echo htmlspecialchars($booking['permit_number']); ?></span>
                            </div>
                            
                            <!-- ✅ FIXED: Added date display -->
                            <div class="booking-row">
                                <span class="booking-label">Tanggal</span>
                                <span class="booking-colon">:</span>
                                <span class="booking-value"><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></span>
                            </div>
                            
                            <div class="booking-row">
                                <span class="booking-label">Ruang</span>
                                <span class="booking-colon">:</span>
                                <span class="booking-value"><?php echo htmlspecialchars($booking['room_name']); ?></span>
                            </div>
                            <div class="booking-row">
                                <span class="booking-label">Jam Peminjaman</span>
                                <span class="booking-colon">:</span>
                                <span class="booking-value"><?php echo date('H:i', strtotime($booking['start_time'])) . '-' . date('H:i', strtotime($booking['end_time'])); ?></span>
                            </div>
                            <div class="booking-row">
                                <span class="booking-label">Peminjam</span>
                                <span class="booking-colon">:</span>
                                <span class="booking-value"><?php echo htmlspecialchars($booking['nama']); ?></span>
                            </div>
                            <div class="booking-row">
                                <span class="booking-label">Keterangan</span>
                                <span class="booking-colon">:</span>
                                <span class="booking-value"><?php echo htmlspecialchars($booking['purpose']); ?></span>
                            </div>
                        </div>
                        <div class="booking-actions">
                            <button class="btn-edit" onclick='openEditModal(<?php echo json_encode($booking); ?>)'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button class="btn-print" onclick="printBooking(<?php echo $booking['id']; ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                    <rect x="6" y="14" width="12" height="8"></rect>
                                </svg>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>Tidak ada booking aktif</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ✅ FIXED: Added date picker to edit modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-box">
            <h2 class="modal-title">Form Edit Booking</h2>
            <p class="modal-subtitle">Masukan Data Untuk Melanjutkan</p>

            <div id="editAlert" class="modal-alert" style="display: none;"></div>

            <form class="modal-form" id="editForm">
                <input type="hidden" id="edit_booking_id" name="booking_id">

                <!-- ✅ NEW: Date picker -->
                <div class="form-group">
                    <label>Tanggal Booking</label>
                    <input type="date" id="edit_booking_date" name="booking_date" 
                           min="<?php echo $today; ?>" required>
                </div>

                <div class="form-group">
                    <label>Ruangan</label>
                    <select id="edit_room_id" name="room_id" required>
                        <option value="">Pilih Ruangan</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>">
                                <?php echo htmlspecialchars($room['room_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Awal Jam Peminjaman</label>
                    <select id="edit_start_time" name="start_time" required>
                        <option value="">Pilih Jam Mulai</option>
                        <?php foreach ($time_options as $time): ?>
                            <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Akhir Jam Peminjaman</label>
                    <select id="edit_end_time" name="end_time" required>
                        <option value="">Pilih Jam Selesai</option>
                        <?php foreach ($time_options as $time): ?>
                            <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Keterangan Peminjaman</label>
                    <input type="text" id="edit_purpose" name="purpose" placeholder="Keterangan Peminjaman" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-delete" onclick="deleteBooking()">Hapus Booking</button>
                    <button type="submit" class="btn-submit">Edit Booking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.bookingsData = <?php echo json_encode($bookings); ?>;
    </script>

    <script src="js/manage_bookings.js"></script>
</body>

</html>
