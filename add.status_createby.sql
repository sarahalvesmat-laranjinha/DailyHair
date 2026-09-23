USE daily_hair;

ALTER TABLE products
ADD COLUMN status ENUM('pending','approved','rejected')
NOT NULL DEFAULT 'approved' AFTER image;

ALTER TABLE products
ADD COLUMN created_by INT UNSIGNED NULL AFTER status;