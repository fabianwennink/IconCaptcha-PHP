-- Challenges
CREATE TABLE iconcaptcha_challenges (
    id int PRIMARY KEY AUTOINCREMENT NOT NULL,
    challenge_id text NOT NULL,
    widget_id text NOT NULL,
    puzzle text NOT NULL,
    ip_address text NOT NULL,
    expires_at int,
    created_at int NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT sessions_widget_challenge UNIQUE (challenge_id, widget_id)
);
