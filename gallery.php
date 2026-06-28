<?php
// gallery.php — Halaman galeri dengan data dari MySQL
// ── Konfigurasi Database ────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'deepblue_db');
define('UPLOAD_URL', 'uploads/gallery/');

// ── Slot default (urutan & label tetap) ────────────────────────
$slots = [
    0 => ['label' => 'Raja Ampat, Papua',   'class' => 'g-large'],
    1 => ['label' => 'Komodo, NTT',          'class' => ''],
    2 => ['label' => 'Bunaken, Sulawesi',    'class' => ''],
    3 => ['label' => 'Tulamben, Bali',       'class' => ''],
    4 => ['label' => 'Wakatobi, Sulawesi',   'class' => 'g-tall'],
    5 => ['label' => 'Alor, NTT',            'class' => ''],
];

// ── Ambil data foto dari database ──────────────────────────────
$photos = [];
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$dbOk = !$conn->connect_error;

if ($dbOk) {
    $conn->set_charset('utf8mb4');
    $result = $conn->query("SELECT slot_index, filename, slot_label FROM gallery_photos");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $photos[(int)$row['slot_index']] = $row;
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Galeri - DeepBlue Diving Academy</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="style.css" rel="stylesheet"/>
</head>
<body>

<nav id="navbar">
<div class="nav-inner">
  <div class="logo">🤿 DeepBlue</div>
  <ul class="nav-links">
    <li><a href="index.html">Beranda</a></li>
    <li><a href="programs.html">Program</a></li>
    <li><a href="instructors.html">Instruktur</a></li>
    <li><a href="gallery.php">Galeri</a></li>
    <li><a href="contact.html">Kontak</a></li>
  </ul>
  <div class="nav-buttons">
    <a href="login.html" class="btn-login">Login</a>
    <a href="daftar.html" class="btn-nav">Daftar Sekarang</a>
  </div>
  <button class="hamburger" id="hamburger">☰</button>
</div>
<ul class="mobile-menu" id="mobileMenu">
  <li><a href="index.html">Beranda</a></li>
  <li><a href="programs.html">Program</a></li>
  <li><a href="instructors.html">Instruktur</a></li>
  <li><a href="gallery.php">Galeri</a></li>
  <li><a href="contact.html">Kontak</a></li>
</ul>
</nav>

<section class="gallery" id="gallery">
<div class="section-header">
  <span class="section-tag">Galeri</span>
  <h2>Momen Bawah <span class="accent">Laut</span></h2>
</div>

<?php if (!$dbOk): ?>
<div class="db-error">
  ⚠️ Koneksi database gagal. Pastikan XAMPP berjalan dan database <strong>deepblue_db</strong> sudah dibuat dengan menjalankan <code>setup.sql</code>.
</div>
<?php endif; ?>

<!-- Upload Controls -->
<div class="gallery-controls">
  <label class="btn-upload" for="imageUpload">
    📁 Upload Foto
    <input type="file" id="imageUpload" accept="image/jpeg,image/png,image/webp" multiple style="display:none"/>
  </label>
  <button class="btn-clear" onclick="hapusSemua()">🗑️ Hapus Semua</button>
  <span class="upload-hint">Format: JPG, PNG, WEBP &bull; Maks. 10 MB per file</span>
</div>

<!-- Indikator loading -->
<div class="upload-status" id="uploadStatus" style="display:none">
  <div class="status-inner">
    <div class="spinner"></div>
    <span id="statusText">Mengupload...</span>
  </div>
</div>

<!-- Gallery Grid -->
<div class="gallery-grid" id="galleryGrid">
<?php foreach ($slots as $idx => $slot):
  $hasPhoto = isset($photos[$idx]);
  $photo    = $hasPhoto ? $photos[$idx] : null;
  $classes  = 'gallery-item ' . $slot['class'] . ($hasPhoto ? ' has-photo' : ' placeholder-item');
?>
  <div class="<?= htmlspecialchars(trim($classes)) ?>"
       data-slot="<?= $idx ?>"
       data-label="<?= htmlspecialchars($slot['label']) ?>">

    <?php if ($hasPhoto): ?>
      <img class="g-photo"
           src="<?= htmlspecialchars(UPLOAD_URL . $photo['filename']) ?>"
           alt="<?= htmlspecialchars($slot['label']) ?>"/>
      <div class="g-overlay"><span><?= htmlspecialchars($slot['label']) ?></span></div>
      <button class="btn-remove-photo" title="Hapus foto" onclick="hapusFoto(event, <?= $idx ?>)">✕</button>
    <?php else: ?>
      <div class="g-overlay"><span><?= htmlspecialchars($slot['label']) ?></span></div>
      <div class="placeholder-inner">
        <div class="placeholder-icon">🌊</div>
        <p>Klik untuk upload foto</p>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
  <button class="lightbox-prev" onclick="event.stopPropagation(); changePhoto(-1)">‹</button>
  <img class="lightbox-img" id="lightboxImg" src="" alt=""/>
  <div class="lightbox-caption" id="lightboxCaption"></div>
  <button class="lightbox-next" onclick="event.stopPropagation(); changePhoto(1)">›</button>
</div>

<style>
/* ── Status / Error ──────────────────────────────── */
.db-error {
  background: rgba(220,50,50,0.15);
  border: 1px solid rgba(220,50,50,0.4);
  color: #ff8a8a;
  padding: 14px 20px;
  border-radius: 8px;
  margin: 0 auto 24px;
  max-width: 680px;
  text-align: center;
  font-size: 14px;
}
.db-error code { background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; }

.upload-status {
  position: fixed;
  top: 80px;
  right: 24px;
  z-index: 9000;
}
.status-inner {
  background: #023e8a;
  color: #fff;
  padding: 12px 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}
.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Gallery Controls ────────────────────────────── */
.gallery-controls {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  margin: 0 0 32px 0;
}
.btn-upload {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #0077b6, #023e8a);
  color: #fff;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: opacity .2s, transform .15s;
  border: none;
}
.btn-upload:hover { opacity: .88; transform: translateY(-1px); }
.btn-clear {
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.2);
  color: #ccc;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  transition: background .2s;
}
.btn-clear:hover { background: rgba(220,50,50,0.2); color: #ff6b6b; }
.upload-hint { font-size: 13px; color: #888; }

/* ── Placeholder ─────────────────────────────────── */
.placeholder-item {
  background: linear-gradient(135deg, #0a1628, #0d2137) !important;
  border: 2px dashed rgba(0,180,216,0.3);
  cursor: pointer;
  transition: border-color .2s;
}
.placeholder-item:hover { border-color: rgba(0,180,216,0.7); }
.placeholder-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 10px;
  color: rgba(255,255,255,0.45);
  font-size: 13px;
  text-align: center;
  padding: 10px;
}
.placeholder-icon { font-size: 36px; opacity: 0.5; }

/* ── Has photo ───────────────────────────────────── */
.gallery-item img.g-photo {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
  border-radius: inherit;
}
.gallery-item.has-photo {
  padding: 0;
  overflow: hidden;
  cursor: zoom-in;
  border: none;
}
.gallery-item.has-photo .g-overlay { opacity: 0; transition: opacity .3s; }
.gallery-item.has-photo:hover .g-overlay { opacity: 1; }

.btn-remove-photo {
  position: absolute;
  top: 8px; right: 8px;
  background: rgba(0,0,0,0.6);
  border: none; color: #fff;
  width: 28px; height: 28px;
  border-radius: 50%;
  font-size: 14px;
  cursor: pointer;
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 10;
  transition: background .2s;
}
.gallery-item.has-photo:hover .btn-remove-photo { display: flex; }
.btn-remove-photo:hover { background: rgba(220,50,50,0.8); }

/* ── Lightbox ────────────────────────────────────── */
.lightbox {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.92);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 16px;
}
.lightbox.active { display: flex; }
.lightbox-img {
  max-width: 90vw; max-height: 80vh;
  border-radius: 8px;
  object-fit: contain;
  box-shadow: 0 8px 40px rgba(0,0,0,0.6);
}
.lightbox-caption { color: rgba(255,255,255,0.75); font-size: 15px; text-align: center; }
.lightbox-close {
  position: fixed; top: 20px; right: 24px;
  background: none; border: none;
  color: #fff; font-size: 28px;
  cursor: pointer; opacity: .7;
  transition: opacity .2s;
}
.lightbox-close:hover { opacity: 1; }
.lightbox-prev, .lightbox-next {
  position: fixed; top: 50%;
  transform: translateY(-50%);
  background: rgba(255,255,255,0.12);
  border: none; color: #fff;
  font-size: 40px; width: 52px; height: 52px;
  border-radius: 50%; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s;
}
.lightbox-prev { left: 20px; }
.lightbox-next { right: 20px; }
.lightbox-prev:hover, .lightbox-next:hover { background: rgba(0,180,216,0.4); }
</style>

<script>
(function () {
  const input       = document.getElementById('imageUpload');
  const grid        = document.getElementById('galleryGrid');
  const statusBox   = document.getElementById('uploadStatus');
  const statusText  = document.getElementById('statusText');
  let lightboxIndex = -1;

  // ── Tampilkan / sembunyikan status ──────────────
  function showStatus(msg) {
    statusText.textContent = msg;
    statusBox.style.display = 'block';
  }
  function hideStatus() { statusBox.style.display = 'none'; }

  // ── Klik placeholder → upload ke slot itu ──────
  grid.addEventListener('click', function (e) {
    const item = e.target.closest('.gallery-item');
    if (!item || !item.classList.contains('placeholder-item')) return;
    const idx = item.dataset.slot;
    input.dataset.targetSlot = idx;
    input.removeAttribute('multiple');
    input.click();
  });

  // ── Klik foto → buka lightbox ───────────────────
  grid.addEventListener('click', function (e) {
    const item = e.target.closest('.gallery-item.has-photo');
    if (!item || e.target.classList.contains('btn-remove-photo')) return;
    openLightbox(parseInt(item.dataset.slot));
  });

  // ── File dipilih ────────────────────────────────
  input.addEventListener('change', async function () {
    const files     = Array.from(this.files);
    const targetSlot = this.dataset.targetSlot;

    if (!files.length) return;

    if (targetSlot !== undefined && files.length === 1) {
      await uploadFile(files[0], parseInt(targetSlot));
      delete this.dataset.targetSlot;
    } else {
      // Multi upload: isi placeholder dari kiri ke kanan
      const placeholders = Array.from(grid.querySelectorAll('.placeholder-item'));
      for (let i = 0; i < files.length && i < placeholders.length; i++) {
        await uploadFile(files[i], parseInt(placeholders[i].dataset.slot));
      }
    }
    this.value = '';
    this.setAttribute('multiple', '');
  });

  // ── Upload satu file ke server ──────────────────
  async function uploadFile(file, slotIdx) {
    const item  = grid.querySelector(`[data-slot="${slotIdx}"]`);
    const label = item.dataset.label;

    showStatus(`Mengupload ke slot ${slotIdx + 1}...`);

    const formData = new FormData();
    formData.append('foto', file);
    formData.append('slot_index', slotIdx);
    formData.append('slot_label', label);

    try {
      const res  = await fetch('upload.php', { method: 'POST', body: formData });
      const data = await res.json();

      if (data.success) {
        renderPhoto(item, data.url, label);
      } else {
        alert('❌ ' + data.message);
      }
    } catch (err) {
      alert('❌ Koneksi ke server gagal. Pastikan XAMPP berjalan.');
    } finally {
      hideStatus();
    }
  }

  // ── Render item setelah upload berhasil ────────
  function renderPhoto(item, url, label) {
    item.innerHTML = `
      <img class="g-photo" src="${url}" alt="${label}"/>
      <div class="g-overlay"><span>${label}</span></div>
      <button class="btn-remove-photo" title="Hapus foto"
              onclick="hapusFoto(event, ${item.dataset.slot})">✕</button>`;
    item.classList.remove('placeholder-item');
    item.classList.add('has-photo');
  }

  // ── Hapus satu foto ─────────────────────────────
  window.hapusFoto = async function (e, slotIdx) {
    e.stopPropagation();
    if (!confirm('Hapus foto ini dari galeri?')) return;

    showStatus('Menghapus foto...');
    try {
      const res  = await fetch('hapus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ slot_index: slotIdx })
      });
      const data = await res.json();

      if (data.success) {
        const item  = grid.querySelector(`[data-slot="${slotIdx}"]`);
        const label = item.dataset.label;
        renderPlaceholder(item, label);
      } else {
        alert('❌ ' + data.message);
      }
    } catch (err) {
      alert('❌ Gagal menghapus. Pastikan XAMPP berjalan.');
    } finally {
      hideStatus();
    }
  };

  // ── Hapus semua foto ────────────────────────────
  window.hapusSemua = async function () {
    const hasPhotos = grid.querySelector('.has-photo');
    if (!hasPhotos) { alert('Tidak ada foto untuk dihapus.'); return; }
    if (!confirm('Hapus SEMUA foto dari galeri? Tindakan ini tidak bisa dibatalkan.')) return;

    showStatus('Menghapus semua foto...');
    try {
      const res  = await fetch('hapus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ delete_all: true })
      });
      const data = await res.json();

      if (data.success) {
        grid.querySelectorAll('.has-photo').forEach(item => {
          renderPlaceholder(item, item.dataset.label);
        });
      } else {
        alert('❌ ' + data.message);
      }
    } catch (err) {
      alert('❌ Gagal menghapus. Pastikan XAMPP berjalan.');
    } finally {
      hideStatus();
    }
  };

  // ── Kembalikan item ke placeholder ─────────────
  function renderPlaceholder(item, label) {
    item.innerHTML = `
      <div class="g-overlay"><span>${label}</span></div>
      <div class="placeholder-inner">
        <div class="placeholder-icon">🌊</div>
        <p>Klik untuk upload foto</p>
      </div>`;
    item.classList.remove('has-photo');
    item.classList.add('placeholder-item');
  }

  // ── Lightbox ────────────────────────────────────
  window.openLightbox = function (slotIdx) {
    const item = grid.querySelector(`[data-slot="${slotIdx}"].has-photo`);
    if (!item) return;
    lightboxIndex = slotIdx;
    document.getElementById('lightboxImg').src    = item.querySelector('.g-photo').src;
    document.getElementById('lightboxCaption').textContent = item.dataset.label;
    document.getElementById('lightbox').classList.add('active');
  };

  window.closeLightbox = function () {
    document.getElementById('lightbox').classList.remove('active');
  };

  window.changePhoto = function (dir) {
    const photos = Array.from(grid.querySelectorAll('.has-photo'));
    if (!photos.length) return;
    const slots     = photos.map(i => parseInt(i.dataset.slot));
    const currPos   = slots.indexOf(lightboxIndex);
    const newPos    = (currPos + dir + slots.length) % slots.length;
    openLightbox(slots[newPos]);
  };

  document.addEventListener('keydown', function (e) {
    if (!document.getElementById('lightbox').classList.contains('active')) return;
    if (e.key === 'Escape')      closeLightbox();
    if (e.key === 'ArrowRight')  changePhoto(1);
    if (e.key === 'ArrowLeft')   changePhoto(-1);
  });
})();
</script>
</section>

