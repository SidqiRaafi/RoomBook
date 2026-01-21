<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'config/database.php';

$booking_id = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT bookings.*, rooms.room_name, admins.username as admin_name
    FROM bookings 
    JOIN rooms ON bookings.room_id = rooms.id 
    LEFT JOIN admins ON bookings.created_by = admins.id
    WHERE bookings.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Booking tidak ditemukan');
}

$bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$date = date('d', strtotime($booking['booking_date']));
$month = $bulan[date('m', strtotime($booking['booking_date']))];
$year = date('Y', strtotime($booking['booking_date']));
$tanggal_indo = "$date $month $year";

$today_date = date('d');
$today_month = $bulan[date('m')];
$today_year = date('Y');
$today_indo = "$today_date $today_month $today_year";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Booking - <?php echo $booking['permit_number']; ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/print.css">
</head>
<body class="print-page">
    
    <div class="page-wrapper">
        <div class="button-container no-print">
            <button class="print-btn" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                CETAK
            </button>
            <button class="download-btn" onclick="downloadPDF()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                DOWNLOAD PDF
            </button>
        </div>
        
        <div class="print-container">
            <div class="document-header">
                <h1>SURAT IZIN PEMINJAMAN RUANGAN</h1>
                <div class="permit-number">Nomor: <strong><?php echo htmlspecialchars($booking['permit_number']); ?></strong></div>
            </div>
            <div class="document-content">
                <p class="intro-text">
                    Yang bertanda tangan di bawah ini menerangkan bahwa:
                </p>
                <div class="info-table">
                    <div class="info-row">
                        <span class="info-label">NIM</span>
                        <span class="info-colon">:</span>
                        <span class="info-value"><?php echo htmlspecialchars($booking['nim']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Nama Peminjam</span>
                        <span class="info-colon">:</span>
                        <span class="info-value"><?php echo htmlspecialchars($booking['nama']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Ruangan</span>
                        <span class="info-colon">:</span>
                        <span class="info-value"><?php echo htmlspecialchars($booking['room_name']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Tanggal Peminjaman</span>
                        <span class="info-colon">:</span>
                        <span class="info-value"><?php echo $tanggal_indo; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Waktu Peminjaman</span>
                        <span class="info-colon">:</span>
                        <span class="info-value"><?php echo date('H:i', strtotime($booking['start_time'])) . ' - ' . date('H:i', strtotime($booking['end_time'])) . ' WIB'; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Keperluan</span>
                        <span class="info-colon">:</span>
                        <span class="info-value"><?php echo htmlspecialchars($booking['purpose']); ?></span>
                    </div>
                </div>
                
                <p class="closing-text">
                    Demikian surat izin peminjaman ruangan ini dibuat untuk dapat digunakan sebagaimana mestinya.
                </p>
            </div>
            
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-title">Menyetujui,</div>
                    <div class="signature-location">Bandung, <?php echo $today_indo; ?></div>
                    <div class="signature-role">Petugas Admin</div>
                    <div class="signature-name">
                        <?php echo htmlspecialchars($booking['admin_name'] ?? 'admin'); ?>
                    </div>
                </div>
                
                <div class="signature-box">
                    <div class="signature-title">Peminjam,</div>
                    <div class="signature-location">Bandung, <?php echo $today_indo; ?></div>
                    <div class="signature-role">Mahasiswa</div>
                    <div class="signature-name">
                        <?php echo htmlspecialchars($booking['nama']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function downloadPDF() {
            window.location.href = 'download_pdf.php?id=<?php echo $booking['id']; ?>';
        }
    </script>
</body>
</html>
