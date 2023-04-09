-- Challenges
CREATE TABLE iconcaptcha_challenges (
    id bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    challenge_id varchar(36) NOT NULL,
    widget_id varchar(36) NOT NULL,
    puzzle varchar(max) NOT NULL,
    ip_address varbinary(32) NOT NULL,
    expires_at datetime NULL,
    created_at datetime NOT NULL DEFAULT GETDATE(),
    CONSTRAINT sessions_widget_challenge UNIQUE (challenge_id, widget_id)
);
