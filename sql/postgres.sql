-- Challenges
CREATE TABLE iconcaptcha_challenges (
    id serial NOT NULL PRIMARY KEY,
    challenge_id varchar(36) NOT NULL,
    widget_id varchar(36) NOT NULL,
    puzzle text NOT NULL,
    ip_address inet NOT NULL,
    expires_at timestamp,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT iconcaptcha_challenges_widget UNIQUE (challenge_id, widget_id)
);

-- Attempts
CREATE TABLE iconcaptcha_attempts (
    id serial NOT NULL PRIMARY KEY,
    ip_address inet NOT NULL,
    attempts int NOT NULL,
    timeout_until timestamp NULL,
    valid_until timestamp NOT NULL,
    CONSTRAINT iconcaptcha_attempts_ip_address UNIQUE (ip_address)
);
