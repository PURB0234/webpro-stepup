CREATE TABLE challenge_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,

    challenge_id INT NOT NULL,
    user_id INT NOT NULL,

    current_progress INT DEFAULT 0,

    completion_status ENUM(
        'in_progress',
        'completed'
    ) DEFAULT 'in_progress',

    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (challenge_id)
        REFERENCES challenges(id)
        ON DELETE CASCADE
);