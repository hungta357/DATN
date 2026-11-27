CREATE TABLE IF NOT EXISTS incident_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NULL,
    reported_by BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    category ENUM('ticket','service','safety','other') NOT NULL DEFAULT 'ticket',
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open','in_review','resolved','rejected') NOT NULL DEFAULT 'open',
    resolution TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_incident_reports_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_incident_reports_reporter FOREIGN KEY (reported_by) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_incident_reports_assignee FOREIGN KEY (assigned_to) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_incident_reports_status_created (status, created_at),
    INDEX idx_incident_reports_reporter_created (reported_by, created_at),
    INDEX idx_incident_reports_assignee_status (assigned_to, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS staff_shifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    duty VARCHAR(80) NOT NULL,
    notes VARCHAR(1000) NULL,
    status ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_staff_shifts_staff FOREIGN KEY (staff_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_staff_shifts_assigner FOREIGN KEY (assigned_by) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_staff_shifts_room FOREIGN KEY (room_id) REFERENCES rooms(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_staff_shifts_staff_status_time (staff_id, status, starts_at),
    INDEX idx_staff_shifts_time_status (starts_at, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ticket_scan_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NULL,
    staff_id BIGINT UNSIGNED NOT NULL,
    ticket_code VARCHAR(255) NOT NULL,
    outcome ENUM('accepted','rejected','anomaly') NOT NULL,
    anomaly_type VARCHAR(50) NULL,
    details VARCHAR(500) NOT NULL,
    scanned_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL,
    CONSTRAINT fk_ticket_scan_logs_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_ticket_scan_logs_staff FOREIGN KEY (staff_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_ticket_scan_logs_anomaly_time (outcome, scanned_at),
    INDEX idx_ticket_scan_logs_ticket_time (ticket_code, scanned_at),
    INDEX idx_ticket_scan_logs_staff_time (staff_id, scanned_at)
) ENGINE=InnoDB;
