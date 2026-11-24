CREATE TABLE IF NOT EXISTS maintenance_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS maintenance_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_code VARCHAR(40) NULL UNIQUE,
    category_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    seat_id BIGINT UNSIGNED NULL,
    reported_by BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    severity ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    status ENUM('reported','assigned','in_progress','pending_confirmation','completed') NOT NULL DEFAULT 'reported',
    description TEXT NOT NULL,
    resolution TEXT NULL,
    due_at DATETIME NULL,
    seat_auto_maintenance TINYINT(1) NOT NULL DEFAULT 0,
    room_auto_maintenance TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_maintenance_reports_category FOREIGN KEY (category_id) REFERENCES maintenance_categories(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_maintenance_reports_room FOREIGN KEY (room_id) REFERENCES rooms(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_maintenance_reports_seat FOREIGN KEY (seat_id) REFERENCES seats(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_maintenance_reports_reporter FOREIGN KEY (reported_by) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_maintenance_reports_assignee FOREIGN KEY (assigned_to) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_maintenance_reports_status_severity (status, severity),
    INDEX idx_maintenance_reports_room_created (room_id, created_at),
    INDEX idx_maintenance_reports_assignee_status (assigned_to, status),
    INDEX idx_maintenance_reports_seat_status (seat_id, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS maintenance_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    CONSTRAINT fk_maintenance_attachments_report FOREIGN KEY (report_id) REFERENCES maintenance_reports(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_maintenance_attachments_user FOREIGN KEY (uploaded_by) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_maintenance_attachments_report (report_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS maintenance_histories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(40) NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    CONSTRAINT fk_maintenance_histories_report FOREIGN KEY (report_id) REFERENCES maintenance_reports(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_maintenance_histories_actor FOREIGN KEY (actor_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_maintenance_histories_report (report_id, created_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO maintenance_categories (name, description, created_at, updated_at) VALUES
('Ghế ngồi', 'Ghế hỏng, lỏng hoặc cần bảo trì', NOW(), NOW()),
('Máy chiếu', 'Máy chiếu hoặc màn chiếu gặp sự cố', NOW(), NOW()),
('Âm thanh', 'Loa, âm thanh hoặc thiết bị liên quan', NOW(), NOW()),
('Điều hòa', 'Hệ thống điều hòa, thông gió', NOW(), NOW()),
('Ánh sáng', 'Đèn trong phòng hoặc hệ thống chiếu sáng', NOW(), NOW()),
('Thiết bị soát vé', 'Máy quét vé hoặc thiết bị kiểm tra vé', NOW(), NOW()),
('Vệ sinh', 'Phòng chiếu cần vệ sinh hoặc bảo dưỡng', NOW(), NOW()),
('Khác', 'Sự cố vận hành khác', NOW(), NOW());
