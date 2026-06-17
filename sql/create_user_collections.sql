CREATE TABLE user_collections (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    collection_id INT NOT NULL,

    progress_percentage INT DEFAULT 0,

    status ENUM(
        'joined',
        'in_progress',
        'completed'
    ) DEFAULT 'joined',

    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (collection_id)
        REFERENCES collections(id)
        ON DELETE CASCADE
);