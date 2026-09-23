USE daily_hair;

CREATE TABLE aprove_products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(160) NOT NULL,
    brand VARCHAR(100) NOT NULL,

    category ENUM(
        'Nutrição',
        'Hidratação',
        'Reconstrução'
    ) NOT NULL,

    description TEXT NOT NULL,

    image VARCHAR(500) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    created_by INT UNSIGNED NOT NULL,

    status ENUM(
        'Aprovado',
        'Em análise',
        'Recusado'
    ) NOT NULL DEFAULT 'Em análise',

    CONSTRAINT fk_product_requests_user
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;