-- Challenges
CREATE TABLE iconcaptcha_challenges (
    id integer PRIMARY KEY AUTOINCREMENT NOT NULL,
    challenge_id text NOT NULL,
    widget_id text NOT NULL,
    puzzle text NOT NULL,
    ip_address text NOT NULL,
    expires_at integer NULL,
    created_at integer NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT iconcaptcha_challenges_widget UNIQUE (challenge_id, widget_id)
);

-- Attempts
CREATE TABLE iconcaptcha_attempts (
    id integer PRIMARY KEY AUTOINCREMENT,
    ip_address text NOT NULL,
    attempts integer NOT NULL,
    timeout_until integer NULL,
    valid_until integer NOT NULL,
    CONSTRAINT iconcaptcha_attempts_ip_address UNIQUE (ip_address)
);
