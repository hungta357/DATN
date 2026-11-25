-- Bo sung phan he thong tin rap va tuong tac khach hang (MySQL 8.0)
USE cinema_complete;
ALTER TABLE cinema_info
    ADD COLUMN opening_hours VARCHAR(150) NULL AFTER description,
    ADD COLUMN facilities TEXT NULL AFTER opening_hours,
    ADD COLUMN website VARCHAR(255) NULL AFTER facilities,
    ADD COLUMN map_url VARCHAR(500) NULL AFTER website;

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    admin_reply TEXT NULL,
    status ENUM('new','processing','replied','closed') NOT NULL DEFAULT 'new',
    replied_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_contact_status_created (status, created_at),
    INDEX idx_contact_email (email),
    CONSTRAINT fk_contact_messages_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;
