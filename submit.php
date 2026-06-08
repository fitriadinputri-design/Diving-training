<?php

session_start();

include 'config.php';

header('Content-Type: application/json; charset=UTF-8');

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ── Sanitasi Input ───────────────────────────────────────────
function sanitize(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$nama       = sanitize($_POST['nama']       ?? '');
$email      = sanitize($_POST['email']      ?? '');
$telepon    = sanitize($_POST['telepon']    ?? '');
$program    = sanitize($_POST['program']    ?? '');
$pengalaman = sanitize($_POST['pengalaman'] ?? '');
$jadwal     = sanitize($_POST['jadwal']     ?? '');
$pesan      = sanitize($_POST['pesan']      ?? '');
$ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// ── Validasi ─────────────────────────────────────────────────
$errors = [];
if (strlen($nama) < 3)   $errors[] = 'Nama minimal 3 karakter.';
if (empty($email))        $errors[] = 'Email tidak boleh kosong.';
if (empty($telepon))      $errors[] = 'Nomor telepon tidak boleh kosong.';
if (empty($program))      $errors[] = 'Program harus dipilih.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// ── Simpan ke Database ───────────────────────────────────────
$stmt = $conn->prepare(
    "INSERT INTO pendaftaran
     (nama, email, telepon, program, pengalaman, jadwal, pesan, ip_address)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $conn->error]);
    exit;
}

$jadwalVal = !empty($jadwal) ? $jadwal : null;

$stmt->bind_param(
    'ssssssss',
    $nama, $email, $telepon, $program,
    $pengalaman, $jadwalVal, $pesan, $ip
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Pendaftaran berhasil! Kami akan menghubungi Anda dalam 24 jam.'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
