CREATE TABLE challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255) NOT NULL,
    description TEXT,

    banner_image VARCHAR(255),

    challenge_type ENUM(
        'daily',
        'weekly',
        'monthly',
        'team',
        'event'
    ) NOT NULL,

    goal_type ENUM(
        'steps',
        'distance',
        'active_minutes'
    ) NOT NULL,

    goal_value INT NOT NULL,

    reward_points INT DEFAULT 0,

    badge_reward VARCHAR(100),

    participant_count INT DEFAULT 0,

    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    status ENUM(
        'draft',
        'active',
        'completed',
        'archived'
    ) DEFAULT 'draft',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);