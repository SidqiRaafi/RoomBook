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

$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_name");
$rooms = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT bookings.*, rooms.room_name 
    FROM bookings 
    JOIN rooms ON bookings.room_id = rooms.id 
    WHERE bookings.booking_date = ?
    AND bookings.status = 'active'
");
$stmt->execute([$today]);
$bookings = $stmt->fetchAll();

$time_slots = [
    '08:00-09:00',
    '09:00-10:00',
    '10:00-11:00',
    '11:00-12:00',
    '12:00-13:00',
    '13:00-14:00',
    '14:00-15:00',
    '15:00-16:00',
    '16:00-17:00',
    '17:00-18:00'
];

function isBooked($room_id, $time_slot, $bookings)
{
    list($slot_start, $slot_end) = explode('-', $time_slot);

    foreach ($bookings as $booking) {
        if ($booking['room_id'] == $room_id) {
            $booking_start = date('H:i', strtotime($booking['start_time']));
            $booking_end = date('H:i', strtotime($booking['end_time']));
            if ($slot_start < $booking_end && $slot_end > $booking_start) {
                return true;
            }
        }
    }
    return false;
}

$time_options = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Room Booking System</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="dashboard-page">
    <div class="top-nav">
        <div class="nav-tabs">
            <a href="dashboard.php" class="nav-tab active">Dashboard</a>
            <a href="manage_bookings.php" class="nav-tab inactive">Manage</a>
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
        <h1 class="dashboard-title">Ruang Tersedia</h1>
        <div class="grid-container">
            <div class="availability-grid">
                <?php foreach ($rooms as $room): ?>
                    <div class="room-label">
                        <?php echo htmlspecialchars($room['room_name']); ?>
                    </div>
                    <?php foreach ($time_slots as $slot): ?>
                        <?php
                        $is_booked = isBooked($room['id'], $slot, $bookings);
                        $class = $is_booked ? 'booked' : 'available';
                        ?>
                        <div class="time-slot <?php echo $class; ?>">
                            <?php echo $slot; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dashboard-footer">
            <div class="legend">
                <p>Hijau = Ruangan tersedia</p>
                <p>Abu-abu = Ruangan tidak tersedia</p>
            </div>
            <button class="btn-add-booking" id="btnOpenModal">Tambah Booking</button>
        </div>
    </div>

    <!-- ✅ MODAL WITH DATE PICKER -->
    <div class="modal-overlay" id="modalBooking">
        <div class="modal-box">
            <h2 class="modal-title">Form Booking</h2>
            <p class="modal-subtitle">Masukan Data Untuk Melanjutkan</p>

            <div id="modalAlert" class="modal-alert" style="display: none;"></div>

            <form class="modal-form" id="bookingForm" method="POST" action="create_booking.php">
                <!-- ✅ NEW: Date Picker -->
                <div class="form-group">
                    <label>Tanggal Booking</label>
                    <input type="date" name="booking_date" id="bookingDate"
                        value="<?php echo $today; ?>"
                        min="<?php echo $today; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" name="nim" placeholder="NIM" required>
                </div>

                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama" placeholder="Nama" required>
                </div>

                <div class="form-group">
                    <label>Ruangan</label>
                    <select name="room_id" id="roomSelect" required>
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
                    <select name="start_time" id="startTime" required>
                        <option value="">Pilih Jam Mulai</option>
                        <?php foreach ($time_options as $time): ?>
                            <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Akhir Jam Peminjaman</label>
                    <select name="end_time" id="endTime" required>
                        <option value="">Pilih Jam Selesai</option>
                        <?php foreach ($time_options as $time): ?>
                            <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Keterangan Peminjaman</label>
                    <input type="text" name="purpose" placeholder="Keterangan Peminjaman" required>
                </div>

                <button type="submit" class="btn-submit-booking">Tambah Booking</button>
            </form>
        </div>
    </div>

    <script>
        window.bookingsData = <?php echo json_encode($bookings); ?>;
    </script>
    <script src="js/dashboard.js"></script>
</body>

</html>