-- Execute uma única vez no banco já existente. Preserve os usuários e dados.
ALTER TABLE users ADD COLUMN firebase_uid VARCHAR(128) NULL UNIQUE;
