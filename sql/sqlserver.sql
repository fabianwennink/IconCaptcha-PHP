-- Challenges
CREATE TABLE iconcaptcha_challenges (
    id bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    challenge_id varchar(36) NOT NULL,
    widget_id varchar(36) NOT NULL,
    puzzle varchar(max) NOT NULL,
    ip_address varbinary(32) NOT NULL,
    expires_at datetime NULL,
    created_at datetime NOT NULL DEFAULT GETDATE(),
    CONSTRAINT iconcaptcha_challenges_widget UNIQUE (challenge_id, widget_id)
);

-- Attempts
CREATE TABLE iconcaptcha_attempts (
    id bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    ip_address varbinary(32) NOT NULL,
    attempts int NOT NULL,
    timeout_until datetime NULL,
    valid_until datetime NOT NULL,
    CONSTRAINT iconcaptcha_attempts_ip_address UNIQUE (ip_address)
);
