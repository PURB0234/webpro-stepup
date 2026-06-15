-- Buat tabel comments untuk menyimpan komentar postingan community
-- Jalankan SQL ini di phpMyAdmin atau MySQL client

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    community_id INT NOT NULL,
    nama_user VARCHAR(100) NOT NULL DEFAULT 'User',
    komentar TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES community(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
