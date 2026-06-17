CREATE TABLE collections (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(255) NOT NULL,
    description TEXT,

    cover_image VARCHAR(255),

    difficulty ENUM(
        'easy',
        'medium',
        'hard'
    ) DEFAULT 'easy',

    estimated_duration VARCHAR(100),

    status ENUM(
        'active',
        'inactive'
    ) DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);