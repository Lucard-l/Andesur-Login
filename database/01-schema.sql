CREATE TABLE users (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    role VARCHAR(30) NOT NULL CHECK (role IN ('Empleado', 'Gerente', 'Administrador')),
    password_hash TEXT NOT NULL
);
CREATE TABLE login_events (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    logged_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX login_events_logged_at_idx ON login_events(logged_at);
CREATE INDEX login_events_user_id_idx ON login_events(user_id);
