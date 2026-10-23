<?php
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();
$zone = getenv('APP_TIMEZONE') ?: 'America/La_Paz';
date_default_timezone_set($zone);
function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$error = '';
try {
    $db = new PDO(getenv('DB_DSN') ?: 'pgsql:host=localhost;dbname=andesur', getenv('DB_USER') ?: 'andesur', getenv('DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $db->prepare("SELECT set_config('TimeZone', ?, false)");
    $stmt->execute([$zone]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar a la base de datos. Revisa la configuración de PostgreSQL.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403); exit('Solicitud inválida. Recarga la página.');
    }
    if (($_POST['action'] ?? '') === 'logout') {
        $_SESSION = []; session_regenerate_id(true);
        header('Location: /'); exit;
    }
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([strtolower($email)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        $stmt = $db->prepare('INSERT INTO login_events (user_id) VALUES (?)');
        $stmt->execute([$user['id']]);
        session_regenerate_id(true);
        $_SESSION['user'] = ['name' => $user['name'], 'role' => $user['role']];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: /'); exit;
    }
    $error = 'Correo o contraseña incorrectos.';
}
$loggedIn = isset($_SESSION['user']);
$day = is_string($_GET['day'] ?? null) ? $_GET['day'] : date('Y-m-d');
$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $day);
if (!$parsed || $parsed->format('Y-m-d') !== $day) $day = date('Y-m-d');
if ($loggedIn) {
    $days = $db->query('SELECT DISTINCT logged_at::date AS day FROM login_events ORDER BY day DESC')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array($day, $days)) { $days[] = $day; rsort($days); }
    $stmt = $db->prepare("SELECT u.name, u.email, u.role, COUNT(*) AS total, to_char(MIN(e.logged_at), 'HH24:MI:SS') AS first_login, to_char(MAX(e.logged_at), 'HH24:MI:SS') AS last_login, string_agg(to_char(e.logged_at, 'HH24:MI:SS'), ', ' ORDER BY e.logged_at) AS times FROM login_events e JOIN users u ON u.id = e.user_id WHERE e.logged_at >= CAST(? AS date) AND e.logged_at < CAST(? AS date) + INTERVAL '1 day' GROUP BY u.id ORDER BY MAX(e.logged_at) DESC");
    $stmt->execute([$day, $day]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = array_sum(array_column($rows, 'total'));
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Andesur · Accesos del equipo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/style.css" rel="stylesheet">
</head>
<body>
<header class="topbar"><a class="brand" href="/"><span class="brand-icon">A</span> ANDESUR<span class="brand-sub"> / EQUIPO</span></a><span class="status"><span>●</span> Registro de accesos</span></header>
<main class="container py-5">
<?php if (!$loggedIn): ?>
<div class="login-layout">
<section class="welcome"><span class="eyebrow">PORTAL DEL EQUIPO</span><h1>Un buen día<br>empieza aquí<span>.</span></h1><p>Tu espacio para entrar, conectar y seguir<br>el movimiento de nuestro equipo.</p><div class="welcome-footer">ANDESUR <span>Personas que nos mueven.</span></div></section>
<section class="card login-card"><span class="eyebrow">BIENVENIDO DE NUEVO</span><h2>Inicia sesión</h2><p class="text-secondary">Ingresa con tu cuenta de trabajo.</p>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= esc($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= esc($_SESSION['csrf']) ?>"><label for="email">Correo electrónico</label><input class="form-control mb-3" id="email" name="email" type="email" autocomplete="username" placeholder="nombre@andesur.test" required value="<?= esc($_POST['email'] ?? '') ?>"><label for="password">Contraseña</label><input class="form-control mb-4" id="password" name="password" type="password" autocomplete="current-password" required><button class="btn btn-primary w-100" type="submit">Entrar al portal <span class="float-end">→</span></button></form>
<div class="demo"><strong>Cuentas de demostración</strong><span>ana@andesur.test · Empleada</span><span>carlos@andesur.test · Gerente</span><span>sofia@andesur.test · Administradora</span><small>Contraseña para todas: <code>P@ssw0rd</code></small></div>
</section></div>
<?php else: ?>
<div class="d-flex justify-content-between align-items-start gap-3 mb-5 flex-wrap"><div><span class="eyebrow">ACTIVIDAD DEL EQUIPO</span><h1 class="dashboard-title">Accesos del día<span>.</span></h1><p class="text-secondary">Hola, <?= esc($_SESSION['user']['name']) ?>. Así se mueve nuestro equipo.</p></div><form method="post"><input type="hidden" name="csrf" value="<?= esc($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="logout"><button class="btn btn-outline-secondary">Cerrar sesión ↗</button></form></div>
<div class="row g-3 mb-4"><div class="col-md-4"><div class="metric"><span>PERSONAS QUE INGRESARON</span><strong><?= count($rows) ?></strong></div></div><div class="col-md-4"><div class="metric"><span>ACCESOS REGISTRADOS</span><strong><?= $total ?></strong></div></div><div class="col-md-4"><div class="metric"><span>FECHA CONSULTADA</span><strong class="date-value"><?= esc(date('d/m/Y', strtotime($day))) ?></strong></div></div></div>
<section class="card activity-card"><div class="d-flex justify-content-between gap-3 align-items-center flex-wrap mb-4"><div><h2>Historial de ingresos</h2><p class="text-secondary mb-0">Un registro por persona, separado por día.</p></div><form method="get" class="d-flex gap-2 align-items-center"><label for="day" class="visually-hidden">Día</label><input class="form-control" type="date" id="day" name="day" value="<?= esc($day) ?>" required><button class="btn btn-primary">Consultar</button></form></div>
<nav class="day-tabs" aria-label="Días con registros"><?php foreach ($days as $d): ?><a class="<?= $d === $day ? 'active' : '' ?>" href="?day=<?= esc($d) ?>"><?= esc(date('d/m/Y', strtotime($d))) ?><?= $d === date('Y-m-d') ? ' · Hoy' : '' ?></a><?php endforeach; ?></nav>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>PERSONA</th><th>ROL</th><th>INGRESOS</th><th>PRIMER ACCESO</th><th>ÚLTIMO ACCESO</th><th>DETALLE</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><strong><?= esc($r['name']) ?></strong><small class="d-block text-secondary"><?= esc($r['email']) ?></small></td><td><span class="role-badge"><?= esc($r['role']) ?></span></td><td><strong><?= esc($r['total']) ?></strong></td><td><?= esc($r['first_login']) ?></td><td><?= esc($r['last_login']) ?></td><td><details><summary>Ver horas</summary><p class="times"><?= esc($r['times']) ?></p></details></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="6" class="text-center py-5 text-secondary">Todavía no hay accesos registrados en este día.</td></tr><?php endif; ?>
</tbody></table></div><p class="small text-secondary mb-0 mt-3">Horas en <?= esc($zone) ?> · Cada inicio de sesión correcto suma un acceso.</p></section>
<?php endif; ?>
<footer>© <?= date('Y') ?> Andesur <span>Portal interno del equipo</span></footer>
</main></body></html>
