-- Cuentas de demostración. Contraseña para todas: P@ssw0rd
INSERT INTO users (name, email, role, password_hash) VALUES
('Ana Torres', 'ana@andesur.test', 'Empleado', '$2y$10$Are/RJ182Ktfn4DdzPMUfOvOmf39D0th9jISfStw6Z8S0atuJJR8G'),
('Carlos Mendoza', 'carlos@andesur.test', 'Gerente', '$2y$10$Are/RJ182Ktfn4DdzPMUfOvOmf39D0th9jISfStw6Z8S0atuJJR8G'),
('Sofía Vargas', 'sofia@andesur.test', 'Administrador', '$2y$10$Are/RJ182Ktfn4DdzPMUfOvOmf39D0th9jISfStw6Z8S0atuJJR8G');

-- Eventos de ejemplo de hoy y los dos días anteriores (hora de Bolivia).
INSERT INTO login_events (user_id, logged_at)
SELECT u.id, ((CURRENT_TIMESTAMP AT TIME ZONE 'America/La_Paz')::date - d.day_offset + t.hour_value) AT TIME ZONE 'America/La_Paz'
FROM users u CROSS JOIN (VALUES (0), (1), (2)) AS d(day_offset)
CROSS JOIN (VALUES (TIME '08:05:12'), (TIME '09:32:47')) AS t(hour_value);
