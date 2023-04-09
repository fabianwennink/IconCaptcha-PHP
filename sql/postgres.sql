-- Challenges
CREATE TABLE iconcaptcha_challenges (
    id serial NOT NULL PRIMARY KEY,
    challenge_id varchar(36) NOT NULL,
    widget_id varchar(36) NOT NULL,
    puzzle text NOT NULL,
    ip_address inet NOT NULL,
    expires_at timestamp,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT sessions_widget_challenge UNIQUE (challenge_id, widget_id)
);
