<?php
session_start();
include 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];
$username = $_SESSION['username'];

// LOGIKA BARU: Saat user menekan tombol "Pilih Program Ini"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilih_program'])) {
    $program_baru = $_POST['pilih_program'];
    
    // Simpan pilihan program HANYA ke dalam Session, TIDAK ke database pendaftaran
    $_SESSION['program_aktif'] = $program_baru;
    
    // Refresh halaman agar data terbaru langsung muncul
    header("Location: dashboard.php");
    exit;
}

// Ambil data program terbaru dari Session peserta (bukan dari database pendaftaran)
$programPeserta = isset($_SESSION['program_aktif']) ? $_SESSION['program_aktif'] : ''; 
$hasProgram = !empty($programPeserta);

// ==========================================
// DATA DINAMIS UNTUK PROGRESS & MODUL
// ==========================================
$program_levels = [
    "Open Water Diver" => 1,
    "Advanced Open Water" => 2,
    "Rescue Diver" => 3,
    "Divemaster" => 4
];
$current_level = $hasProgram && isset($program_levels[$programPeserta]) ? $program_levels[$programPeserta] : 0;

// ... (kode sisa ke bawah seperti $daftar_program, $modul_pelatihan, dan HTML tetap sama) ... 
$hasProgram = !empty($programPeserta);

// ==========================================
// DATA DINAMIS UNTUK PROGRESS & MODUL
// ==========================================
$program_levels = [
    "Open Water Diver" => 1,
    "Advanced Open Water" => 2,
    "Rescue Diver" => 3,
    "Divemaster" => 4
];
$current_level = $hasProgram && isset($program_levels[$programPeserta]) ? $program_levels[$programPeserta] : 0;

$daftar_program = [
    "Open Water Diver" => ["emoji" => "🐠", "level" => "Level 1", "desc" => "Pelatihan dasar untuk pemula hingga kedalaman 18 meter. Cocok untuk Anda yang baru memulai.", "durasi" => "4 Hari"],
    "Advanced Open Water" => ["emoji" => "🦈", "level" => "Level 2", "desc" => "Tingkatkan skill dengan 5 penyelaman petualangan termasuk deep dive dan navigasi bawah air.", "durasi" => "3 Hari"],
    "Rescue Diver" => ["emoji" => "🚑", "level" => "Level 3", "desc" => "Kuasai teknik penyelamatan dan penanganan keadaan darurat kritis di perairan terbuka.", "durasi" => "5 Hari"],
    "Divemaster" => ["emoji" => "👨‍🏫", "level" => "Profesional", "desc" => "Mulai karir profesional sebagai Divemaster. Pimpin penyelaman kelompok dan bantu instruktur.", "durasi" => "14 Hari"]
];

$modul_pelatihan = [
    "Open Water Diver" => [
        ["title" => "Modul 1 — Teori Dasar Diving", "desc" => "Fisika air, hukum Boyle, peralatan dasar", "status" => "done"],
        ["title" => "Modul 2 — Latihan Kolam Renang (Pool)", "desc" => "Equalizing, buoyancy control, buddy check", "status" => "done"],
        ["title" => "Modul 3 — Open Water Dive #1 & #2", "desc" => "Penyelaman laut terbuka pertama", "status" => "active"],
        ["title" => "Modul 4 — Ujian Akhir & Sertifikasi", "desc" => "Evaluasi keseluruhan, pengambilan sertifikat", "status" => "locked"],
    ],
    "Advanced Open Water" => [
        ["title" => "Modul 1 — Deep Dive", "desc" => "Penyelaman hingga kedalaman 30 meter", "status" => "done"],
        ["title" => "Modul 2 — Underwater Navigation", "desc" => "Navigasi presisi menggunakan kompas", "status" => "active"],
        ["title" => "Modul 3 — Peak Performance Buoyancy", "desc" => "Kontrol apung tingkat lanjut", "status" => "locked"],
        ["title" => "Modul 4 — Night Dive & Wreck Dive", "desc" => "Menyelami bangkai kapal dan selam malam", "status" => "locked"],
    ],
    "Rescue Diver" => [
        ["title" => "Modul 1 — Self Rescue & Stress", "desc" => "Mengenali stres pada diri sendiri dan penyelam lain", "status" => "active"],
        ["title" => "Modul 2 — Emergency Management", "desc" => "Prosedur oksigen & manajemen P3K", "status" => "locked"],
        ["title" => "Modul 3 — Rescuing Panicked Diver", "desc" => "Penanganan kepanikan di permukaan air", "status" => "locked"],
        ["title" => "Modul 4 — Unresponsive Diver", "desc" => "Penyelamatan penyelam pingsan di bawah air", "status" => "locked"],
    ],
    "Divemaster" => [
        ["title" => "Modul 1 — The Role of Divemaster", "desc" => "Kepemimpinan & manajemen risiko penyelaman", "status" => "active"],
        ["title" => "Modul 2 — Dive Theory", "desc" => "Fisika, fisiologi, dan lingkungan laut mendalam", "status" => "locked"],
        ["title" => "Modul 3 — Assisting Students", "desc" => "Latihan praktek membantu instruktur OWD", "status" => "locked"],
    ]
];