<footer>
<div class="footer-content">
<div class="footer-brand">
  <div class="footer-logo">🤿 DeepBlue Diving Academy</div>
  <p>Pusat pelatihan diving profesional bersertifikat PADI di Surabaya, Jawa Timur. Kami berkomitmen untuk keselamatan dan kesenangan di setiap penyelaman.</p>
  <div class="social-links">
    <a href="#">📘 Facebook</a>
    <a href="#">📸 Instagram</a>
    <a href="#">▶️ YouTube</a>
    <a href="#">💬 WhatsApp</a>
  </div>
</div>
<div class="footer-links">
  <h4>Program</h4>
  <ul>
    <li><a href="#programs">Open Water Diver</a></li>
    <li><a href="#programs">Advanced OW</a></li>
    <li><a href="#programs">Rescue Diver</a></li>
    <li><a href="#programs">Divemaster</a></li>
    <li><a href="#programs">Specialty Courses</a></li>
  </ul>
</div>
<div class="footer-links">
  <h4>Informasi</h4>
  <ul>
    <li><a href="#">Tentang Kami</a></li>
    <li><a href="#">Sertifikasi PADI</a></li>
    <li><a href="#">Lokasi Diving</a></li>
    <li><a href="#">Blog Diving</a></li>
    <li><a href="#">Syarat &amp; Ketentuan</a></li>
  </ul>
</div>
</div>
<div class="footer-bottom">
  <p>© 2025 DeepBlue Diving Academy. All rights reserved. | PADI Authorized Dive Center</p>
</div>
</footer>

<button id="backToTop" title="Kembali ke atas">↑</button>
<script src="script.js"></script>
</body>
</html>
