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

$stmt = $pdo->query("
    SELECT bookings.*, rooms.room_name, admins.username as deleted_by_name
    FROM bookings 
    JOIN rooms ON bookings.room_id = rooms.id 
    LEFT JOIN admins ON bookings.deleted_by = admins.id
    ORDER BY bookings.booking_date DESC, bookings.start_time DESC
");
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History - Room Booking System</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/history.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="dashboard-page">
    <div class="top-nav">
        <div class="nav-tabs">
            <a href="dashboard.php" class="nav-tab inactive">Dashboard</a>
            <a href="manage_bookings.php" class="nav-tab inactive">Manage</a>
            <a href="booking_history.php" class="nav-tab active">History</a>
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
        <h1 class="dashboard-title">Booking History</h1>
        <div class="filter-section">
            <div class="filter-title">Filter Bookings</div>
            <div class="filter-form">
                <input type="date" id="filter_date_from" placeholder="Dari Tanggal">
                <input type="date" id="filter_date_to" placeholder="Sampai Tanggal">
                <input type="text" id="filter_nim" placeholder="Cari NIM">
                <select id="filter_status">
                    <option value="">Semua Status</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="deleted">Deleted</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn-filter" onclick="filterBookings()">Filter</button>
                <button class="btn-reset" onclick="resetFilter()">Reset</button>
            </div>
        </div>

        <div class="booking-list-container" id="bookings-container">
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card <?php echo $booking['status']; ?>"
                    data-date="<?php echo $booking['booking_date']; ?>"
                    data-nim="<?php echo $booking['nim']; ?>"
                    data-status="<?php echo $booking['status']; ?>">
                    <div class="booking-info">
                        <div class="booking-row">
                            <span class="booking-label">Booking ID</span>
                            <span class="booking-colon">:</span>
                            <span class="booking-value">
                                <?php echo htmlspecialchars($booking['permit_number']); ?>
                                <span class="status-badge <?php echo $booking['status']; ?>">
                                    <?php
                                    if ($booking['status'] == 'active') echo 'ACTIVE';
                                    elseif ($booking['status'] == 'expired') echo 'EXPIRED';
                                    else echo 'DELETED';
                                    ?>
                                </span>
                            </span>
                        </div>
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
                            <span class="booking-label">NIM</span>
                            <span class="booking-colon">:</span>
                            <span class="booking-value"><?php echo htmlspecialchars($booking['nim']); ?></span>
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
                        <?php if ($booking['deleted_at']): ?>
                            <div class="booking-row deleted-info">
                                <span class="booking-label">Dihapus pada</span>
                                <span class="booking-colon">:</span>
                                <span class="booking-value"><?php echo date('d M Y H:i', strtotime($booking['deleted_at'])); ?> oleh <?php echo htmlspecialchars($booking['deleted_by_name'] ?? 'Unknown'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="booking-actions">
                        <?php if (!$booking['deleted_at']): ?>
                            <button class="btn-print" onclick="printBooking(<?php echo $booking['id']; ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                    <rect x="6" y="14" width="12" height="8"></rect>
                                </svg>
                            </button>

                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function printBooking(bookingId) {
            window.open('print_booking.php?id=' + bookingId, '_blank');
        }

        function filterBookings() {
            const dateFrom = document.getElementById('filter_date_from').value;
            const dateTo = document.getElementById('filter_date_to').value;
            const nim = document.getElementById('filter_nim').value.toLowerCase();
            const status = document.getElementById('filter_status').value;

            const cards = document.querySelectorAll('.booking-card');

            cards.forEach(card => {
                let show = true;
                const cardDate = card.getAttribute('data-date');
                const cardNim = card.getAttribute('data-nim').toLowerCase();
                const cardStatus = card.getAttribute('data-status');

                if (dateFrom && cardDate < dateFrom) show = false;
                if (dateTo && cardDate > dateTo) show = false;
                if (nim && !cardNim.includes(nim)) show = false;
                if (status && cardStatus !== status) show = false;

                card.style.display = show ? 'flex' : 'none';
            });
        }

        function resetFilter() {
            document.getElementById('filter_date_from').value = '';
            document.getElementById('filter_date_to').value = '';
            document.getElementById('filter_nim').value = '';
            document.getElementById('filter_status').value = '';

            document.querySelectorAll('.booking-card').forEach(card => {
                card.style.display = 'flex';
            });
        }
    </script>
</body>

</html>