// ==========================================
// SECTION AWAL: buka pelatihan jika dari login
// ==========================================
$initial_section = 'overview';
if (isset($_GET['section']) && in_array($_GET['section'], ['overview','pelatihan','quest','progress'])) {
    $initial_section = $_GET['section'];
}

// ==========================================
// QUERY PESERTA PROGRAM AKTIF (untuk tabel di section pelatihan)
// ==========================================
$peserta_program = [];
$total_peserta   = 0;
if ($hasProgram) {
    $stmt_p = $conn->prepare("SELECT nama, email, telepon, pengalaman, jadwal, created_at FROM pendaftaran WHERE program = ? ORDER BY id DESC");
    $stmt_p->bind_param("s", $programPeserta);
    $stmt_p->execute();
    $res_p = $stmt_p->get_result();
    while ($row_p = $res_p->fetch_assoc()) {
        $peserta_program[] = $row_p;
    }
    $total_peserta = count($peserta_program);
    $stmt_p->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DeepBlue Diving Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --deep: #03045e;
            --dark: #023e8a;
            --mid: #0077b6;
            --bright: #00b4d8;
            --light: #90e0ef;
            --pale: #caf0f8;
            --accent: #f7c948;
            --white: #ffffff;
            --text: #1a1a2e;
            --muted: #555577;
            --sidebar-w: 260px;
            --header-h: 70px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #f0f6ff;
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg, var(--deep) 0%, #012a6b 100%);
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(3,4,94,0.2);
        }

        .sidebar-logo {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo .brand {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 28px;
            color: white;
            letter-spacing: 1px;
        }

        .sidebar-logo .brand span { color: var(--accent); }

        .sidebar-logo .tagline {
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 4px;
        }

        .sidebar-user {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .user-avatar {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--bright), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: var(--deep);
            flex-shrink: 0;
        }

        .user-info .name {
            font-weight: 600;
            color: white;
            font-size: 14px;
        }

        .user-info .level-badge {
            font-size: 11px;
            color: var(--accent);
            font-weight: 500;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.35);
            padding: 12px 12px 6px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
            cursor: pointer;
        }

        .nav-item:hover {
            background: rgba(0,180,216,0.15);
            color: white;
        }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(0,180,216,0.25), rgba(0,180,216,0.05));
            color: white;
            border-left: 3px solid var(--bright);
        }

        .nav-item .icon { font-size: 18px; flex-shrink: 0; }
        .nav-item .badge {
            margin-left: auto;
            background: var(--accent);
            color: var(--deep);
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 10px;
            color: rgba(255,100,100,0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: rgba(220,53,69,0.15);
            color: #ff6b6b;
        }

        /* ===== MAIN CONTENT ===== */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            height: var(--header-h);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(3,4,94,0.06);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--deep);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .xp-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(90deg, var(--deep), var(--mid));
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .xp-pill .star { color: var(--accent); font-size: 15px; }

        /* ===== PAGE SECTIONS ===== */
        .page-content {
            padding: 32px;
            flex: 1;
        }

        .section { display: none; }
        .section.active { display: block; }

        /* ===== OVERVIEW ===== */
        .welcome-banner {
            background: linear-gradient(135deg, var(--deep) 0%, #0057a8 60%, #0096c7 100%);
            border-radius: 20px;
            padding: 32px 36px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '🌊';
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 100px;
            opacity: 0.12;
        }

        .welcome-banner h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .welcome-banner h2 span { color: var(--accent); }

        .welcome-banner p {
            font-size: 15px;
            opacity: 0.8;
            max-width: 480px;
        }

        .progress-overview {
            display: flex;
            gap: 8px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .prog-chip {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
        }

        .prog-chip.done { background: rgba(247,201,72,0.25); border-color: var(--accent); color: var(--accent); }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            box-shadow: 0 2px 12px rgba(3,4,94,0.06);
            transition: transform 0.2s;
        }

        .stat-card:hover { transform: translateY(-2px); }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-icon.blue { background: #e8f4ff; }
        .stat-icon.yellow { background: #fff9e6; }
        .stat-icon.green { background: #e6fff5; }
        .stat-icon.purple { background: #f0eaff; }

        .stat-number { font-size: 28px; font-weight: 800; color: var(--deep); line-height: 1; }
        .stat-label { font-size: 13px; color: var(--muted); }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(3,4,94,0.06);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-header h3 { font-size: 16px; font-weight: 700; color: var(--deep); }
        .card-header .see-all { font-size: 13px; color: var(--mid); text-decoration: none; font-weight: 500; }

        .mini-program {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f4f8;
        }
        .mini-program:last-child { border-bottom: none; }

        .mini-icon {
            width: 40px; height: 40px;
            background: #e8f4ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .mini-info { flex: 1; }
        .mini-info .prog-name { font-size: 14px; font-weight: 600; color: var(--deep); }
        .mini-info .prog-meta { font-size: 12px; color: var(--muted); margin-top: 2px; }

        .mini-bar-wrap { margin-top: 6px; }
        .mini-bar { height: 5px; background: #e8f0fe; border-radius: 3px; overflow: hidden; }
        .mini-bar-fill { height: 100%; border-radius: 3px; transition: width 1s ease; }
        .mini-pct { font-size: 11px; color: var(--muted); margin-top: 3px; }

        .mini-quest {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f4f8;
        }
        .mini-quest:last-child { border-bottom: none; }

        .quest-status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .quest-status-dot.done { background: #22c55e; }
        .quest-status-dot.active { background: var(--accent); }

        .mini-quest .quest-name { font-size: 14px; font-weight: 500; color: var(--text); flex: 1; }
        .mini-quest .quest-xp { font-size: 12px; font-weight: 700; color: var(--mid); }

        /* ===== PELATIHAN SECTION ===== */
        .section-title { font-size: 22px; font-weight: 800; color: var(--deep); margin-bottom: 6px; }
        .section-desc { font-size: 14px; color: var(--muted); margin-bottom: 24px; }

        .program-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .prog-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(3,4,94,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
        }

        .prog-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(3,4,94,0.12); }
        .prog-card-header {
            background: linear-gradient(135deg, var(--deep), var(--mid));
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        .prog-card.completed .prog-card-header { background: linear-gradient(135deg, #166534, #15803d); }

        .prog-emoji { font-size: 40px; }
        .prog-level-tag { background: rgba(255,255,255,0.2); color: white; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; }
        .prog-status-badge { position: absolute; top: 14px; right: 14px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .prog-status-badge.completed { background: #dcfce7; color: #166534; }

        .prog-card-body { padding: 24px; display: flex; flex-direction: column; flex: 1; }
        .prog-card-body h3 { font-size: 18px; font-weight: 700; color: var(--deep); margin-bottom: 10px; }
        .prog-card-body p { font-size: 14px; color: var(--muted); line-height: 1.6; margin-bottom: 24px; flex: 1; }
        .prog-card-body form { width: 100%; margin-top: auto; }

        .btn-prog {
            display: block; text-align: center; padding: 11px; border-radius: 10px; font-size: 14px;
            font-weight: 700; border: none; cursor: pointer; width: 100%; transition: all 0.2s;
        }
        .btn-prog.primary { background: linear-gradient(90deg, var(--deep), var(--mid)); color: white; }
        .btn-prog.primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-prog.done { background: #dcfce7; color: #166534; cursor: default; }

        .modules-section { margin-top: 32px; }
        .modules-section .section-title { font-size: 18px; }
        .module-list { display: flex; flex-direction: column; gap: 12px; }

        .module-item {
            background: white; border-radius: 14px; padding: 18px 22px; display: flex;
            align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(3,4,94,0.05); transition: all 0.2s;
        }
        .module-item:hover { box-shadow: 0 6px 20px rgba(3,4,94,0.1); transform: translateX(3px); }

        .module-num {
            width: 36px; height: 36px; background: linear-gradient(135deg, var(--deep), var(--mid));
            color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; flex-shrink: 0;
        }
        .module-num.done { background: linear-gradient(135deg, #166534, #22c55e); }
        .module-num.locked { background: #e2e8f0; color: #94a3b8; }

        .module-info { flex: 1; }
        .module-info .mod-title { font-size: 15px; font-weight: 600; color: var(--deep); }
        .module-info .mod-sub { font-size: 13px; color: var(--muted); margin-top: 2px; }

        .module-right { display: flex; align-items: center; gap: 10px; }
        .mod-duration { font-size: 12px; color: var(--muted); background: #f1f5f9; padding: 4px 10px; border-radius: 20px; }
        .mod-check { font-size: 20px; }

        /* ===== QUEST ===== */
        .quest-tabs {
            display: flex; gap: 8px; margin-bottom: 24px; background: white; padding: 6px;
            border-radius: 12px; width: fit-content; box-shadow: 0 2px 8px rgba(3,4,94,0.06);
        }
        .quest-tab { padding: 9px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: none; color: var(--muted); }
        .quest-tab.active { background: linear-gradient(90deg, var(--deep), var(--mid)); color: white; }
        .quest-tab-content { display: none; }
        .quest-tab-content.active { display: block; }
        .quest-cards { display: flex; flex-direction: column; gap: 14px; }
        .quest-card {
            background: white; border-radius: 16px; padding: 20px 22px; display: flex; align-items: center;
            gap: 18px; box-shadow: 0 2px 10px rgba(3,4,94,0.06); border-left: 4px solid transparent;
        }
        .quest-card.active-quest { border-left-color: var(--accent); }
        .quest-card.done-quest { border-left-color: #22c55e; }
        .quest-emoji { font-size: 32px; width: 54px; height: 54px; background: #f0f6ff; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
        .quest-main { flex: 1; }
        .quest-main .q-title { font-size: 15px; font-weight: 700; color: var(--deep); }
        .quest-main .q-desc { font-size: 13px; color: var(--muted); }
        .quest-right { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .xp-badge { background: linear-gradient(135deg, var(--deep), var(--mid)); color: white; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }

        /* ===== PROGRESS ===== */
        .progress-hero {
            background: linear-gradient(135deg, var(--deep), var(--mid));
            border-radius: 20px; padding: 28px 32px; color: white; margin-bottom: 24px; display: flex; align-items: center; gap: 32px;
        }
        .level-circle {
            width: 90px; height: 90px; border-radius: 50%; border: 4px solid var(--accent);
            display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .level-circle .lv-label { font-size: 10px; opacity: 0.7; }
        .level-circle .lv-num { font-size: 36px; color: var(--accent); font-family: 'Bebas Neue', sans-serif; line-height: 1; }
        .level-info h3 { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .xp-bar-wrap { max-width: 400px; }
        .xp-bar-label { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px; }
        .xp-bar { height: 10px; background: rgba(255,255,255,0.2); border-radius: 5px; }
        .xp-bar-fill { height: 100%; background: linear-gradient(90deg, var(--accent), #ffb703); border-radius: 5px; }

        @media (max-width: 860px) {
            :root { --sidebar-w: 0px; }
            .sidebar { transform: translateX(-260px); width: 260px; transition: transform 0.3s; }
            .main { margin-left: 0; }
            .two-col, .program-cards { grid-template-columns: 1fr; }
            .progress-hero { flex-direction: column; align-items: flex-start; }
        }

        /* ===== TABEL PESERTA DI SECTION PELATIHAN ===== */
        .peserta-section {
            margin-top: 32px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(3,4,94,0.06);
        }

        .peserta-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 26px;
            border-bottom: 1px solid #f1f5f9;
        }

        .peserta-header h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--deep);
        }

        .badge-count {
            background: linear-gradient(90deg, var(--deep), var(--mid));
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .peserta-table-wrap { overflow-x: auto; }

        .peserta-table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        .peserta-table thead {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .peserta-table th {
            padding: 14px 18px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .peserta-table td {
            padding: 14px 18px;
            font-size: 14px;
            color: var(--text);
            border-bottom: 1px solid #f1f5f9;
        }

        .peserta-table tbody tr:hover { background: #f8fafc; }

        .peserta-table .p-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--bright), var(--mid));
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            margin-right: 10px;
            vertical-align: middle;
        }

        .peserta-table .p-name {
            font-weight: 600;
            vertical-align: middle;
        }

        .badge-jadwal {
            background: #e8f4ff;
            color: var(--dark);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pengalaman {
            background: #fff9e6;
            color: #b45309;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-peserta {
            text-align: center;
            padding: 48px 20px;
            color: var(--muted);
        }

        .empty-peserta .ep-icon { font-size: 40px; margin-bottom: 12px; }
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="brand">🤿 Deep<span>Blue</span></div>
        <div class="tagline">Diving Academy</div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
        <div class="user-info">
            <div class="name"><?php echo htmlspecialchars($username); ?></div>
            <div class="level-badge">⭐ <?php echo $hasProgram ? htmlspecialchars($programPeserta) : 'Belum Ada Program'; ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>
        <a class="nav-item" href="profile.php"><span class="icon">👤</span> Profil Saya</a>
        <a class="nav-item <?= $initial_section === 'overview' ? 'active' : '' ?>" onclick="showSection('overview', this)"><span class="icon">🏠</span> Ringkasan</a>
        <a class="nav-item <?= $initial_section === 'pelatihan' ? 'active' : '' ?>" onclick="showSection('pelatihan', this)"><span class="icon">📚</span> Pelatihan</a>
        <a class="nav-item <?= $initial_section === 'quest' ? 'active' : '' ?>" onclick="showSection('quest', this)"><span class="icon">🎯</span> Quest & Misi</a>
        <a class="nav-item <?= $initial_section === 'progress' ? 'active' : '' ?>" onclick="showSection('progress', this)"><span class="icon">📈</span> Progress & Pencapaian</a>

        <div class="nav-section-label" style="margin-top:12px">Admin</div>
        <a class="nav-item" href="daftar_peserta.php"><span class="icon">👥</span> Daftar Peserta</a>
        <a class="nav-item" href="upload.php"><span class="icon">🖼️</span> Upload Foto</a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout"><span>🚪</span> Keluar</a>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div class="topbar-title" id="topbarTitle"><?php
            $section_titles = [
                'overview'  => '🏠 Ringkasan',
                'pelatihan' => '📚 Pelatihan',
                'quest'     => '🎯 Quest & Misi',
                'progress'  => '📈 Progress & Pencapaian',
            ];
            echo $section_titles[$initial_section] ?? '🏠 Ringkasan';
        ?></div>
        <div class="topbar-right">
            <div class="xp-pill"><span class="star">⭐</span> 1.250 XP</div>
        </div>
    </header>

    <div class="page-content">

        <div class="section <?= $initial_section === 'overview' ? 'active' : '' ?>" id="sec-overview">
            <div class="welcome-banner">
                <div>
                    <h2>
                        <?php if ($hasProgram): ?>
                            Selamat Datang di <span><?= htmlspecialchars($programPeserta) ?></span>
                        <?php else: ?>
                            Selamat Datang, <span><?= htmlspecialchars($username) ?>!</span>
                        <?php endif; ?>
                    </h2>
                    <?php if ($hasProgram): ?>
                        <p>Lanjutkan perjalanan bawah lautmu di program <?= htmlspecialchars($programPeserta) ?>. Berikut adalah progres terkinimu.</p>
                        <div class="progress-overview">
                            <div class="prog-chip done">✅ Tahap Awal</div>
                            <div class="prog-chip">🔄 Tahap Lanjutan</div>
                        </div>
                    <?php else: ?>
                        <p>Kamu belum memiliki program pelatihan yang aktif. Silakan pilih program di menu Pelatihan untuk memulai petualanganmu!</p><br>
                        <button class="btn-prog primary" style="width:200px" onclick="showSection('pelatihan', document.querySelectorAll('.nav-item')[2])">Pilih Program Sekarang</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-icon blue">📅</div><div class="stat-number">12</div><div class="stat-label">Hari Latihan</div></div>
                <div class="stat-card"><div class="stat-icon yellow">⭐</div><div class="stat-number">1.250</div><div class="stat-label">Total XP</div></div>
                <div class="stat-card"><div class="stat-icon green">🎯</div><div class="stat-number">5</div><div class="stat-label">Quest Selesai</div></div>
                <div class="stat-card"><div class="stat-icon purple">🤿</div><div class="stat-number">3</div><div class="stat-label">Total Dive</div></div>
            </div>

            <div class="two-col">
                <div class="card">
                    <div class="card-header">
                        <h3>Progress Keseluruhan</h3>
                        <a onclick="showSection('pelatihan', document.querySelectorAll('.nav-item')[2])" class="see-all" style="cursor:pointer">Lihat semua →</a>
                    </div>
                    <?php 
                    foreach ($daftar_program as $nama_prog => $detail): 
                        $lvl = $program_levels[$nama_prog];
                        if ($lvl < $current_level) {
                            $pct = 100;
                            $status_text = "Selesai (100%)";
                            $bar_bg = "linear-gradient(90deg, #16a34a, #22c55e)";
                        } elseif ($lvl == $current_level) {
                            $pct = 45; // Simulasi persen
                            $status_text = "Sedang Berjalan ($pct%)";
                            $bar_bg = "linear-gradient(90deg, var(--mid), var(--bright))";
                        } else {
                            $pct = 0;
                            $status_text = "Terkunci — Selesaikan level sebelumnya";
                            $bar_bg = "transparent";
                        }
                    ?>
                    <div class="mini-program">
                        <div class="mini-icon"><?= $detail['emoji'] ?></div>
                        <div class="mini-info">
                            <div class="prog-name"><?= $nama_prog ?></div>
                            <div class="prog-meta"><?= $detail['level'] ?> · <?= $detail['durasi'] ?></div>
                            <div class="mini-bar-wrap">
                                <div class="mini-bar"><div class="mini-bar-fill" style="width:<?= $pct ?>%; background:<?= $bar_bg ?>;"></div></div>
                                <div class="mini-pct"><?= $status_text ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>🎯 Quest Terakhir</h3>
                        <a onclick="showSection('quest', document.querySelectorAll('.nav-item')[3])" class="see-all" style="cursor:pointer">Lihat semua →</a>
                    </div>
                    <div class="mini-quest"><div class="quest-status-dot active"></div><div class="quest-name">📖 Baca Modul Teori</div><div class="quest-xp">+100 XP</div></div>
                    <div class="mini-quest"><div class="quest-status-dot active"></div><div class="quest-name">🌊 Latihan Laut Terbuka</div><div class="quest-xp">+250 XP</div></div>
                    <div class="mini-quest"><div class="quest-status-dot done"></div><div class="quest-name">✅ Kuis Keselamatan</div><div class="quest-xp">+150 XP</div></div>
                </div>
            </div>
        </div>

        <div class="section <?= $initial_section === 'pelatihan' ? 'active' : '' ?>" id="sec-pelatihan">
            <div class="section-title">📚 Program Pelatihan</div>
            <div class="section-desc">Pilih dan ikuti kurikulum diving bersertifikat.</div>

            <div class="program-cards">
                <?php foreach ($daftar_program as $nama_prog => $detail) {
                    $isActive = ($programPeserta === $nama_prog);
                    $cardClass = $isActive ? "prog-card completed" : "prog-card";
                ?>
                <div class="<?= $cardClass ?>">
                    <div class="prog-card-header">
                        <div class="prog-emoji"><?= $detail['emoji'] ?></div>
                        <div class="prog-level-tag"><?= $detail['level'] ?></div>
                        <?php if ($isActive): ?><div class="prog-status-badge completed">Sedang Aktif</div><?php endif; ?>
                    </div>
                    <div class="prog-card-body">
                        <h3><?= $nama_prog ?></h3>
                        <p><?= $detail['desc'] ?></p>
                        <?php if ($isActive): ?>
                            <button class="btn-prog done" disabled>Program Saat Ini</button>
                        <?php else: ?>
                            <form method="POST" action="dashboard.php">
                                <input type="hidden" name="pilih_program" value="<?= $nama_prog ?>">
                                <button type="submit" class="btn-prog primary">Pilih Program Ini</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php } ?>
            </div>

            <?php if ($hasProgram && isset($modul_pelatihan[$programPeserta])): ?>
                <div class="modules-section">
                    <div class="section-title">📋 Modul <?= htmlspecialchars($programPeserta) ?></div>
                    <div class="section-desc" style="margin-bottom:16px">Selesaikan semua modul untuk mendapatkan sertifikasi <?= htmlspecialchars($programPeserta) ?>.</div>

                    <div class="module-list">
                        <?php 
                        $no = 1;
                        foreach ($modul_pelatihan[$programPeserta] as $modul) {
                            if ($modul['status'] == 'done') {
                                $num_class = 'module-num done'; $num_text = '✓'; $style = ''; $icon = '✅';
                            } elseif ($modul['status'] == 'active') {
                                $num_class = 'module-num'; $num_text = $no; $style = 'style="border:2px solid var(--accent); border-radius:14px;"'; $icon = '🔄';
                            } else {
                                $num_class = 'module-num locked'; $num_text = $no; $style = 'style="opacity:0.6"'; $icon = '🔒';
                            }
                        ?>
                        <div class="module-item" <?= $style ?>>
                            <div class="<?= $num_class ?>" <?= $modul['status'] == 'active' ? 'style="background:linear-gradient(135deg,var(--accent),#ffb703);color:var(--deep)"' : '' ?>><?= $num_text ?></div>
                            <div class="module-info">
                                <div class="mod-title"><?= $modul['title'] ?></div>
                                <div class="mod-sub"><?= $modul['desc'] ?></div>
                            </div>
                            <div class="module-right">
                                <span class="mod-duration">📅 Sesi <?= $no ?></span>
                                <span class="mod-check"><?= $icon ?></span>
                            </div>
                        </div>
                        <?php $no++; } ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="modules-section">
                    <div style="padding: 20px; background: white; border-radius: 12px; text-align: center; color: var(--muted);">
                        Pilih program pelatihan di atas terlebih dahulu untuk melihat daftar modul.
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== TABEL PESERTA PROGRAM AKTIF ===== -->
            <?php if ($hasProgram): ?>
            <div class="peserta-section">
                <div class="peserta-header">
                    <h3>👥 Daftar Peserta — <?= htmlspecialchars($programPeserta) ?></h3>
                    <span class="badge-count"><?= $total_peserta ?> Peserta Terdaftar</span>
                </div>
                <div class="peserta-table-wrap">
                    <table class="peserta-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Peserta</th>
                                <th>Kontak</th>
                                <th>Pengalaman</th>
                                <th>Jadwal</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($total_peserta > 0): ?>
                            <?php $no = 1; foreach ($peserta_program as $p): ?>
                            <tr>
                                <td style="color:var(--muted); font-weight:600;"><?= $no++ ?></td>
                                <td>
                                    <span class="p-avatar"><?= strtoupper(substr($p['nama'], 0, 1)) ?></span>
                                    <span class="p-name"><?= htmlspecialchars($p['nama']) ?></span>
                                </td>
                                <td>
                                    <div style="font-size:13px;font-weight:600;color:var(--deep);"><?= htmlspecialchars($p['telepon'] ?? '-') ?></div>
                                    <div style="font-size:12px;color:var(--muted);"><?= htmlspecialchars($p['email']) ?></div>
                                </td>
                                <td><span class="badge-pengalaman"><?= htmlspecialchars($p['pengalaman'] ?? '-') ?></span></td>
                                <td><span class="badge-jadwal">📅 <?= htmlspecialchars($p['jadwal'] ?? '-') ?></span></td>
                                <td style="font-size:13px;color:var(--muted);"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-peserta">
                                        <div class="ep-icon">📂</div>
                                        <strong>Belum ada peserta terdaftar</strong>
                                        <p style="margin-top:6px;font-size:13px;">Belum ada yang mendaftar untuk program <strong><?= htmlspecialchars($programPeserta) ?></strong>.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="section <?= $initial_section === 'quest' ? 'active' : '' ?>" id="sec-quest">
            <div class="section-title">🎯 Quest & Misi</div>
            <div class="quest-tabs">
                <button class="quest-tab active" onclick="switchQuestTab('aktif', this)">🔄 Aktif</button>
            </div>
            <div class="quest-tab-content active" id="qtab-aktif">
                <div class="quest-cards">
                    <div class="quest-card active-quest">
                        <div class="quest-emoji" style="background:#fff9e6">📖</div>
                        <div class="quest-main">
                            <div class="q-title">Pelajari Materi Keselamatan</div>
                            <div class="q-desc">Tingkatkan skor pengetahuan keselamatan dasar sebelum menyelam.</div>
                        </div>
                        <div class="quest-right"><span class="xp-badge">+100 XP</span><button class="btn-prog primary" style="padding:6px 12px; font-size:12px;">Kerjakan</button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section <?= $initial_section === 'progress' ? 'active' : '' ?>" id="sec-progress">
            <div class="section-title">📈 Progress & Pencapaian</div>
            <div class="section-desc">Pantau perkembangan seluruh perjalanan diving-mu.</div>

            <div class="progress-hero">
                <div class="level-circle">
                    <span class="lv-label">LEVEL</span>
                    <span class="lv-num"><?= $current_level > 0 ? $current_level : '-' ?></span>
                </div>
                <div class="level-info">
                    <h3><?= $hasProgram ? htmlspecialchars($programPeserta) . " Trainee" : "Belum Ada Program Aktif" ?></h3>
                    <p>Terus berprogres dan dapatkan sertifikasi resmimu!</p>
                    
                    <?php if ($hasProgram): ?>
                    <div class="xp-bar-wrap">
                        <div class="xp-bar-label">
                            <span>Progress Level <?= $current_level ?></span>
                            <span>45% Selesai</span>
                        </div>
                        <div class="xp-bar"><div class="xp-bar-fill" style="width:45%"></div></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function showSection(name, el) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById('sec-' + name).classList.add('active');
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    if (el) el.classList.add('active');
    const titles = { overview: '🏠 Ringkasan', pelatihan: '📚 Pelatihan', quest: '🎯 Quest & Misi', progress: '📈 Progress & Pencapaian' };
    document.getElementById('topbarTitle').textContent = titles[name] || '';
}

function switchQuestTab(tab, el) {
    document.querySelectorAll('.quest-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.quest-tab-content').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('qtab-' + tab).classList.add('active');
}
</script>

</body>
</html>