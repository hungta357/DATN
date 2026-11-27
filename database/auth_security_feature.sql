CREATE TABLE IF NOT EXISTS auth_login_attempts (
    attempt_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_auth_login_attempts_updated (updated_at)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS password_reset_tokens;
