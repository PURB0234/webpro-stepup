CREATE TABLE collection_challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,

    collection_id INT NOT NULL,
    challenge_id INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (collection_id)
        REFERENCES collections(id)
        ON DELETE CASCADE,

    FOREIGN KEY (challenge_id)
        REFERENCES challenges(id)
        ON DELETE CASCADE
);