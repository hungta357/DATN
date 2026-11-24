CREATE TABLE IF NOT EXISTS group_bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invite_code VARCHAR(24) NOT NULL UNIQUE,
    created_by BIGINT UNSIGNED NOT NULL,
    show_id BIGINT UNSIGNED NOT NULL,
    member_limit TINYINT UNSIGNED NOT NULL,
    payment_mode ENUM('shared','split') NOT NULL,
    status ENUM('open','pending_payment','paid','expired') NOT NULL DEFAULT 'open',
    booking_id BIGINT UNSIGNED NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_group_bookings_creator FOREIGN KEY (created_by) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_group_bookings_show FOREIGN KEY (show_id) REFERENCES shows(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_group_bookings_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_group_bookings_status_expiry (status, expires_at),
    INDEX idx_group_bookings_show (show_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS group_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    is_owner TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('joined','paid') NOT NULL DEFAULT 'joined',
    joined_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_group_member (group_id, user_id),
    CONSTRAINT fk_group_members_group FOREIGN KEY (group_id) REFERENCES group_bookings(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_group_members_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_group_members_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS group_seat_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    show_seat_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    UNIQUE KEY uq_group_assigned_seat (show_seat_id),
    UNIQUE KEY uq_group_member_seat (member_id),
    CONSTRAINT fk_group_seat_assignments_group FOREIGN KEY (group_id) REFERENCES group_bookings(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_group_seat_assignments_member FOREIGN KEY (member_id) REFERENCES group_members(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_group_seat_assignments_show_seat FOREIGN KEY (show_seat_id) REFERENCES show_seats(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_group_seat_assignments_group (group_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS group_contributions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    wallet_transaction_id BIGINT UNSIGNED NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('paid','refunded') NOT NULL DEFAULT 'paid',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_group_contribution_member (group_id, member_id),
    CONSTRAINT fk_group_contributions_group FOREIGN KEY (group_id) REFERENCES group_bookings(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_group_contributions_member FOREIGN KEY (member_id) REFERENCES group_members(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_group_contributions_wallet_transaction FOREIGN KEY (wallet_transaction_id) REFERENCES wallet_transactions(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_group_contributions_status (group_id, status)
) ENGINE=InnoDB;
