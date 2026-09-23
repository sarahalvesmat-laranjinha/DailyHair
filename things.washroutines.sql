CREATE TABLE wash_routines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    weekday TINYINT UNSIGNED NOT NULL,
    step_order INT UNSIGNED NOT NULL DEFAULT 1,
    step_name VARCHAR(120) NOT NULL,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_user_weekday (user_id, weekday)
) ENGINE=InnoDB;