<?php
// hapus.php — Hapus foto dari folder & database
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'deepblue_db');
define('UPLOAD_DIR', __DIR__ . '/uploads/gallery/');

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method tidak valid.');
}

// Baca JSON body
$body      = json_decode(file_get_contents('php://input'), true);
$slotIndex = isset($body['slot_index']) ? (int)$body['slot_index'] : -1;
$deleteAll = isset($body['delete_all']) && $body['delete_all'] === true;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    respond(false, 'Koneksi database gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

if ($deleteAll) {
    // Hapus semua foto
    $result = $conn->query("SELECT filename FROM gallery_photos");
    while ($row = $result->fetch_assoc()) {
        $filePath = UPLOAD_DIR . $row['filename'];
        if (file_exists($filePath)) unlink($filePath);
    }
    $conn->query("DELETE FROM gallery_photos");
    $conn->close();
    respond(true, 'Semua foto berhasil dihapus.');
}

// Hapus satu slot
if ($slotIndex < 0 || $slotIndex > 5) {
    respond(false, 'Slot index tidak valid.');
}

$stmt = $conn->prepare("SELECT filename FROM gallery_photos WHERE slot_index = ?");
$stmt->bind_param('i', $slotIndex);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    respond(false, 'Foto tidak ditemukan di database.');
}

// Hapus file fisik
$filePath = UPLOAD_DIR . $row['filename'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Hapus dari database
$stmt = $conn->prepare("DELETE FROM gallery_photos WHERE slot_index = ?");
$stmt->bind_param('i', $slotIndex);
$stmt->execute();
$stmt->close();
$conn->close();

respond(true, 'Foto slot ' . $slotIndex . ' berhasil dihapus.');
