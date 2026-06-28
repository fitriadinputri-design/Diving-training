<?php
// upload.php — Terima upload foto, simpan ke folder & database
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ── Konfigurasi Database ────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Ganti jika user MySQL bukan root
define('DB_PASS', '');           // Ganti jika ada password MySQL
define('DB_NAME', 'deepblue_db');

// ── Konfigurasi Upload ──────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/uploads/gallery/');
define('UPLOAD_URL', 'uploads/gallery/');
define('MAX_SIZE',   10 * 1024 * 1024); // 10 MB
define('ALLOWED',    ['image/jpeg','image/png','image/webp','image/gif']);

// ── Fungsi response ─────────────────────────────────────────────
function respond($success, $message, $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// ── Buat folder uploads jika belum ada ─────────────────────────
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ── Validasi request ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method tidak valid.');
}

$slotIndex = isset($_POST['slot_index']) ? (int)$_POST['slot_index'] : -1;
$slotLabel = isset($_POST['slot_label']) ? trim($_POST['slot_label']) : '';

if ($slotIndex < 0 || $slotIndex > 5) {
    respond(false, 'Slot index tidak valid (harus 0-5).');
}
if (empty($slotLabel)) {
    respond(false, 'Label slot tidak boleh kosong.');
}
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    $errMsg = [
        UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
        UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar.',
        UPLOAD_ERR_PARTIAL    => 'Upload tidak lengkap.',
        UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diupload.',
    ];
    $code = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;
    respond(false, $errMsg[$code] ?? 'Gagal upload file.');
}

$file = $_FILES['foto'];

// ── Validasi tipe & ukuran ──────────────────────────────────────
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, ALLOWED)) {
    respond(false, 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.');
}
if ($file['size'] > MAX_SIZE) {
    respond(false, 'Ukuran file melebihi 10 MB.');
}

// ── Generate nama file unik ─────────────────────────────────────
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$safeExt  = strtolower($ext ?: 'jpg');
$newName  = 'slot' . $slotIndex . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
$destPath = UPLOAD_DIR . $newName;

// ── Koneksi database ────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    respond(false, 'Koneksi database gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// ── Cek apakah slot sudah ada foto lama, hapus jika ada ────────
$stmt = $conn->prepare("SELECT filename FROM gallery_photos WHERE slot_index = ?");
$stmt->bind_param('i', $slotIndex);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $oldFile = UPLOAD_DIR . $row['filename'];
    if (file_exists($oldFile)) {
        unlink($oldFile); // Hapus file lama
    }
}
$stmt->close();

// ── Pindahkan file upload ke folder ────────────────────────────
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    respond(false, 'Gagal menyimpan file ke server.');
}

// ── Simpan/update ke database ───────────────────────────────────
$stmt = $conn->prepare("
    INSERT INTO gallery_photos (slot_index, slot_label, filename, original_name, file_size)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      filename      = VALUES(filename),
      original_name = VALUES(original_name),
      file_size     = VALUES(file_size),
      uploaded_at   = CURRENT_TIMESTAMP
");
$originalName = basename($file['name']);
$fileSize     = $file['size'];
$stmt->bind_param('isssi', $slotIndex, $slotLabel, $newName, $originalName, $fileSize);

if (!$stmt->execute()) {
    // Rollback: hapus file yang sudah diupload
    unlink($destPath);
    respond(false, 'Gagal menyimpan ke database: ' . $stmt->error);
}
$stmt->close();
$conn->close();

respond(true, 'Foto berhasil diupload!', [
    'filename'  => $newName,
    'url'       => UPLOAD_URL . $newName,
    'slot_index' => $slotIndex,
]);
