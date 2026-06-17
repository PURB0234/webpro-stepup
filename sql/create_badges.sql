CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    badge_icon VARCHAR(255),
    category ENUM('challenge', 'collection', 'activity', 'streak', 'special_event') DEFAULT 'challenge',
    rarity ENUM('common', 'rare', 'epic', 'legendary') DEFAULT 'common',
    unlock_requirement VARCHAR(255),
    related_challenge_id INT NULL,
    related_collection_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (related_challenge_id) REFERENCES challenges(id) ON DELETE SET NULL,
    FOREIGN KEY (related_collection_id) REFERENCES collections(id) ON DELETE SET NULL
);
