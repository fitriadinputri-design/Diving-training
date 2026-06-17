-- ============================================
-- DeepBlue Diving Academy - Setup Tabel Galeri
-- Jalankan di phpMyAdmin:
--   1. Pilih database "deepblue_db" di sidebar kiri
--   2. Klik tab "SQL"
--   3. Paste query ini lalu klik "Go"
-- ============================================

USE deepblue_db;

CREATE TABLE IF NOT EXISTS gallery_photos (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  slot_index    INT NOT NULL COMMENT 'Posisi slot di grid (0-5)',
  slot_label    VARCHAR(100) NOT NULL COMMENT 'Nama lokasi (e.g. Raja Ampat)',
  filename      VARCHAR(255) NOT NULL COMMENT 'Nama file tersimpan di uploads/gallery/',
  original_name VARCHAR(255) NOT NULL COMMENT 'Nama file asli dari user',
  file_size     INT NOT NULL COMMENT 'Ukuran file dalam bytes',
  uploaded_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_slot (slot_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Slot 0 = Raja Ampat, Papua   (g-large)
-- Slot 1 = Komodo, NTT
-- Slot 2 = Bunaken, Sulawesi
-- Slot 3 = Tulamben, Bali
-- Slot 4 = Wakatobi, Sulawesi  (g-tall)
-- Slot 5 = Alor, NTT

SELECT 'Tabel gallery_photos berhasil dibuat di deepblue_db!' AS status;
