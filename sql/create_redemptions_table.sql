-- Tabel redemptions untuk menyimpan history redeem reward
-- Jalankan SQL ini di phpMyAdmin atau MySQL client

CREATE TABLE IF NOT EXISTS redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_id INT NOT NULL,
    poin_digunakan INT NOT NULL,
    nama_reward VARCHAR(255) NOT NULL,
    tanggal_redeem DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES rewards(id_reward) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pastikan tabel users memiliki kolom poin
-- Jalankan ini jika kolom poin belum ada di tabel users:
-- ALTER TABLE users ADD COLUMN poin INT DEFAULT 0;

-- Pastikan tabel rewards memiliki kolom stok
-- (User sudah menambahkan ini)
-- ALTER TABLE rewards ADD COLUMN stok INT DEFAULT 0;
