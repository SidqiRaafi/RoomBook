<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'config/database.php';
require_once 'fpdf/fpdf.php';

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

class PDF extends FPDF {
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt), $border, $ln, $align, $fill, $link);
    }
    
    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) {
        parent::MultiCell($w, $h, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt), $border, $align, $fill);
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(20);

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(1.0);
$pdf->Rect(15, 15, 180, 260, 'D'); // Correct A4 border

$pdf->SetY(25);
$pdf->SetFont('Times', 'B', 18);
$pdf->Cell(0, 10, 'SURAT IZIN PEMINJAMAN RUANGAN', 0, 1, 'C');

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line(30, $pdf->GetY() + 3, 180, $pdf->GetY() + 3);

$pdf->SetFont('Times', '', 12);
$pdf->SetY($pdf->GetY() + 6);
$pdf->Cell(0, 6, 'Nomor: ' . $booking['permit_number'], 0, 1, 'C');
$pdf->SetY($pdf->GetY() + 5);

$pdf->SetFont('Times', '', 12);
$pdf->SetX(35);
$pdf->MultiCell(140, 6, 'Yang bertanda tangan di bawah ini menerangkan bahwa:', 0, 'L');
$pdf->SetY($pdf->GetY() + 3);

$pdf->SetFont('Times', '', 11);
$line_height = 7;
$x_label = 35;
$x_colon = 95;
$x_value = 103;

$data = [
    ['NIM', $booking['nim']],
    ['Nama Peminjam', $booking['nama']],
    ['Ruangan', $booking['room_name']],
    ['Tanggal Peminjaman', $tanggal_indo],
    ['Waktu Peminjaman', date('H:i', strtotime($booking['start_time'])) . ' - ' . date('H:i', strtotime($booking['end_time'])) . ' WIB'],
    ['Keperluan', $booking['purpose']]
];

foreach ($data as $row) {
    $pdf->SetXY($x_label, $pdf->GetY());
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(60, $line_height, $row[0], 0, 0, 'L');
    $pdf->SetX($x_colon);
    $pdf->Cell(3, $line_height, ':', 0, 0, 'C');
    $pdf->SetX($x_value);
    $pdf->SetFont('Times', '', 11);
    $pdf->MultiCell(75, $line_height, $row[1], 0, 'L');
}

$pdf->SetY($pdf->GetY() + 5);

$pdf->SetX(35);
$pdf->SetFont('Times', '', 11);
$pdf->MultiCell(140, 6, 'Demikian surat izin peminjaman ruangan ini dibuat untuk dapat digunakan sebagaimana mestinya.', 0, 'J');

$pdf->SetY($pdf->GetY() + 12);

$pdf->SetY($pdf->GetY() + 15);
$y_sig = $pdf->GetY();
$left_x = 40;
$right_x = 125;

$pdf->SetXY($left_x, $y_sig);
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(65, 6, 'Menyetujui,', 0, 1, 'L');

$pdf->SetXY($left_x, $y_sig + 6);
$pdf->SetFont('Times', 'I', 10);
$pdf->Cell(65, 6, 'Bandung, ' . $today_indo, 0, 1, 'L');

$pdf->SetY($y_sig + 37);

$pdf->SetXY($left_x, $pdf->GetY());
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(65, 6, 'Petugas Admin', 0, 1, 'L');

$pdf->SetY($pdf->GetY() + 5);
$line_y = $pdf->GetY();
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.4);
$pdf->Line($left_x, $line_y, $left_x + 65, $line_y);

$pdf->SetXY($left_x, $line_y + 3);
$pdf->SetFont('Times', '', 11);
$pdf->Cell(65, 6, $booking['admin_name'] ?? 'admin', 0, 1, 'L');

$pdf->SetXY($right_x, $y_sig);
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(65, 6, 'Peminjam,', 0, 1, 'L');

$pdf->SetXY($right_x, $y_sig + 6);
$pdf->SetFont('Times', 'I', 10);
$pdf->Cell(65, 6, 'Bandung, ' . $today_indo, 0, 1, 'L');

$pdf->SetXY($right_x, $y_sig + 37);

$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(65, 6, 'Mahasiswa', 0, 1, 'L');

$pdf->SetY($pdf->GetY() + 5);
$line_y = $pdf->GetY();
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.4);
$pdf->Line($right_x, $line_y, $right_x + 65, $line_y);

$pdf->SetXY($right_x, $line_y + 3);
$pdf->SetFont('Times', '', 11);
$pdf->Cell(65, 6, $booking['nama'], 0, 1, 'L');

$filename = $booking['permit_number'] . '.pdf';
$pdf->Output('D', $filename);
?>
